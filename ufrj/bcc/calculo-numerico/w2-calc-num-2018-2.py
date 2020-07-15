#Trabalho 2 de Cálculo Numérico
#Realizado por:
#Pedro Paulo Soares Kastrup Ferreira
#Gabriel Silva Pereira

import numpy as np
import matplotlib.pyplot as plt
import math
#import matplotlib.animation as animation

#Saturaçao
S = 9.0
#Concentraçao inicial
I = 2.0
#Taxa de aeraçao
K = 0.88
# Nível de OD requisitado
N = 8.0
#Erro
E = 10**(-6)

np.seterr(all='raise')

#Funçao da concentração C(t)
def function_c(t):
	#return (1-(7)*np.exp(-0.88*t))
    try :
        #Termos da funçao como descrita no enunciado
        A = S-N
        B = S-I
        C = (-K)*t
        R = A-(B*np.exp(C))
        #retorno da funçao
        return R
    #Em caso de erro, avisa que ocorreu um overflow
    except :
        print("Overflow!")
        return 0

#Funçao da derivada da concentraçao C(t)
def derivative_function_c(t):
	#return (6.16*np.exp(-0.88*t))
    try :
        #Termos da derivada da funçao
        A = (-K)*(-(S-I))
        B = -K*t
        R = A*np.exp(B)
        #retorno da derivada funçao
        return R
    #Em caso de erro, avisa que ocorreu um overflow
    except :
        print("Overflow!")
        return 1

def newton_raphson(t0):
    #Lista com os valores de t (tempo) ao longo da realizaçao do método
    t = []
    #Lista com os valores da funçao C(t)
    a = []
    #Lista com os valores do Polinômio de Newton
    b = []
    t.append(t0)
    i = 1
    #Repetiçao do método com t[i-1] e atualizaçao das listas a cada iteraçao
    while i <= 100:
        u = function_c(t[i-1])
        v = derivative_function_c(t[i-1])
        t.append(t[i-1] - (u/v))
        a.append(v)
        b.append(u-v*t[i-1])
        print("Iteração de Newton Raphson "+str(i)+"º com t0 = "+str(t0)+" : "+ "{0:.7f}".format(t[i]))
        #Finaliza o loop caso diferença entre t[i-1] e t[i] seja menor que o erro proposto
        if abs(t[i-1]-t[i])<E or math.isnan(t[i]) == True :
            print("")
            break
        i=i+1
    #Desenho do gráfico do método    
    plot_func_with_tg(a, b)


#Funçao para desenhar o gráfico da funçao com suas tangentes
def plot_func_with_tg(a, b) :
    x_axes = np.linspace(0, 6, 100)
    fig, ax = plt.subplots()
    for i in range(len(a)):
        if math.isnan(b[i]) or math.isnan(a[i]) :
            break
        ax.plot(x_axes, (a[i]*x_axes+b[i]),"r:")
    ax.plot(x_axes, function_c(x_axes),"g-", label="Função C(t)")
    ax.axis([0, 5, -2, 6])
    ax.grid(True, which='both')
    ax.axhline(y=0, color='k')
    ax.axvline(x=0, color='k')
    plt.title("Método de Newton-Raphson")
    plt.legend()
    plt.show()
    plt.close('all')


#Chamadas para os valores de t propostos pelo trabalho
newton_raphson(1.0)

newton_raphson(5.0)
 
newton_raphson(10.0)
