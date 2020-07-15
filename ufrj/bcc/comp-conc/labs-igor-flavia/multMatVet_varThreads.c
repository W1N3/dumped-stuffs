#include <stdio.h>
#include <stdlib.h> 
#include <pthread.h>

#define NTHREADS  5
#define dimN NTHREADS
#define dimM 4

// Declara matrizes e vetores
int matA[dimN][dimM];
int vetX[dimM];
int vetB[dimN];
  
//cria a estrutura de dados para armazenar os argumentos da thread
typedef struct {int idThread, nThreads;} t_Args;

//funcao executada pelas threads
void *PrintHello (void *arg) {
  t_Args *args = (t_Args *) arg;
  int id = args->idThread;
  free(arg);
  
  int i;
  for(i=0; i<dimM; i++) {vetB[id] += matA[id][i] * vetX[i];}
  
  pthread_exit(NULL);
}

//funcao principal do programa
int main() {
  pthread_t tid_sistema[NTHREADS];
  int t, t2;
  t_Args *arg; //receberá os argumentos para a thread
  
  // inicializa matrizes e vetores
  printf("Printing matA elements:\n");
  for (t=0; t<dimN; t++) {
    for (t2=0; t2<dimM; t2++){matA[t][t2] = t2 + t + 5; printf("%d ", matA[t][t2]);}
    printf("\n");
  }
  
  printf("Printing vetX elements:\n");
  for (t=0; t<dimM; t++) {vetX[t] = t+1; printf("%d\n", vetX[t]);}
  
  printf("Printing vetB elements (vazio):\n");
  for (t=0; t<dimN; t++) {vetB[t] = 0; printf("%d\n", vetB[t]);}
  
  for(t=0; t<NTHREADS; t++) {
    //printf("--Aloca e preenche argumentos para thread %d\n", t);
    arg = malloc(sizeof(t_Args));
    if (arg == NULL) {printf("--ERRO: malloc()\n"); exit(-1);}
    arg->idThread = t; 
    arg->nThreads = NTHREADS;
    
    printf("--Cria a thread %d\n", t);
    int ec = pthread_create(&tid_sistema[t], NULL, PrintHello, (void*) arg);
    if (ec) {printf("--ERRO: pthread_create() - code %d\n", ec); exit(-1);}
  }

  //--espera todas as threads terminarem
  for (t=0; t<NTHREADS; t++) {if (pthread_join(tid_sistema[t], NULL)) {printf("--ERRO: pthread_join() \n"); exit(-1);}}
  
  printf("Printing vetB elements (resultados):\n");
  for (t=0; t<dimN; t++) {printf("%d\n", vetB[t]);}
  
  printf("--Thread principal terminou\n");
  pthread_exit(NULL);
}