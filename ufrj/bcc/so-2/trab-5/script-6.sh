#!/bin/sh
# Script que exibe todos os parametros na tela grudados

# Utilizo o utilitário tr para retirar os espaços existentes na listagem dos parâmetros na variável $@
echo "$@" | tr -d [:space:]
# Salta uma linha para ficar elegante
echo

# Termina o script
exit 0

