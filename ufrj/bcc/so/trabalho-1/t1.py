from collections import deque
import numpy as np
import json
import datetime
import time
import pandas as pd
from pandas.io.json import json_normalize
from pandas.compat import StringIO

# Tempo de cada unidade de tempo em milissegundos
universal_ut = 0.05

# tabela hash com todos os processos, suas respectivas informações e o PCB
LOP = {}

# Filas cujo irão guardar a referência do processo na LOP
high_kiwi = []  # prioridade alta
low_kiwi = []  # prioridade baixa
io_kiwi = []  # IO ( no caso guarda uma tupla )

# variaveis de controle

# lista que guarda
# PID
# qual IO do processo está sendo realizado
# tempo realizado de IO até então
p_on_io = [0, 0, 0]

# guarda o PID do processo em execução
p_on_processor = 0


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


# na criação do processo, nós utilizamos um dicionário aninhado. O dicionário externo guarda
# as informações de tempo de serviço, tempos de IO e o PCB. O dicionário interno é o PCB.
# As filas de prioridade são 3, sendo elas: fila de prioridade alta, prioridade baixa e fila de IO.
# Para implementar o feedback no escalonamento faremos: Processos novos vão para fila de prioridade
# alta, processos retornando de IO seguem de acordo com o seu IO. Como nosso número de processos é finito
# e pequeno, não haverá starvation dos processos na fila de baixa prioridade porque eventualmente serão
# atendidos.
def create_process(next_pid):
    ts = int(np.random.uniform(10, 100))
    processo = {
        'tempo_de_chegada': int(np.random.uniform(0, 50)),
        'tempo_de_serviço': ts,
        'tempo_executado': 0,
        'IO': randomize_ios(ts),
        'PCB': {
            'PID': 6496 + next_pid,  # Definição do PID é sequencial a partir do pai de todos
            'PPID': 6495,  # pai de todos ( pela numerologia de pitagoras - ODIN )
            'Status': 'P',  # Executando (E), Pronto (P), Bloqueado (B), Terminado (T) - processos
            # recém criados vão automaticamente para fila de Prontos e durante sua execução terão seus estados alterados
            'Prioridade': 'A'  # Alta (A) e Baixa (B) - processos recém criados tem automaticamente prioridade alta
        }
    }
    return processo


# função que coloca o processo em execução no processador durante sua fatia de tempo
# ou até que seja despachado para IO
def schedule_next_process():
    global p_on_processor
    if (len(high_kiwi) > 0):
        p_on_processor = high_kiwi.popleft()
        if (LOP[p_on_processor]['PCB']['Status'] == 'P'):
            LOP[p_on_processor]['PCB']['Status'] = 'E'
            print("Processo " + str(p_on_processor) + " entrou em execução via AP")
    elif (len(low_kiwi) > 0):
        p_on_processor = low_kiwi.popleft()
        if (LOP[p_on_processor]['PCB']['Status'] == 'P'):
            LOP[p_on_processor]['PCB']['Status'] = 'E'
            print("Processo " + str(p_on_processor) + " entrou em execução via BP")
    else:
        p_on_processor = 0


# função que troca o estado de Executando para Pronto, Bloqueado ou Terminado
def unschedule_current_process(pid):
    if (LOP[pid]['tempo_executado'] < LOP[pid]['tempo_de_serviço']):
        # vai para Pronto caso não esteja na fila de IO e para a fila de baixa prioridade
        # vai para Bloqueado caso esteja na fila de IO e para a fila de alta prioridade
        is_io = False
        for i in range(len(io_kiwi)):
            if (io_kiwi[i][0] == pid and pid != p_on_io[0]):
                LOP[pid]['PCB']['Status'] = 'B'
                LOP[pid]['PCB']['Prioridade'] = 'A'
                io_type = LOP[pid]['IO'][io_kiwi[i][1]][0]
                is_io = True
                # Disco - retorna para a fila de baixa prioridade
                if (io_type == 'D'):
                    low_kiwi.append(pid)
                # Fita magnética e Impressora - retorna para a fila de alta prioridade;
                elif (io_type == 'R' or io_type == 'M'):
                    high_kiwi.append(pid)
                print("Processo " + str(pid) + " bloqueado")
            # Processos que sofreram preempção – retornam na fila de baixa prioridade
        if (is_io == False):
            LOP[pid]['PCB']['Status'] = 'P'
            LOP[pid]['PCB']['Prioridade'] = 'B'
            low_kiwi.append(pid)
            print("Processo " + str(pid) + " pronto")

    else:
        # vai para Terminado caso o tempo executado seja igual ao tempo de serviço
        LOP[pid]['PCB']['Status'] = 'T'
        print("Processo " + str(pid) + " terminado")


