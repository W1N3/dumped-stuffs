import matplotlib.pyplot as plt
import numpy as np
import math

n = 10 # Número de elementos/equações

kazins = [[[0 for x in range(2)] for y in range(2)] for z in range(n)]

for i in range(n):
  for j in range(2):
    for l in range(2):
      if(j==l):
        kazins[i][j][l] = 1 # EA/L do elemento
      else:
        kazins[i][j][l] = -1 # EA/L do elemento

K = [[0 for x in range(n)] for y in range(n)] 

for i in range(n):
  for j in range(n):
    K[i][j] = 0

#for i in range(n-1):

  
for i in range(n):
  if i != (n-1):
    K[i][i] = kazins[i+1][0][0] + kazins[i][1][1]
  else:
    K[i][i] = kazins[i][1][1]
    
for i in range(n-1):
  K[i+1][i] = kazins[i+1][0][1]
  K[i][i+1] = kazins[i+1][1][0]

inv_K = np.linalg.inv(K)

P = 5 # Carga na ponta

F = [0 for i in range(n)]
F[n-1] = P

a = np.array(inv_K)
b = np.array(F)

result = a.dot(b)

x_axes = np.arange(0, 10, 1)

plt.plot(x_axes, result)
plt.xlabel("Elementos")
plt.ylabel("Deslocamento")
plt.show()