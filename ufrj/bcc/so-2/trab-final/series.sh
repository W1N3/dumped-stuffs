IFS="
"
LOG="$HOME/.log_series"
EXT_VID="(mp4|mkv|avi|ogv|wmv|mov|flv|mpg|mpeg)"
EXT_SUB="(srt|idx|sub)"

# Cores
SEM_COR="\033[0m"
VERMELHO_CLARO="\033[1;31m"
VERMELHO="\033[0;31m"  # Erro
AZUL_CLARO="\033[1;34m"   # Debug
AMARELO="\033[1;33m"
MARROM="\033[0;33m"
CINZA="\033[0;30m"
CINZA_CLARO="\033[0;37m"
VERDE="\033[0;32m"
VERDE_CLARO="\033[1;32m"


seleciona_arquivos() {
  for arq in $@; do
    if ! [ -e $arq ]; then
      imprime_aviso "$arq não existe.\n"
      continue
    fi

    find $arq -maxdepth 1 -type f
  done
}

seleciona_videos() {
  seleciona_arquivos "$@" | grep -iE "\.$EXT_VID$"
}

seleciona_legendas() {
  seleciona_arquivos "$@" | grep -iE "\.$EXT_SUB$"
}


salvar_mudanca() {
  SALVOU_MUDANCA="0"

  imprime_debug "Função salvar_mudanca()"
  local nome_antigo=$1
  local nome_novo=$2

  if [ -z "$nome_antigo" ]; then
    imprime_erro "Falta nome_antigo em salvar_mudanca.\n"
    return
  fi

  if [ -z "$nome_novo" ]; then
    imprime_erro "Falta nome_novo em salvar_mudanca.\n"
    return
  fi

  if ! [ -e "$LOG" ]; then
    touch "$LOG"
  fi

  path_antigo=$(readlink -f $nome_antigo)
  path_novo=$(readlink -f $nome_novo)

  entradas_em_log=$(wc -l "$LOG" | cut -d" " -f1)
  imprime_debug "Entradas em log: $entradas_em_log"
  log_entry="[$entradas_em_log] $(date '+%Y-%m-%d %H:%M:%S');$path_antigo;$path_novo"
  if $(echo $log_entry >> "$LOG") ; then
    imprime_acerto "Mudança salva em $LOG."
  fi

  SALVOU_MUDANCA="1"
}


aplica_sem_salvar() {
  APLICOU_SEM_SALVAR="0"

  imprime_debug "Função aplica_sem_salvar"
  local nome_antigo=$1
  local nome_novo=$2

  if [ -z "$nome_antigo" ]; then
    imprime_erro "Falta argumento nome_antigo em aplica_sem_salvar.\n"
    return
  fi

  if [ -z "$nome_novo" ]; then
    imprime_erro "Falta argumento nome_novo em aplica_sem_salvar.\n"
    return
  fi

  if [ "$nome_antigo" = "$nome_novo" ]; then
    imprime_aviso "$nome_antigo já existe.\n"
    return
  fi

  if ! mv "$nome_antigo" "$nome_novo"; then
    imprime_erro "Falha ao mover arquivo.\n"
    return
  fi

  APLICOU_SEM_SALVAR="1"
}

aplica_mudanca() {
  APLICOU_MUDANCA="0"

  imprime_debug "Função aplica_mudanca"
  local nome_antigo=$1
  local nome_novo=$2

  if [ -z "$nome_antigo" ]; then
    imprime_erro "Falta argumento nome_antigo em aplica_mudanca.\n"
    APLICOU_MUDANCA="0"
    return
  fi

  if [ -z "$nome_novo" ]; then
    imprime_erro "Falta argumento nome_novo em aplica_mudanca.\n"
    APLICOU_MUDANCA="0"
    return
  fi

  if [ "$nome_antigo" = "$nome_novo" ]; then
    imprime_aviso "$nome_antigo já existe.\n"
    APLICOU_MUDANCA="0"
    return
  fi

  if ! mv "$nome_antigo" "$nome_novo"; then
    imprime_erro "Falha ao mover arquivo.\n"
    APLICOU_MUDANCA="0"
    return
  fi

  if [ "$(salvar_mudanca "$nome_antigo" "$nome_novo")" = "1" ]; then
    imprime_erro "Falha ao salvar mudança.\n"
    APLICOU_MUDANCA="0"
    return
  fi

  APLICOU_MUDANCA="1"
}

