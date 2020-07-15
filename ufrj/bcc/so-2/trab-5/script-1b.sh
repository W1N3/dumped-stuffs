#!/bin/sh
# Script que exibe horário, percentual de disco ocupado e os usuários os logados

# comando que exibe o horário
echo "Exibição do horário"
date "+%Hh %Mmins %Ssecs"
echo

# comando que exibe o percentual de disco ocupado
echo "Exibição do percentual de disco ocupado"
df -h ~/
echo

# comando que exibe a quantidade de usuários logados
echo "Exibição dos usuários logados"
who
echo

exit 0

