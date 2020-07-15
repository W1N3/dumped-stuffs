#!/bin/sh
# Script que mostra o usuário e o nome completo dos usuários do sistema

# Utilizo o utilitário cut para pegar a primeira e quinta coluna que correspondem respectivamente ao usuário e seu nome completo no sistema
# Utilizo o utilitário tr para espaçar com TABs ao invés de dois pontos
cut -d: -f 1,5 /etc/passwd | tr ":" "\t"

# Termina o script
exit 0

