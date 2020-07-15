#!/usr/bin/env python
# -*- coding: utf-8 -*-

#Primeiro Trabalho de Sistemas Operacionais I
#Professora: Valeria Bastos
#Grupo: Gabriel Silva - DRE 115192431
#		Thamires Bessa - DRE 113032431

# Premissas do trabalho 1
#	PCB - dicionário python
#	filas de prioridades - listas
#	fatia de tempo - 5ut
#	número de processos - 10
#	tempo de serviço - aleatório entre 0 e 100 - seguindo distribuição uniforme(0,100)
#	tempos de I/O - Impressora: 15ut; Fita: 10ut; Disco: 1ut
#	maximo de 5 pedidos de I/O por processo (aleatório entre 0 e 5)
#	Filas: 3 filas, dessas 1 de alta prioridade, 1 de baixa prioridade, 1 de I/O que se dividem em:
#		I/O longo e I/O curto
#
#PPID seria apenas pra identificar o pai de todos (8379) pelo qual é o primeiro processo a entrar na fila e possui tempo de serviço = 1ut
#
#Process Control Block será um dicionário para identificar o processo e suas características
#PCB
#   PID
#   PPID
#   STATUS (Executando, Pronto, Bloqueado, Pronto/Suspenso, Bloqueado/Suspenso)
#   PRIORIDADE (Alta, Baixa)
#   I/O [lista de IOs]

# Premissas do trabalho 2
#	Memória principal - lista de 'frames' com os PIDs dos processos
#	Spawn de processo a cada 3 segundos - função spawn_process associada a um contador de tempo global
#	WSL de 4 páginas - lista com 4 objetos, que representa cada objeto uma 'página', no máximo dentro do PCB cujo será guardado como uma tripla na seguinte forma :
#		- numéro da página referenciando qual das páginas que está alocada
#		- indice de referência da página na memória principal ('endereço real')
#		- clock da última vez que foi referenciado
#	Páginas virtuais do processo - incluir no PCB a quantidade de até 64 'páginas' em uma variável e uma sequência do pedido de páginas, sendo random a quantidade de páginas
#	política de realocação - LRU tanto para o 'swap' quanto para o 'paging'
#	swap out - retirar todo o WSL
#	swap in - colocar todo o WSL

from collections import deque
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import json
import datetime
import time
import ast
import os

# clock que controla toda o simulador
clock = 0

# Tempo de cada unidade de tempo em milissegundos
universal_ut = 0.01

# total de processos a ser criado
total_process = 50

# variavel gambiarra para criação de processos em momentos distintos da execução
global_process_creation_iterator = 0

# tabela hash com todos os processos, suas respectivas informações e o PCB
LOP = {}

# memória principal
MP = []

# Filas cujo irão guardar a referência do processo na LOP ( o PID )
high_kiwi = []  # prioridade alta
low_kiwi = []  # prioridade baixa
io_kiwi = []  # IO ( no caso guarda uma tripla com PID, ID do IO, tempo)

# variaveis de controle

# lista que guarda
# PID
# qual IO do processo está sendo realizado
# tempo realizado de IO até então
p_on_io = [0, 0, 0]

# guarda o PID do processo em execução
p_on_processor = 0

# variável para controle de fatia de tempo de execução
time_slice = 0


# temos os tempos fixos de duração de cada IO. O tempo de serviço de cada processo
# é aleatório, uniforme, entre 10 e 100 ut. A quantidade de IO que cada processo vai
# ter é aleatório, uniforme, entre 0 e 5. Dentro disso, temos que saber quando se darão
# essas saídas de IO, então resolvemos randomizar também. Ou seja, pegamos a quantidade de IOs
# aleatórias que foram sorteadas e sorteamos instantes dentro do tempo de serviço onde haverão
# saídas para IO.
def randomize_ios(service_time):
	types_of_io = ["D", "R", "M"]  # tempo de IO são respectivamente 1, 15 e 10
	# D = IO de Disco
	# R = IO de impRessora
	# M = IO de fita Magnetica
	list_of_ios = []
	num_of_ios = int(np.random.uniform(0, 5))
	for i in range(num_of_ios):
		io_type = types_of_io[int(np.random.uniform(0, 3))]
		io_p_time = int(np.random.uniform(0, service_time))
		list_of_ios.append((io_type, io_p_time))
	return list_of_ios


# me retorna a quantidade randômica de páginas que um processo deve possuir
def randomize_pages():
	num_of_pages = int(np.random.uniform(1, 65))
	return num_of_pages

