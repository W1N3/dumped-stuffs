#!/bin/sh
# Script que verifica se o primeiro parâmetro está contido no segundo

# Verifica se existe pelo menos dois parâmetros
if [ $# -ge 2 ]
then
	# parametro F para achar a string literal ao invés de aplicar regex
	if echo $2 | grep -Fq "$1"
	then
		# Exibe que está contido caso esteja contido
		echo "$1 está contido em $2"
	else
		exit 1
	fi
else
	exit 1
fi

# Termina o script
exit 0

