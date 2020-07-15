#include <stdio.h>
#include <stdlib.h> 
#include <string.h>
#include <pthread.h>
#include "estrutura.h"

// Parte 1 - Funções do Sistema de Assento
//////////////////////////////////////////////////////////////////////////////////////////


Mapa* CriaVeiculo(char *n_nome, int n_quantidade) {
	Mapa* n_mapa = (Mapa *) malloc(sizeof(Mapa));
	if(n_mapa != NULL) {
		n_mapa->quantidade = n_quantidade;
		strcpy(n_mapa->nome, n_nome);
		n_mapa->poltronas = (Assento **) calloc(n_mapa->quantidade, sizeof(Assento *));
		for(n_quantidade = 0 ; n_quantidade < n_mapa->quantidade ; n_quantidade++) {
			n_mapa->poltronas[n_quantidade] = (Assento *) malloc(sizeof(Assento));
		}
	}
	return n_mapa;
}

int DestroiVeiculo(Mapa *n_veiculo) {

}

void visualizaAssentos(Mapa *v);

int alocaAssentoLivre(Assento *a, int id);

int alocaAssentoDado(Assento a, int id);

int liberaAssento(Assento a, int id);

//////////////////////////////////////////////////////////////////////////////////////////

// Parte 2 - Thread Usuária
//////////////////////////////////////////////////////////////////////////////////////////
void* usuario( void *arg ) {

	// variavel tid recebe o id da thread
	//int tid = *(int *) arg;
	int steps, detour;
	time_t t;

	// Inicializa o gerador de numeros aleatorios
	srand((unsigned) time(&t));
	steps = rand() % 6;
	while(steps > 0) {
		detour = rand() % 4;

		// Visualiza Assento		
		if(detour == 0) {

		}

		// Aloca Assento Livre
		else if(detour == 1) {

		}

		// Aloca Assento Dado
		else if(detour == 2) {

		}

		// Libera Assento
		else if(detour == 3) {

		}
		steps--;
	}
	pthread_exit(NULL);
}
//////////////////////////////////////////////////////////////////////////////////////////

// Parte 3 - Escritor de Log
//////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////

int main( int argc, char** args ) {
	printf("\n Some shit of code \n");
	return 0;
}