# me gera a sequencia de páginas dado a quantidade de páginas existentes e o tempo de execução do processo
def generate_sequence_of_pages(q_pages, s_time):
	queue_sequence = []
	for i in range(s_time):
		queue_sequence.append(int(np.random.uniform(1, (q_pages+1) )))
	return queue_sequence

# na criação do processo, nós utilizamos um dicionário aninhado. O dicionário externo guarda
# as informações de tempo de serviço, tempos de IO e o PCB. O dicionário interno é o PCB.
# As filas de prioridade são 3, sendo elas: fila de prioridade alta, prioridade baixa e fila de IO.
# Para implementar o feedback no escalonamento faremos: Processos novos vão para fila de prioridade
# alta, processos retornando de IO seguem de acordo com o seu IO. Como nosso número de processos é finito
# e pequeno, não haverá starvation dos processos na fila de baixa prioridade porque eventualmente serão
# atendidos.
def create_process(amount):
	global global_process_creation_iterator
	for i in range(amount):
		ts = int(np.random.uniform(10, 100))
		rp = randomize_pages()
		processo = {
			'tempo_de_chegada': clock+int(np.random.uniform(5, 30)),
			'tempo_de_servico': ts,
			'tempo_executado': 0,
			'IO': randomize_ios(ts),
			'PCB': {
				'PID': 6496 + global_process_creation_iterator,  # Definição do PID é sequencial a partir do pai de todos
				'PPID': 6495,  # pai de todos ( pela numerologia de pitagoras - ODIN )
				'Status': 'N',  # Novo (N), Executando (E), Pronto (P), Bloqueado (B), Pronto/Suspenso (K), Bloqueado/Suspenso (Y), Terminado (T)
				# recém criados vão automaticamente para fila de Prontos e durante sua execução terão seus estados alterados
				'Prioridade': 'A',  # Alta (A) e Baixa (B) - processos recém criados tem automaticamente prioridade alta
				'WSL': [ [0, -1, 0], [0, -1, 0], [0, -1, 0], [0, -1, 0]],
				'quantidade_de_paginas' : rp, # quantidade de páginas que o processo possuí, entre 1 a 64 páginas
				'sequencia_de_paginas' : generate_sequence_of_pages(rp, ts), # sequencia de páginas cujo cada página é referenciada em um u.t. diferente, por isso o tempo de serviço é passado como parametro
				'ultimo_acesso' : clock # ultimo acesso é um nome ruim, porém é o que melhor pensei para representar tal variável que guarda a última vez que alguma página desse processo referenciou a memória
			}
		}
		global_process_creation_iterator += 1
		LOP[processo['PCB']['PID']] = processo


# função que coloca o processo em execução no processador durante sua fatia de tempo
# ou até que seja despachado para IO
def schedule_next_process():
	global p_on_processor
	global time_slice
	time_slice = 0

	if (len(high_kiwi) > 0):
		if (LOP[high_kiwi[0]]['PCB']['Status'] == 'P'):
			LOP[high_kiwi[0]]['PCB']['Status'] = 'E'
			p_on_processor = high_kiwi.popleft()
		elif(LOP[high_kiwi[0]]['PCB']['Status'] == 'K'):
			LOP[high_kiwi[0]]['PCB']['Status'] = 'E'
			p_on_processor = high_kiwi.popleft()
			do_swap_in(p_on_processor)
		print("\tProcesso " + str(p_on_processor) + " entrou em execução via AP")
	elif (len(low_kiwi) > 0):
		if (LOP[low_kiwi[0]]['PCB']['Status'] == 'P'):
			LOP[low_kiwi[0]]['PCB']['Status'] = 'E'
			p_on_processor = low_kiwi.popleft()
		elif (LOP[low_kiwi[0]]['PCB']['Status'] == 'K'):
			LOP[low_kiwi[0]]['PCB']['Status'] = 'E'
			p_on_processor = low_kiwi.popleft()
			do_swap_in(p_on_processor)
		print("\tProcesso " + str(p_on_processor) + " entrou em execução via BP")
	else:
		p_on_processor = 0

