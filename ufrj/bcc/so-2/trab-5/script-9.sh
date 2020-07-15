#!/bin/sh
# Script que exibe todos os parâmetros com contagem

# Verifica se a lista de parâmetros é maior do que 0
if [ $# -gt 0 ]
then
	cont=1
	# Itera a lista de parâmetros sem separador, $@
	for pam in $@
	do
		# Exibe a contagem do parametro a partir de $cont e o parâmetro a partir de $pam
		echo "Parâmetro $cont : $pam"
		# Soma o contador
		cont=$((cont+1))
	done
fi

# Termina o script
exit 0
