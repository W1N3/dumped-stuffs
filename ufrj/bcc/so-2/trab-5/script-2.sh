#!/bin/sh
# Script que testa se um arquivo existe e se existe, diz se é regular ou diretório.

# Impera ao usuário o nome do arquivo
echo -n "Digite o nome de um arquivo: "
# Lê o nome do arquivo
read nome_do_arquivo

# Verifica se o arquivo existe na diretório atual
if [ -e $nome_do_arquivo ]
then
	echo "$nome_do_arquivo existe!"
	# Verifica se o arquivo é um diretório
	if [ -d $nome_do_arquivo ]
	then
		echo "E é um diretório."
	# Verifica se o arquivo é regular
	elif [ -f $nome_do_arquivo ]
	then
		echo "E é um arquivo regular."
	fi
else
	echo "$nome_do_arquivo não existe..."
fi

# Termina o script
exit 0