# função que troca o estado de Executando para Pronto, Bloqueado ou Terminado
def unschedule_current_process(pid):
	global p_on_processor
	if (LOP[pid]['tempo_executado'] < LOP[pid]['tempo_de_servico']):
		# vai para Pronto caso não esteja na fila de IO e para a fila de baixa prioridade
		# vai para Bloqueado caso esteja na fila de IO e para a fila de alta prioridade
		is_io = False
		for i in range(len(io_kiwi)):
			if (is_io == False and io_kiwi[i][0] == pid and pid != p_on_io[0]):
				LOP[pid]['PCB']['Status'] = 'B'
				LOP[pid]['PCB']['Prioridade'] = 'A'
				io_type = LOP[pid]['IO'][io_kiwi[i][1]][0]
				is_io = True
				
				# Disco - retorna para a fila de baixa prioridade
				if (io_type == 'D'):
					# !!!
					# este trecho estava gerando duplicata de processos nas filas
					# !!!
					#low_kiwi.append(pid)
					print("\tProcesso " + str(pid) + " mudou para o estado Bloqueado")
				# Fita magnética e Impressora - retorna para a fila de alta prioridade;
				elif (io_type == 'R' or io_type == 'M'):
					# !!!
					# este trecho estava gerando duplicata de processos nas filas
					# !!!
					#high_kiwi.append(pid)
					print("\tProcesso " + str(pid) + " mudou para o estado Bloqueado")

				p_on_processor = 0
		# Processos que sofreram preempção – retornam na fila de baixa prioridade
		if (is_io == False):
			LOP[pid]['PCB']['Status'] = 'P'
			LOP[pid]['PCB']['Prioridade'] = 'B'
			low_kiwi.append(pid)
			print("\tProcesso " + str(pid) + " volta para a fila de BP e estado Pronto")
			p_on_processor = 0

	else:
		# vai para Terminado caso o tempo executado seja igual ao tempo de serviço
		LOP[pid]['PCB']['Status'] = 'T'
		do_swap_out(pid)
		print("\tProcesso " + str(pid) + " mudou para o estado Terminado")
		p_on_processor = 0


# Função para dispachar os processos para seus respectivos serviços de IO pelo tempo
# de duração estipulado para cada um deles
def dispatch_process_io(pid):
	for i in range(len(LOP[pid]['IO'])):
		if (LOP[pid]['IO'][i][1] == LOP[pid]['tempo_executado']):
			io_kiwi.append([pid, i, 0])
			io_type = LOP[pid]['IO'][i][0]
			print("\tProcesso " + str(pid) + " colocou o IO["+str(i)+"] de "+('fita magnética' if io_type == 'M' else 'disco' if io_type == 'D' else 'impressora')+" na fila")


# função que atualiza a fila de IO e escalona os processos para IO
def update_io_queue():
	global p_on_io
	if (p_on_io[0] == 0 and len(io_kiwi) > 0):
		p_on_io = io_kiwi.popleft()
	elif (p_on_io[0] != 0):
		# testar se o IO atual acabou
		io_type = LOP[p_on_io[0]]['IO'][p_on_io[1]][0]
		time_in_io = ord(io_type) - 67
		# verifica se o contador chegou no limite do tempo daquele tipo de IO
		if (p_on_io[2] >= time_in_io):
			# ??? LOP[p_on_io[0]]['PCB']['Status'] = 'B'
			# Disco - retorna para a fila de baixa prioridade
			if (io_type == 'D'):
				
				if(LOP[p_on_io[0]]['PCB']['Status'] == 'B'):
					LOP[p_on_io[0]]['PCB']['Prioridade'] = 'B'
					low_kiwi.append(p_on_io[0])
					LOP[p_on_io[0]]['PCB']['Status'] = 'P'
					print("\tProcesso "+str(p_on_io[0])+" volta para a fila de BP e muda para o estado Pronto")
				elif(LOP[p_on_io[0]]['PCB']['Status'] == 'Y'):
					LOP[p_on_io[0]]['PCB']['Prioridade'] = 'B'
					low_kiwi.append(p_on_io[0])
					LOP[p_on_io[0]]['PCB']['Status'] = 'K'
					print("\tProcesso "+str(p_on_io[0])+" volta para a fila de BP e muda para o estado Pronto/Suspenso")
			# Fita magnética e Impressora - retorna para a fila de alta prioridade;
			elif (io_type == 'R' or io_type == 'M'):
				
				if(LOP[p_on_io[0]]['PCB']['Status'] == 'B'):
					LOP[p_on_io[0]]['PCB']['Prioridade'] = 'A'
					high_kiwi.append(p_on_io[0])
					LOP[p_on_io[0]]['PCB']['Status'] = 'P'
					print("\tProcesso "+str(p_on_io[0])+" volta para a fila de AP e muda para o estado Pronto")
				elif(LOP[p_on_io[0]]['PCB']['Status'] == 'Y'):
					LOP[p_on_io[0]]['PCB']['Prioridade'] = 'A'
					low_kiwi.append(p_on_io[0])
					LOP[p_on_io[0]]['PCB']['Status'] = 'K'
					print("\tProcesso "+str(p_on_io[0])+" volta para a fila de AP e muda para o estado Pronto/Suspenso")

			if (len(io_kiwi) > 0):
				p_on_io = io_kiwi.popleft()
			else:
				p_on_io = [0, 0, 0]
		print("\t\tPID = "+str(p_on_io[0])+", IO["+str(p_on_io[1])+"], tempo = "+str(p_on_io[2]))
		p_on_io[2] += 1


