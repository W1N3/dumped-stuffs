import matplotlib.pyplot as plt
import numpy as np
import math

# Constante do metodo de newmark
step = 0.1
T = 20.0
beta = 0.25
gama = 0.5
E = 1.0
A = 1.0
M = 1.0
# Pois agora K e M são matriciais
P0 = 1.0
C = 0.0

def charge_function(x) :
  return 1#math.sin(x)

period = 6.28
freq = 1/period
omega = 2*3.1416*freq

P = P0*charge_function(0)

# começo da criação das matrizes de rigidez global

elem = 5 # Número de elementos/equações

kazins = [[[0 for x in range(2)] for y in range(2)] for z in range(elem)]
emezins = [[[0 for x in range(2)] for y in range(2)] for z in range(elem)]

for i in range(elem):
  for j in range(2):
    for l in range(2):
      emezins[i][j][l] =  M/elem # (Massa do elemento, M é a massa do objeto dada, logo a massa do elemento é uma fração da massa do objeto)
      if(j==l):
        kazins[i][j][l] = K # EA/L (Constante de elasticidade do elemento, por ser homogêneo o material acaba sendo o mesmo)
      else:
        kazins[i][j][l] = -K # EA/L


Matriz_K = [[0 for x in range(elem)] for y in range(elem)]
Matriz_M = [[0 for x in range(elem)] for y in range(elem)]

# matriz global K
k1 = E*A/(0.25-0.15)
k2 = E*A/(0.50-0.25)
k3 = E*A/(0.20-0.00)
k4 = E*A/(1.00-0.85)
k5 = E*A/(0.85-0.50)
Matriz_K = [[k4+k5,0,0,-k5,-k4],
            [0,k3+k1,-k1,0,0],
            [0,-k1,k1+k2,-k2,0],
            [-k5,0,-k2,k2+k5,0],
            [-k4,0,0,0,k4]]

# ...

for i in range(elem):
  if i != (elem-1):
    Matriz_K[i][i] = kazins[i+1][0][0] + kazins[i][1][1]
    Matriz_M[i][i] = emezins[i+1][0][0] + emezins[i][1][1]
  else:
    Matriz_K[i][i] = kazins[i][1][1]
    Matriz_M[i][i] = emezins[i][1][1]
    
for i in range(elem-1):
  Matriz_K[i+1][i] = kazins[i+1][0][1]
  Matriz_K[i][i+1] = kazins[i+1][1][0]
  Matriz_M[i+1][i] = emezins[i+1][0][1]
  Matriz_M[i][i+1] = emezins[i+1][1][0]

Matriz_K_inversa = np.array(np.linalg.inv(Matriz_K))
Matriz_M_inversa = np.array(np.linalg.inv(Matriz_M))
 
# fim da criação das matrizes de rigidez global

F_intermediaria = [0 for i in range(elem)]
F_intermediaria[elem-1] = P

F = np.array(F_intermediaria)

Elementos_Deslocamento = []
Elementos_Deslocamento_Intermediario = []
Elementos_Velocidade = []
Elementos_Velocidade_Intermediario = []
Elementos_Aceleracao = []

Elementos_Velocidade.append(np.array([0 for i in range(elem)]))
Elementos_Deslocamento.append(np.array([0 for i in range(elem)]))
if M == 0.0 :
  Elementos_Aceleracao.append(F-K*Elementos_Deslocamento[0])
else :
  Elementos_Aceleracao.append((P-K*Elementos_Deslocamento[0])/M)
Elementos_Velocidade_Intermediario.append(np.array([0 for i in range(elem)]))
Elementos_Deslocamento_Intermediario.append(np.array([0 for i in range(elem)]))

# Apenas sem newmark, sem loop no tempo
#a = np.array(inv_K)
#b = np.array(F)
#Vetor_Deslocamento = a.dot(b)
#a = np.array(inv_M)
#b = np.array(F)
#Vetor_Aceleracao = a.dot(b)

# começo do metodo de newmark-beta
i = 0.0
i += step
n = 0
while i < T :
  A1 = Elementos_Deslocamento[n]
  A2 = step*Elementos_Velocidade[n]
  A3 = (1-2*beta)
  A4 = (step**2)/2
  A5 = Elementos_Aceleracao[n]*A3*A4
  A6 = A1+A2+A5
  Elementos_Deslocamento_Intermediario.append(A6)
  A1 = Elementos_Velocidade[n]
  A2 = step*(1-gama)*Elementos_Aceleracao[n]
  A3 = A1+A2
  Elementos_Velocidade_Intermediario.append(A3)
  aux = (M+gama*step*C+beta*(step**2)*K)
  if aux == 0.0 :
    aux = 1
  P = P0*charge_function(omega*i)
  Elementos_Aceleracao.append((P-C*Elementos_Velocidade_Intermediario[n+1]-K*Elementos_Deslocamento_Intermediario[n+1])/aux)
  Elementos_Deslocamento.append(Elementos_Deslocamento_Intermediario[n+1]+Elementos_Aceleracao[n+1]*beta*(step**2))
  Elementos_Velocidade.append(Elementos_Velocidade_Intermediario[n+1]+Elementos_Aceleracao[n+1]*gama*step)
  i += step
  n += 1
# fim do metodo de newmark-beta

x_axes = np.arange(0.0, T, step)

indexador = 9

Elemento_a_ser_mostrado = []
print(len(Elementos_Deslocamento));

for i in range(len(Elementos_Deslocamento)) :
  Elemento_a_ser_mostrado.append(Elementos_Deslocamento[i][indexador])

plt.plot(x_axes, Elemento_a_ser_mostrado)
plt.xlabel("Tempo")
plt.ylabel("Deslocamento do Elemento 9")
plt.show()	