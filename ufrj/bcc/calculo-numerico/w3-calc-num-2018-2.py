#Trabalho 3 de Cálculo Numérico
#Feito por:
#Gabriel Silva Pereira

import numpy as np
import matplotlib.pyplot as plt
import math

def function_T(x):
	k = 0.8109302162
	A = -k*x
	B = np.exp(A)
	return (60*B + 10)

def function_derivative_T(x, y):
	k = 0.8109302162
	return (-k*y + 10*k)

def function_euler_modified(X0, Y0, h, b, a):
	amount_steps = math.floor(((b-a)/h))
	vector_X = []
	vector_Y = []
	vector_X.append(X0)
	vector_Y.append(Y0)
	for i in range(amount_steps):
		temp_Y = vector_Y[i] + h*function_derivative_T(vector_X[i], vector_Y[i])
		vector_X.append(vector_X[i]+h)
		prox_Y = vector_Y[i]+(h/2)*(function_derivative_T(vector_X[i], vector_Y[i]) +
									function_derivative_T(vector_X[i+1], temp_Y))
		print("Diferença do valor analitico para o aproximado no ponto x = "+str(vector_X[i])+" : "+ str(function_T(vector_X[i]) - vector_Y[i]) )
		vector_Y.append(prox_Y)
	print('\n')
	return vector_X, vector_Y

def function_runge_kutta_4(X0, Y0, h, b, a):
	amount_steps = math.floor(((b-a)/h))
	vector_X = []
	vector_Y = []
	vector_X.append(X0)
	vector_Y.append(Y0)
	K1 = 0
	K2 = 0
	K3 = 0
	K4 = 0
	for i in range(amount_steps):
		K1 = function_derivative_T(vector_X[i], vector_Y[i])
		K2 = function_derivative_T(vector_X[i]+(h/2), vector_Y[i]+((h*K1)/2))
		K3 = function_derivative_T(vector_X[i]+(h/2), vector_Y[i]+((h*K2)/2))
		K4 = function_derivative_T(vector_X[i]+h, vector_Y[i]+(h*K3))
		prox_Y = vector_Y[i]+(h/6)*(K1+2*K2+2*K3+K4)
		print("Diferença do valor analitico para o aproximado no ponto x = "+str(vector_X[i])+" : "+ str(function_T(vector_X[i]) - vector_Y[i]) )
		vector_Y.append(prox_Y)
		vector_X.append(vector_X[i]+h)
	print('\n')
	return vector_X, vector_Y

def fill_plot(vector_X, vector_Y, title, start, end, start_y, end_y):
	x_axes = np.linspace(start, end, 100)
	plt.plot(x_axes, function_T(x_axes), "g-")
	plt.plot(vector_X, vector_Y, "bo")	
	plt.axis([start, end, start_y, end_y])
	plt.grid(True, which='both')
	plt.xlabel('Tempo t')
	plt.ylabel('Temperatura T')
	plt.axhline(y=0, color='k')
	plt.axvline(x=0, color='k')
	plt.title(title)
	plt.show()

def application():
	VX = []
	VY = []

	VX, VY = function_euler_modified(0, 70, 0.5, 3, 0)
	fill_plot(VX, VY, "Euler modificado com passo 0.5", -1, 4, 15, 75)
	VX, VY = function_runge_kutta_4(0, 70, 0.5, 3, 0)
	fill_plot(VX, VY, "Runge Kutta de 4 ordem com passo 0.5", -1, 4, 15, 75)

	VX, VY = function_euler_modified(0, 70, 0.5, 3, 0)
	fill_plot(VX, VY, "Euler modificado com passo 0.5", 2.4, 3.1, 14, 20)
	VX, VY = function_runge_kutta_4(0, 70, 0.5, 3, 0)
	fill_plot(VX, VY, "Runge Kutta de 4 ordem com passo 0.5", 2.4, 3.1, 14, 20)

	VX, VY = function_euler_modified(0, 70, 0.2, 3, 0)
	fill_plot(VX, VY, "Euler modificado com passo 0.2", -1, 4, 15, 75)
	VX, VY = function_runge_kutta_4(0, 70, 0.2, 3, 0)
	fill_plot(VX, VY, "Runge Kutta de 4 ordem com passo 0.2", -1, 4, 15, 75)

	VX, VY = function_euler_modified(0, 70, 0.2, 3, 0)
	fill_plot(VX, VY, "Euler modificado com passo 0.2", 2.7, 3.1, 14, 17)
	VX, VY = function_runge_kutta_4(0, 70, 0.2, 3, 0)
	fill_plot(VX, VY, "Runge Kutta de 4 ordem com passo 0.2", 2.7, 3.1, 14, 17)

	VX, VY = function_euler_modified(0, 70, 0.1, 3, 0)
	fill_plot(VX, VY, "Euler modificado com passo 0.1", -1, 4, 15, 75)
	VX, VY = function_runge_kutta_4(0, 70, 0.1, 3, 0)
	fill_plot(VX, VY, "Runge Kutta de 4 ordem com passo 0.1", -1, 4, 15, 75)

	VX, VY = function_euler_modified(0, 70, 0.1, 3, 0)
	fill_plot(VX, VY, "Euler modificado com passo 0.1", 2.95, 3.05, 14, 16)
	VX, VY = function_runge_kutta_4(0, 70, 0.1, 3, 0)
	fill_plot(VX, VY, "Runge Kutta de 4 ordem com passo 0.1", 2.95, 3.05, 14, 16)

application()