################################################################################

extrai_numero() {
  # Extrai número de episódio de um ou mais arquivos.
  grep -oiE "ep?[0-9]+" <<< "$@" | grep -oiE "[1-9][0-9]*"
}


procura_arquivos_correspondentes() {
  ARQUIVOS_CORRESPONDENTES=""
  local numero_arquivo="$1"
  local outros_arquivos="${@:2}"

  imprime_debug "Buscando número $numero_arquivo entre os arquivos:\n>>\n$outros_arquivos\n<<"
  if [ -z "$numero_arquivo" ]; then
    imprime_debug "Nenhum número para procurar."
    return
  fi

  if [ -z "$outros_arquivos" ]; then
    imprime_erro "Faltam arquivos para pesquisar."
    return
  fi

  # Pesquisa os arquivos de mesmo padrão
  ARQUIVOS_CORRESPONDENTES="$(grep -oiE ".*ep?0*$numero_arquivo[^0-9]+.*" <<< "$outros_arquivos")"
}


video_tem_numero_unico() {
  VIDEOS_CORRESPONDENTES="0"
  local video="$1"
  local todos_os_videos="${@:2}"

  ## Pega o número do video
  local numero=$(extrai_numero "$video")
  if [ -z "$numero" ]; then
    imprime_erro "Nenhum número encontrado no nome do vídeo."
    VIDEOS_CORRESPONDENTES="0"
    return
  fi

  ## Pesquisa quantos vídeos têm o mesmo padrão
  procura_arquivos_correspondentes "$numero" "$todos_os_videos"
  local videos_correspondentes="$ARQUIVOS_CORRESPONDENTES"
  if [ -z "$videos_correspondentes" ] || [ "$videos_correspondentes" = "0" ]; then
    imprime_erro "Não há vídeos correspondentes para o vídeo $video"
    VIDEOS_CORRESPONDENTES="0"
    return
  fi

  imprime_debug "Vídeos correspondentes: $videos_correspondentes"

  local iguais=$(echo "$videos_correspondentes" | wc -l)
  imprime_debug "Iguais: $iguais"
  if [ -z "$iguais" ] || [ "$iguais" = "0" ]; then
    imprime_erro "Não há vídeos do episódio $numero.\n"
    VIDEOS_CORRESPONDENTES="0"
    return
  fi

  if [ "$iguais" -gt "1" ]; then
    imprime_erro "Há vídeos repetidos do episódio $numero: $video\n"
    VIDEOS_CORRESPONDENTES="$iguais"
    return
  fi

  VIDEOS_CORRESPONDENTES="1"
}


legenda_tem_numero_unico() {
  LEGENDAS_CORRESPONDENTES="0"

  local legenda="$1"
  local todas_as_legendas="${@:2}"

  ## Pega o número da legenda
  local numero="$(extrai_numero "$legenda")"
  if [ -z "$numero" ]; then
    imprime_erro "Nenhum número encontrado no nome da legenda."
      LEGENDAS_CORRESPONDENTES="0"
    return
  fi

  ## Pesquisa quantos vídeos têm o mesmo padrão
  procura_arquivos_correspondentes "$numero" "$todas_as_legendas"
  local legendas_correspondentes="$ARQUIVOS_CORRESPONDENTES"
  if [ -z "$legendas_correspondentes" ] || [ "$legendas_correspondentes" = "0" ]; then
    imprime_erro "Não há legendas correspondentes para a legenda $legenda"
      LEGENDAS_CORRESPONDENTES="0"

    return
  fi

  imprime_debug "Legendas correspondentes:\n>>>\n$legendas_correspondentes\n<<<"
  local iguais=$(echo "$legendas_correspondentes" | wc -l )
  imprime_debug "Iguais: $iguais"
  if [ -z "$iguais" ] || [ "$iguais" = "0" ]; then
    imprime_erro "Não há legendas do episódio $numero.\n"
      LEGENDAS_CORRESPONDENTES="0"
    return
  fi

  if [ "$iguais" -gt "1" ]; then
    imprime_erro "Há legendas repetidas do episódio $numero: $legenda\n"
    LEGENDAS_CORRESPONDENTES="$iguais"
    return
  fi

  LEGENDAS_CORRESPONDENTES="1"
}