# função que atualiza o estado do processo dado o ciclo de clock
def step_current_process(pid):
	global time_slice
	ret = False
	if (LOP[pid]['PCB']['Status'] == 'E'):
		pag = update_WSL(pid)
		LOP[pid]['ultimo_acesso'] = clock
		LOP[pid]['tempo_executado'] += 1
		time_slice += 1
		print("\tPID("+str(pid)+") - u.t.("+str(time_slice)+") - pagina referenciada = "+ str(pag[0])+" - page fault ? "+ ("N" if pag[1]==True else "S"))
		for i in range(len(LOP[pid]['IO'])):
			if (ret == False and LOP[pid]['IO'][i][1] == LOP[pid]['tempo_executado']):
				print("\tProcesso " + str(pid) + " foi para IO")
				dispatch_process_io(pid)
				unschedule_current_process(pid)
				schedule_next_process()
				ret = True
		if (ret == False and LOP[pid]['tempo_executado'] == LOP[pid]['tempo_de_servico']):
			unschedule_current_process(pid)
			schedule_next_process()
			ret = True
	return ret

# atualiza/realoca as páginas(WSL) do processo
def update_WSL(pid):
	is_on_wsl = False
	# saber se a página da iteração de passo do u.t. está no WSL
	pag = LOP[pid]['PCB']['sequencia_de_paginas'][0]
	LOP[pid]['PCB']['sequencia_de_paginas'] = LOP[pid]['PCB']['sequencia_de_paginas'][1:]
	for i in range(4):
		# se tiver atualiza o clock
		if(LOP[pid]['PCB']['WSL'][i][0] == pag):
			LOP[pid]['PCB']['WSL'][i][2] = clock
			is_on_wsl = True
			### printar qual pagina foi referencia naquele u.t.
	# caso contrário utiliza a política de LRU para realocar dentro da WSL
	### printar q deu page fault
	if(is_on_wsl == False):
		min_clock = 50000
		which_j = 5
		#verifico se está cheio
		wsl_is_full = True
		for j in range(4):
			if(LOP[pid]['PCB']['WSL'][j][0] == 0):
				wsl_is_full = False
				LOP[pid]['PCB']['WSL'][j] = [pag, LOP[pid]['PCB']['WSL'][j][1], clock]
		# verifico qual é mais antigo (LRU), porém é necessário antes saber se está cheio
		if(wsl_is_full):
			for j in range(4):
				if(LOP[pid]['PCB']['WSL'][j][2] < min_clock):
					which_j = j
					min_clock = LOP[pid]['PCB']['WSL'][j][2]
			# se não achar o menor, que é uma contradição, vai dar out_of_index_bound error
			LOP[pid]['PCB']['WSL'][which_j] = [pag, LOP[pid]['PCB']['WSL'][which_j][1], clock]
	return [pag, is_on_wsl]

