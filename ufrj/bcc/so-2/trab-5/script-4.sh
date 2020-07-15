#!/bin/sh
# Script que conta até zero a partir do primeiro parâmetro

# Verifica se existe pelo menos um parâmetro
if [ $# -ge 1 ]
then
	# Verifica se o primeiro parâmetro é numérico
	if echo $1 | egrep -q '^[0-9]+$'
	then
		# Inicializa o contado com o primeiro parâmetro
		cont=$1
	else
		exit 1
	fi
	# Reduz $cont até 0 enquanto exibe o valor na tela de forma espaçada
	while [ $cont -ge 0 ]
	do
		echo -n " $cont"
		cont=$((cont-1))
	done
	# Salta uma linha para ficar elegante
	echo
else
	exit 1
fi

# Termina o script
exit 0

