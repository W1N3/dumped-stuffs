#!/bin/sh
# Script que exibe horário, percentual de disco ocupado e os usuários os logados de forma interativa

# Parte responsável pela exibição do horário
# Exibe a primeira pergunta
echo -n "Quer saber o horário? [y/n] - "
# Lê a primeira pergunta
read primeira_pergunta

# Avalia a primeira pergunta
case "$primeira_pergunta" in
	yes | y | Y | Yes | YES )
		# Caso sim, executa a exibição do horário
		echo "Exibição do horário"
		date "+%Hh %Mmins %Ssecs"
		echo
		;;

	* )
		# Caso qualquer outra resposta, continua a execução do script
		;;
esac

# Parte responsáve pela exibição do percentual de disco ocupado
# Exibe a segunda pergunta
echo -n "Quer saber o percentual de disco ocupado? [y/n] - "
# Lê a segunda pergunta
read segunda_pergunta

# Avalia a segunda pergunta
case "$segunda_pergunta" in
	yes | y | Y | Yes | YES )
		# Caso sim, executa a exibição do percentual de disco ocupado
		echo "Exibição do percentual de disco ocupado"
		df -h ~/
		echo
		;;
	* )
		# Caso qualquer outra resposta, continua a execução do script
		;;
esac

# Parte responsável pela exibição da quantidade de usuários logados
# Exibe a terceira pergunta
echo -n "Quer saber quais são os usuários logados? [y/n] - "
# Lê a terceira pergunta
read terceira_pergunta

# Avalia a terceira pergunta
case "$terceira_pergunta" in
	yes | y | Y | Yes | YES )
		# Caso sim, executa a exibição dos usuários logados
		echo "Exibição dos usuários logados"
		who
		echo
		;;
	* )
		# Caso qualquer outra resposta, o script termina
		;;
esac

exit 0

