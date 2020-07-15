from collections import deque
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import json
import datetime
import time
import ast
import os

### mágica do gráfico bonitinho
### ! funciona apenas em ambientes jupyter/colab !

#referencia para plotar uma o desenho dos processos elegantes 
#https://matplotlib.org/3.1.0/gallery/lines_bars_and_markers/broken_barh.html#sphx-glr-gallery-lines-bars-and-markers-broken-barh-py
#gráfico para o dict de processos
	log_data  = open('process-exec-' + str(hora) + '.log', 'r')
	
	log_data_df = pd.DataFrame.from_dict(log_data)
	status_filt = 'E'
	print(log_data_df)
	#print(sum_of_exec)
	new_data = []
	for elem in log_data:
		i = 0
		for key in elem:
			if elem[key] == 'E':
				i+=1
				new_data.append({'PID':int(key)-6495, 'clock': i}) 
	
	log_data_df = pd.DataFrame.from_dict(new_data)
	log_data_df.plot(kind='scatter',x='clock',y= 'PID',color='red')
	plt.show()

	new_df = log_data_df.style.apply(lambda x: ["background: red" if v == "E" else "" for v in x], axis = 1)
	new_df

	new_df_trans = log_data_df.T
	new_df_trans_color = new_df_trans.style.apply(lambda x: ["background: red" if v == "E" else "" for v in x], axis = 1)
	new_df_trans_color
