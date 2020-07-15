#include <stdio.h>
#include <stdlib.h> 
#include <string.h>
#include <pthread.h>
#include "estrutura.h"

//Variáveis Globais (área de memória compartilhada)
Mapa* veiculo; //Mapa de assentos
pthread_mutex_t* mutex_read;

Mapa* CriaVeiculo(int n_quantidade){
	puts("Chamou");
	Mapa* n_mapa = (Mapa *) malloc(sizeof(Mapa));
	if(n_mapa != NULL) {
		n_mapa->quantidade = n_quantidade;
		n_mapa->poltronas = (Assento **) calloc(n_mapa->quantidade, sizeof(Assento *));
		for(n_quantidade = 0 ; n_quantidade < n_mapa->quantidade ; n_quantidade++) {
			n_mapa->poltronas[n_quantidade] = (Assento *) malloc(sizeof(Assento));
			n_mapa->poltronas[n_quantidade]->pos = n_quantidade;
		}
	}
	return n_mapa;
}

int DestroiVeiculo(Mapa *n_veiculo);

void visualizaAssentos(Mapa *v){
	int i, estado;
	int qtd = v->quantidade;
	Assento** assentos = v->poltronas;
	
	printf("[");
	for(i = 0; i < 	qtd; i++){
		estado = assentos[i][0].tid;
		printf("%d", estado);
		if(i + 1 != qtd)
			printf(", ");
	}
	printf("]\n");		
}

int alocaAssentoLivre(Mapa *v, int id) {
	int onde_coloca, tentativas=v->quantidade;
	time_t t;
	srand((unsigned) time(&t));
	while(tentativas > 0) {
		onde_coloca = rand() % v->quantidade;
		if(v->poltronas[onde_coloca][0].tid == 0) {
			v->poltronas[onde_coloca][0].estado = 1;
			v->poltronas[onde_coloca][0].pos = onde_coloca+1;
			v->poltronas[onde_coloca][0].tid = id;
			return 1;
		}
		tentativas--;
	}
	return 0;
}

int alocaAssentoDado(Assento a, int id){
	if(a.tid == 0){
		a.estado = 1;
		//a.pos = ?
		a.tid = id;
		printf("%d\n", a.tid);
		return 1;
		
	} else {
		printf("Assento %d já está ocupado pelo usuário %d\n", a.pos, a.tid);
	}
	return 0;
}

int liberaAssento(Assento a, int id);

int main(int argc, char* argv[]){
	int num_poltronas;
	
	if(argc < 2){
		puts("Insira o número de Assentos como argumento");
		return 1;
	}
	num_poltronas = atoi(argv[1]);
	veiculo = CriaVeiculo(num_poltronas);
	visualizaAssentos(veiculo);
	alocaAssentoLivre(veiculo, 2);
	visualizaAssentos(veiculo);
	alocaAssentoDado(veiculo->poltronas[0],4 );
	visualizaAssentos(veiculo);	
	return 0;	
}