# realiza o swap in na memória principal
def do_swap_in(pid):
	i = 0
	allocated = False
	# procura espaço vazio na memória
	while(allocated == False and i < 64):
		if(MP[i] == 0):
			MP[i] = pid
			MP[i+1] = pid
			MP[i+2] = pid
			MP[i+3] = pid
			LOP[pid]['PCB']['WSL'][0][1] = i
			LOP[pid]['PCB']['WSL'][1][1] = i+1
			LOP[pid]['PCB']['WSL'][2][1] = i+2
			LOP[pid]['PCB']['WSL'][3][1] = i+3
			print("\tProcesso " + str(pid) + " na MP["+str(i)+"]")
			allocated = True
		i+=4
	
	# caso não ache espacço vazio verifica por LRU qual processo a ser retirado
	if(allocated == False):
		proc_to_swap_out = 0
		# verifica na fila de baixa prioridade com estado Pronto
		proc_to_swap_out = check_lru('L', 'P')
		if(proc_to_swap_out == 0):
			# verifica na fila de alta prioridade com estado Bloqueado
			proc_to_swap_out = check_lru('H', 'B')
			if(proc_to_swap_out == 0):
				# verifica na fila de baixa prioridade com estado Bloqueado
				proc_to_swap_out = check_lru('L', 'B')
				if(proc_to_swap_out == 0):
					# verifica na fila de alta prioridade com estado Pronto
					proc_to_swap_out = check_lru('H', 'P')
		# efetivamente retira tal processo encontrado pela LRU
		if(do_swap_out(proc_to_swap_out)):
			# chama novamente essa mesma função para colocar o processo na memória	
			do_swap_in(pid)

# função que implementa o LRU: dado o parametro ele pesquisa em qual fila e qual a Status é preferencial
def check_lru(kiwi, status):
	min_clock = 50000
	proc_to_swap_out = 0
	for i in (high_kiwi if kiwi == 'H' else low_kiwi):
		if(LOP[i]['PCB']['ultimo_acesso'] < min_clock and LOP[i]['PCB']['Status'] == status):
			proc_to_swap_out = i
			min_clock = LOP[i]['PCB']['ultimo_acesso']
	return proc_to_swap_out

# realiza o swap out da memória principal dado o processo encontrado no swap in
def do_swap_out(pid):
	i = 0
	found = False
	while(found == False and i < 64):
		if(MP[i] == pid):
			MP[i] = 0
			MP[i+1] = 0
			MP[i+2] = 0
			MP[i+3] = 0
			LOP[pid]['PCB']['WSL'][0][1] = -1
			LOP[pid]['PCB']['WSL'][1][1] = -1
			LOP[pid]['PCB']['WSL'][2][1] = -1
			LOP[pid]['PCB']['WSL'][3][1] = -1
			if(LOP[pid]['PCB']['Status'] == 'P'):
				LOP[pid]['PCB']['Status'] = 'K'
				print("\tProcesso " + str(pid) + " mudou para o estado Pronto/Suspenso")
			elif(LOP[pid]['PCB']['Status'] == 'B'):
				LOP[pid]['PCB']['Status'] = 'Y'
				print("\tProcesso " + str(pid) + " mudou para o estado Bloqueado/Suspenso")
			found = True
		i+=4
	return found

# avalia se a memória está cheia
def check_if_mem_is_full():
	ret = True
	for i in range(64):
		if(MP[i] == 0):
			ret = False
	return ret

# função que pega os processos iniciais e coloca na fila de alta prioridade
# Processos novos - entram na fila de alta prioridade
def check_arrival_time(c):
	# a cada ciclo ele sempre checa se existe um processo na cabeça da 
	# fila de alta prioridade no estado Bloqueado/Suspenso
	if(len(high_kiwi) > 0):
		if(LOP[high_kiwi[0]]['PCB']['Status'] == 'Y'):
			LOP[high_kiwi[0]]['PCB']['Status'] = 'B'
			do_swap_in(high_kiwi[0])
			print("\tProcesso " + str(high_kiwi[0]) + " mudou de Bloqueado/Suspenso para Bloqueado!")

	for key in LOP:
		if ((LOP[key]['tempo_de_chegada']-1) == c): # -1 por conta de que ele deve ser executado no instante q ele chegou de fato caso ja esteja disponivel para execuçãos
			# testo se a memória está cheia para decidir se o 'Status' = P ou = K
			if(check_if_mem_is_full()):
				LOP[key]['PCB']['Status'] = 'K'
				high_kiwi.append(LOP[key]['PCB']['PID'])
				print("\tProcesso " + str(LOP[key]['PCB']['PID']) + " chegou e mudou para o estado Pronto/Suspenso")
			else:
				LOP[key]['PCB']['Status'] = 'P'
				high_kiwi.append(LOP[key]['PCB']['PID'])
				do_swap_in(LOP[key]['PCB']['PID'])
				print("\tProcesso " + str(LOP[key]['PCB']['PID']) + " chegou e mudou para o estado Pronto")