clean() {
  imprime_debug "Função clean"
  local padrao_sed="s/(.*)\/.*[eE]p?([0-9]+).*\.($EXT_VID)$/\1\/E\2.\3/"
  local padrao_arquivos=$@

  if [ -z "$padrao_arquivos" ]; then
    imprime_erro "Falta o argumento arquivos em clean."
    return
  fi
  imprime_debug "Padrão de arquivos: $padrao_arquivos"
  imprime_debug "Vídeos: $(seleciona_videos "$padrao_arquivos")"

  local videos=$(seleciona_videos "$padrao_arquivos")
  for video in $videos; do
    local nome_novo=$(sed -E "$padrao_sed" <<< "$video")
    imprime_debug "Arquivo: $video"
    imprime_debug "Nome novo: $nome_novo"

    video_tem_numero_unico "$video" "$videos"
    [ "$VIDEOS_CORRESPONDENTES" != "1" ] && continue

    local path_arquivo=$(readlink -f $video)
    local path_novo=$(readlink -f $nome_novo)

    aplica_mudanca "$video" "$nome_novo"
    if [ "$APLICOU_MUDANCA" = "1" ]; then
      echo -n "Nome sujo: "
      imprime_colorido "$video" $MARROM

      echo -n "Nome limpo: "
      imprime_colorido "$nome_novo" $AMARELO

      echo
    fi
  done
}


adiciona() {
  # Pega número do vídeo
  local nomes=$1
  local video=$2

  local num='$(extrai_numero "$video")'
  [ -z "$num" ] && return

  # Procura nome correspondente em $nomes baseado no formato em texto:
  # <número do episódio><espaço><nome>
  # exemplo:
  # 1 Episódio 1: A caça
  local nome_ep=$(cat "$nomes" | grep -E "^$num\s(.+)" | cut -d" " -f2- )  # | tr -d "'$'\r'")
  [ -z "$nome_ep" ] && return

  imprime_debug "Nome correspondente: $nome_ep"

  # Se o nome do arquivo já não contém o nome do episódio
  if (grep -oiE "$nome_ep" <<< "$video"); then
    echo "'$nome_ep' já está em '$video'"
  else
    local nome_novo=$(sed -E 's/(.*)\.($EXT_VID)$/\1 - "$nome_ep\.\2"/' <<< "$video")
    aplica_mudanca "$video" "$nome_novo"
    if [ "$APLICOU_MUDANCA" = "1" ]; then
      echo -n "Nome antigo: "
      imprime_colorido "$video" $MARROM
      echo -n "Nome novo: "
      imprime_colorido "$nome_novo" $AMARELO
      echo
    fi
  fi
}


append() {
  imprime_debug "Função append"

  local nomes=$1
  local padrao_arquivos=${@:2}

  [ -z "$nomes" ] && return
  [ -z "$padrao_arquivos" ] && return

  local videos=$(seleciona_videos "$padrao_arquivos")
  for video in $videos; do
    video_tem_numero_unico "$video" "$videos"
    if [ "$VIDEOS_CORRESPONDENTES" != "1" ]; then
      imprime_debug "Vídeo $video não é único."
      continue
    else
      imprime_debug "Vídeo $video é único."
    fi

    adiciona $nomes $video
  done
}


