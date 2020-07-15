#!/bin/sh
# Script que exibe horário, percentual de disco ocupado e os usuários os logados

# comando que exibe o horário
echo "Quer saber o horário? [y/n]"
read q1

case "$q1" in
	yes | y | Y | Yes | YES )
		echo "Exibição do horário"
		date "+%Hh %Mmins %Ssecs"
		echo
		;;

	* )
		;;
esac

# comando que exibe o percentual de disco ocupado
echo "Quer saber o percentual de disco ocupado? [y/n]"
read q2

case "$q2" in
	yes | y | Y | Yes | YES )
		echo "Exibição do percentual de disco ocupado"
		df -h ~/
		echo
		;;
	* )
		;;
esac

# comando que exibe a quantidade de usuários logados
echo "Quer saber quais são os usuários logados? [y/n]"
read q3

case "$q3" in
	yes | y | Y | Yes | YES )
		echo "Exibição dos usuários logados"
		who
		echo
		;;
	* )
		;;
esac

exit 0