# função que testa se todos os processos já terminaram
def test_services_times():
	finished_counter = 0
	if (len(LOP) == 0):
		return False
	is_over = True
	for i in LOP:
		if (LOP[i]['tempo_de_servico'] == LOP[i]['tempo_executado']):
			finished_counter += 1
	if (finished_counter == len(LOP) and finished_counter >= total_process):
		is_over = False
	else:
		is_over = True
	return is_over


# faz o print de um vetor com o pid e o estado do processo
def log_status(c, d):
	log = None
	log = open('process-exec-' + str(d) + '.log', 'a')
	vec_to_print = '{'
	for i in LOP:
		vec_to_print += "'"+str(LOP[i]['PCB']['PID'])+"':'"+LOP[i]['PCB']['Status']+"',"
	ret = vec_to_print[:-1] + '}\n'
	# pareiaquio
	log.write(ret)
	log.close()
	return ret

# cria o arquivo com o JSON dos processos
def log_processos(d):
	log = None
	log = open('process-info-' + str(d) + '.log', 'a')
	sum_of_info = '['
	log.write('\n\n[')
	for i in LOP:
		info_to_print = json.dumps(LOP[i]);
		sum_of_info += info_to_print + ','
		log.write(info_to_print + ',\n')
	log.seek(log.tell()-2)
	log.truncate()
	log.write(']')
	log.close()
	sum_of_info = sum_of_info[:-1]+ ']'
	return sum_of_info

# função que carrega o processo a partir do log de informação
def load_process(file_index):
	log = None
	log = open('process-info-'+str(file_index)+'.log', 'r')
	readed = log.read()
	raw_LOP = json.loads(readed)
	for i in range(len(raw_LOP)):
		LOP[raw_LOP[i]['PCB']['PID']] = raw_LOP[i]

if __name__ == "__main__":
	clock = 0
	time_counter = 0
	hora = int(round(time.time()*1000))%100000

	high_kiwi = deque([])
	low_kiwi = deque([])
	io_kiwi = deque([])
	log_data = []
	# cria processos aleatoriamente
	handle_amount_process_to_create = 0

	created_in_this_trigger = int(np.random.uniform(1, 20))
	handle_amount_process_to_create += created_in_this_trigger
	create_process(created_in_this_trigger)
	info = log_processos(hora)
	
	# carrega processos do arquivo de log de informações anteriores
	#load_process(70717)

	# cria/inicializa a memória principal
	for i in range(64):
		MP.append(0);
	
	while (test_services_times() == True):
		print("Tempo : "+str(clock))
		check_arrival_time(clock)
		if (p_on_processor == 0):
			schedule_next_process()
			update_io_queue()
		else:
			already_scheduled = step_current_process(p_on_processor)
			update_io_queue()
			if ( time_slice == 5 and already_scheduled == False): # add-on-wk2 : antes era (clock % 5) == 0. agora o time slice é controlado pra cada processo em execução ao inves de ser um clock divisivel por 5
				unschedule_current_process(p_on_processor)
				schedule_next_process()
		#
		time.sleep(universal_ut)
		if(time_counter % 300 == 0 and handle_amount_process_to_create <= total_process):
			handle_amount_process_to_create += created_in_this_trigger
			create_process(int(created_in_this_trigger))
			info = log_processos(hora)

		stamp = log_status(clock, hora)
		log_data.append(ast.literal_eval(stamp))
		clock += 1
		time_counter += 1
		
		rlkp = "\t Memória Principal = [ "
		for i in range(len(MP)):
			rlkp += ' '+str(MP[i])+','
		rlkp = rlkp[:-1]
		rlkp += ']'
		print(rlkp)

		rlkp = "\t fila de BP = [ "
		for i in low_kiwi:
			rlkp += ' '+str(i)+'('+LOP[i]['PCB']['Status']+'),'
		rlkp = rlkp[:-1]
		rlkp += ']'
		print(rlkp)
		
		rlkp = "\t fila de AP = [ "
		for i in high_kiwi:
			rlkp += ' '+str(i)+'('+LOP[i]['PCB']['Status']+'),'
		rlkp = rlkp[:-1]
		rlkp += ']'
		print(rlkp)

		rlkp = "\t fila de IO = [ "
		for i in io_kiwi:
			rlkp += ' '+str(i[0])+'('+LOP[i[0]]['PCB']['Status']+'),'
		rlkp = rlkp[:-1]
		rlkp += ']'
		print(rlkp)
		print("p on processor = "+str(p_on_processor))
		print("p on io = "+str(p_on_io))
		
	info = log_processos(hora)