combina() {
  local legenda="$1"

  ## Pesquisa quantas legendas têm esse mesmo 'número processado' na pasta da legenda
  local pasta_da_legenda=$(dirname $legenda)
  imprime_debug "Pasta da legenda: $pasta_da_legenda"

  # Se essa legenda for única, eu encontro o vídeo correspondente
  local numero="$(extrai_numero $legenda)"

  local videos_da_pasta=$(seleciona_videos $pasta_da_legenda)
  imprime_debug "Vídeos da pasta:\n>>\n$videos_da_pasta\n<<"

  procura_arquivos_correspondentes "$numero" "$videos_da_pasta"
  local videos_correspondentes="$ARQUIVOS_CORRESPONDENTES"
  imprime_debug "Vídeos correspondentes:" $videos_correspondentes

  local qtd_videos_correspondentes=$(echo "$videos_correspondentes" | wc -l)
  imprime_debug "Qtd. de vídeos correspondentes: $qtd_videos_correspondentes"
  if [ "$qtd_videos_correspondentes" = "0" ] || [ -z "$qtd_videos_correspondentes" ]; then
    imprime_erro "Não há vídeo correspondente para a legenda $legenda.\n"
    return
  fi

  # Se tiverem múltiplos vídeos correspondentes, não usa nenhum.
  if [ "$qtd_videos_correspondentes" -gt "1" ]; then
    imprime_erro "Há vídeos repetidos do episódio $numero.\n"
    return
  fi

  # Iguala nome da legenda para o nome do vídeo (exceto extensão)
  local video_sem_extensao="${videos_correspondentes%.*}"
  if [ -z "$video_sem_extensao" ]; then
    imprime_erro "Vídeo >>$videos_correspondentes<< não tem nome."
    return
fi

  local ext_legenda="${legenda##*.}"
  if [ -z "$ext_legenda" ]; then
    imprime_erro "Legenda não tem extensão."
    return
fi

  imprime_debug "Nome novo (video_sem_ext + ext_legenda): '$video_sem_extensao.$ext_legenda'"
  local path_antigo=$(readlink -f "$legenda")
  local path_novo=$(readlink -f "$video_sem_extensao.$ext_legenda")

  aplica_mudanca "$path_antigo" "$path_novo"
  if [ "$APLICOU_MUDANCA" = "1" ]; then
    echo -n "Nome antigo: "
    imprime_colorido "$legenda" $MARROM

    echo -n "Nome novo: "
    imprime_colorido "$video_sem_extensao.$ext_legenda" $AMARELO

    echo
  fi
}

match() {
  imprime_debug "Função match"
  local legendas=$(seleciona_legendas $@)
  imprime_debug "Legendas selecionadas: $legendas"
  for legenda in $legendas; do
    imprime_debug "#################################################"
    imprime_debug "Legenda: $legenda"

    legenda_tem_numero_unico "$legenda" "$legendas"
    if [ "$LEGENDAS_CORRESPONDENTES" != "1" ]; then
      imprime_debug "Legenda $legenda não é única."
      continue
    else
      imprime_debug "Legenda $legenda é única."
    fi
    combina "$legenda"
  done
}


desfaz_mudanca() {
  # Toda modificação pelo utilitário é salva no arquivo LOG.
  # Para desfazer uma modificação, procuramos a atualização mais recente
  # sobre o nome de um determinado arquivo atual.
  # Após encontrar a linha desejada, salvamos seu nome anterior,
  # apagamos a linha encontrada, e renomeamos o arquivo para o nome anterior.

  imprime_debug "Função desfaz_mudanca"
  local path_para_desfazer=$1

  if [ -z "$path_para_desfazer" ]; then
    imprime_erro "Parâmetro nome_para_desfazer não existe.\n"
    return
  fi

  if ! [ -e "$LOG" ]; then
    imprime_erro "Arquivo de log não foi encontrado.\n"
    return
  fi

  imprime_debug "Path para desfazer: $path_para_desfazer"
  local mudanca_mais_recente=$(cat "$LOG" | grep "$path_para_desfazer$" | tail -n 1)
  imprime_debug "Mudança mais recente: $mudanca_mais_recente"
  if [ -z "$mudanca_mais_recente" ]; then
    if [ -n "$VERBOSE" ]; then
      imprime_erro "Não há mudanças anteriores registradas para $path_para_desfazer.\n"
    fi
    return
  fi

  local path_anterior=$(cut -d";" -f2 <<< "$mudanca_mais_recente")
  imprime_debug "Path anterior: $path_anterior"

  # Remove linha
  local data=$(cut -d";" -f1 <<< "$mudanca_mais_recente")
  imprime_debug "Data: $data"
  local complemento=$(grep -ivF "$data" $LOG)
  imprime_debug "Complemento: $complemento"
  echo "$complemento" > "tmp" && mv "tmp" "$LOG"

  aplica_sem_salvar "$path_para_desfazer" "$path_anterior"
  if [ "$APLICOU_SEM_SALVAR" = "1" ]; then
    imprime_acerto "Mudança foi desfeita de $LOG."

    echo -n "Nome desfeito: "
    imprime_colorido "$path_para_desfazer" $MARROM

    echo -n "Nome novo: "
    imprime_colorido "$path_anterior" $AMARELO

    echo
  fi
}


