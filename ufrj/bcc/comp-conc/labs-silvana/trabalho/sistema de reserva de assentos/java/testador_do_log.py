#!/usr/bin/python

import sys

class Log:
	def __init__(self, o, u, s, c):
		self.seat = s
		self.user = u
		self.cart = c
		self.oper = o

def testador_do_log(nome):
	arquivo = open(nome, 'r')
	lista_log = arquivo.read().split('\n')
	arquivo.close()

	resposta = False
	reproducao = [0] * len(lista_log[0].replace('[', '').replace(']', '').split(',')[3:])

	head = None
	tail = None
	for i in range(len(lista_log)-1):
		raw = lista_log[i].replace('[', '').replace(']', '').split(',')

		if i == 0 :
			head = Log(int(raw[0]), int(raw[1]), int(raw[2]), [int(x) for x in raw[3:]])
		else :
			tail = head
			head = Log(int(raw[0]), int(raw[1]), int(raw[2]), [int(x) for x in raw[3:]])
			
		if head.oper == 1 :
			if tail == None :
				resposta = True
			elif head.cart == tail.cart :
				resposta = True
			else :
				resposta = False
		elif head.oper == 2 :
			reproducao[head.seat-1] = head.user
		elif head.oper == 3 :
			reproducao[head.seat-1] = head.user
		elif head.oper == 4 :
			reproducao[head.seat-1] = 0
		else :
			resposta = False

		if reproducao == head.cart :
			resposta = True
		else :
			resposta = False

	print resposta



if __name__ == "__main__":
	import sys
	testador_do_log(sys.argv[1])