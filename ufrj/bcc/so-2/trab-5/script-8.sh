#!/bin/sh
# Script que mostra os shells de cada usuário do sistema

# Utiliza o utilitário cut para pegar a sétima coluna do arquivo /etc/passwd/ que corresponde ao shell utilizado pelo usuário
# Por existir diversos usuários com o mesmo shell ordeno os valores com o utilitário sort
# E exibo apenas os shells distintos a partir do utilitário uniq
cut -d: -f 7 /etc/passwd | sort | uniq

# Termina o script
exit 0
