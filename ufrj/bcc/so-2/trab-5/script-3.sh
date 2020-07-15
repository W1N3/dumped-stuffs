#!/bin/sh
# Script que mostra a relação entre 2 números passados por parâmetro

# Verifica se é passado apenas dois parâmetros para o script
if [ "$#" -eq 2 ]
then
	# Verifica se o primeiro parâmetro é numérico
	if ! echo $1 | egrep -q '^[0-9]+$'
	then
		exit 1
	fi

	# Verifica se o segundo parâmetro é numérico
	if ! echo $2 | egrep -q '^[0-9]+$'
	then
		exit 1
	fi

	# Verifica se o primeiro parâmetro é maior do que o segundo
	if [ $1 -gt $2 ]
	then
		echo "$1 é maior que $2"
	# Verifica se o primeiro parâmetro é menor do que o segundo
	elif [ $1 -lt $2 ]
	then
		echo "$1 é menor que $2"
	# Caso não seja nem maior nem menor, evidentemente, os parâmetros são iguais
	else
		echo "$1 é igual a $2"
	fi
else
	exit 1
fi
# Termina o script
exit 0

# comentário extra classe
# descobri que no "case" do shell script não se usa regex, mas um jeito próprio de achar os padrões
# https://www.gnu.org/savannah-checkouts/gnu/bash/manual/bash.html#Pattern-Matching
# https://stackoverflow.com/questions/9631335/regular-expressions-in-a-bash-case-statement