# Função para dispachar os processos para seus respectivos serviços de IO pelo tempo
# de duração estipulado para cada um deles
def dispatch_process_io(pid):
    for i in range(len(LOP[pid]['IO'])):
        if (LOP[pid]['IO'][i][1] == LOP[pid]['tempo_executado']):
            io_kiwi.append([pid, i, 0])
            print("Processo " + str(pid) + " colocou IO na fila")


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
            LOP[p_on_io[0]]['PCB']['Status'] = 'B'
            # Disco - retorna para a fila de baixa prioridade
            if (io_type == 'D'):
                LOP[p_on_io[0]]['PCB']['Prioridade'] = 'B'
                low_kiwi.append(p_on_io[0])
            # Fita magnética e Impressora - retorna para a fila de alta prioridade;
            elif (io_type == 'R' or io_type == 'M'):
                LOP[p_on_io[0]]['PCB']['Prioridade'] = 'A'
                high_kiwi.append(p_on_io[0])

            if (len(io_kiwi) > 0):
                p_on_io = io_kiwi.popleft()
            else:
                p_on_io = [0, 0, 0]
        p_on_io[2] += 1


# função que atualiza o estado do processo dado o ciclo de clock
def step_current_process(pid):
    ret = False
    if (LOP[pid]['PCB']['Status'] == 'E'):
        for i in range(len(LOP[pid]['IO'])):
            if (LOP[pid]['IO'][i][1] == LOP[pid]['tempo_executado']):
                print("Processo " + str(pid) + " foi para IO")
                dispatch_process_io(pid)
                unschedule_current_process(pid)
                schedule_next_process()
                ret = True
        if (LOP[pid]['tempo_executado'] == LOP[pid]['tempo_de_serviço']):
            unschedule_current_process(pid)
            schedule_next_process()
            ret = True
        else:
            LOP[pid]['tempo_executado'] += 1
    return ret


# função que pega os processos iniciais e coloca na fila de alta prioridade
# Processos novos - entram na fila de alta prioridade
def check_arrival_time(c):
    for key in LOP:
        if (LOP[key]['tempo_de_chegada'] == c):
            high_kiwi.append(LOP[key]['PCB']['PID'])
            print("Processo " + str(LOP[key]['PCB']['PID']) + " chegou")


# função que testa se todos os processos já terminaram
def test_services_times(pid):
    finished_counter = 0
    if (len(LOP) == 0):
        return False
    is_over = True
    for i in LOP:
        if (LOP[i]['tempo_de_serviço'] == LOP[i]['tempo_executado']):
            finished_counter += 1
    if (finished_counter == len(LOP)):
        is_over = False
    else:
        is_over = True
    return is_over


# faz o print de um vetor com o pid e o estado do processo

def log_status(c, d):
    log = None
    log = open('process-exec-' + str(d) + '.log', 'a')
    vec_to_print = []
    for i in LOP:
        vec_to_print.append({LOP[i]['PCB']['PID']: LOP[i]['PCB']['Status']});
    log.write(str(vec_to_print) + '\n')
    log.close()

def log_processos(d):
    log = None
    log = open('process-info-' + str(d) + '.log', 'a')
    sum_of_info = '['
    for i in LOP:
        info_to_print = json.dumps(LOP[i]);
        sum_of_info += info_to_print + ','
        log.write(info_to_print + '\n')
    log.close()
    sum_of_info = sum_of_info[:-1]+ ']'
    return sum_of_info

if __name__ == "__main__":
    high_kiwi = deque([])
    low_kiwi = deque([])
    io_kiwi = deque([])

    for i in range(0, 10):
        aux_p = create_process(i)
        LOP[aux_p['PCB']['PID']] = aux_p

    clock = 0
    hora = int(round(time.time()*1000))
    while (test_services_times(p_on_processor) == True):
        check_arrival_time(clock)
        if (p_on_processor == 0):
            schedule_next_process()
        else:
            already_scheduled = step_current_process(p_on_processor)
            update_io_queue()
            if ((clock % 5) == 0 and already_scheduled == False):
                unschedule_current_process(p_on_processor)
                schedule_next_process()
        time.sleep(universal_ut)
        log_status(clock, hora)
        clock += 1
    info = log_processos(hora)

