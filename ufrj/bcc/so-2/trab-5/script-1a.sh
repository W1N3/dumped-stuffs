#!/bin/sh
# Script que exibe horário, percentual de disco ocupado e os usuários os logados

# comando que exibe o horário
date "+%Hh %Mmins %Ssecs"
echo
# comando que exibe o percentual de disco ocupado
df -h ~/
echo
# comando que exibe a quantidade de usuários logados
who
echo
exit 0