undo() {
  for path_absoluto in $( readlink -f $(seleciona_arquivos $@) ); do
    imprime_debug "Arquivo para desfazer: $path_absoluto"
    desfaz_mudanca "$path_absoluto"
  done
}


full() {
  imprime_debug "Função full"

  local nomes="$1"
  local arquivos="${@:2}"

  if [ -z "$arquivos" ]; then
    imprime_erro "Endereço vazio.\n"
  fi

  if [ -z "$nomes" ]; then
    imprime_erro "Falta arquivo de nomes.\n"
  fi

  echo "@@@@@@@@@@@@@@@@ Modo  Clean @@@@@@@@@@@@@@@@"
  clean $arquivos

  echo "@@@@@@@@@@@@@@@@ Modo Append @@@@@@@@@@@@@@@@" $CINZA_CLARO
  append $nomes $arquivos

  echo "@@@@@@@@@@@@@@@@ Modo  Match @@@@@@@@@@@@@@@@" $CINZA_CLARO
  match $arquivos
}

####################################################################

imprime_colorido() {
  cor="$2"
  [ -z "$2" ] && cor=$SEM_COR
  echo -e "${cor}${1}${SEM_COR}"
}


imprime_acerto() {
  imprime_colorido "[SUCESSO] $1" $VERDE_CLARO
}

imprime_erro() {
  imprime_colorido "[ERRO] $1" $VERMELHO_CLARO
}

imprime_aviso() {
  imprime_colorido "[AVISO] $1" $AMARELO
}

imprime_debug() {
  [ "$DEBUG" = "1" ] && imprime_colorido "[DEBUG] $1" $AZUL_CLARO
}

imprime_arquivo() {
  imprime_debug "Função imprime arquivo:"
  for arquivo in $(cat -); do
    eh_video=$(grep -oiE ".*\.$EXT_VID$" <<< "$1")
    [ -n $eh_video ] && cor=$VERDE_CLARO

    eh_sub=$(grep -oiE ".*\.($EXT_SUB)$" <<< "$1")
    [ -n $eh_sub ] && cor=$AZUL_CLARO

    imprime_colorido $1 $cor
  done
}


mensagem_uso() {
  echo "Uso:"
  echo "    $0 clean  <arquivos/pastas>"
  echo "       - Padroniza arquivos de episódios no formato E<número>.<extensão>"
  echo "    $0 undo   <arquivos/pastas>"
  echo "       - Desfaz quaisquer mudanças prévias registradas"
  echo "    $0 match  <arquivos/pastas>"
  echo "       - Iguala legendas numeradas com seus episódios"
  echo "       Obs.: Não funciona com legendas 'repetidas'"
  echo "    $0 append <arquivo de nomes> <arquivos/pastas>"
  echo "       - Adiciona nome de episódios numerados baseado em um arquivo de nomes."
  echo "         * Exemplo de linhas do arquivo de nomes:"
  echo "           10 Episódio 10: 'Pine Barrens'"
  echo "           2 Juan Likes Rice and Chicken"
  echo "           8 Part 8"
  echo "    $0 full   <arquivo de nomes> <arquivos/pastas>"
  echo "       - Executa clean, append e match."

}

DEBUG=0

while getopts ":hdv" option; do
  case "$option" in
    h) mensagem_uso;
       exit 1 ;;
    d) DEBUG="1" ;;
    v) VERBOSE="1" ;;
    \?) echo "Opção inválida: $OPTARG" 1>&2;
       mensagem_uso;
       exit 1 ;;
  esac
done

shift $((OPTIND - 1))

subcomando="$1"; shift
case "$subcomando" in
  clean) clean $@ ;;
  undo) undo $@ ;;
  match) match $@ ;;
  append) append $@ ;;
  full) full $@ ;;
  *) mensagem_uso ;;
esac
