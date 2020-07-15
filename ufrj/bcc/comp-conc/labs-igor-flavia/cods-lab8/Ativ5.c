/* Disciplina: Computacao Concorrente */
/* Prof.: Silvana Rossetto */
/* Laboratório: 1 */
/* Codigo: "Hello World" usando threads em C e a funcao join*/

#include <stdio.h>
#include <stdlib.h> 
#include <pthread.h>

#define NTHREADS  5
#define PASSOS 5

//cria a estrutura de dados para armazenar os argumentos da thread
typedef struct {
   int idThread, nThreads;
} t_Args;

int threads=0;
pthread_mutex_t mutex;
pthread_cond_t cond_bar;
void barreira(int nthreads) {
    pthread_mutex_lock(&mutex);
    threads++;
    if (threads < nthreads) {
        pthread_cond_wait(&cond_bar, &mutex);
    } else {
        threads=0;
        pthread_cond_broadcast(&cond_bar);
    }
    pthread_mutex_unlock(&mutex);
}

//funcao executada pelas threads
void *A (void *arg) {
    int tid = *(int*)arg, i;
    int cont = 0, boba1, boba2;
    for (i=0; i < PASSOS; i++) {
        cont++;
        printf("Thread %d: cont=%d, passo=%d\n", tid, cont, i); //sincronizacao condicional
        barreira(NTHREADS);
        /* faz alguma coisa inutil pra gastar tempo... */
        boba1=100; boba2=-100; while (boba2 < boba1) boba2++;
    }
    pthread_exit(NULL);
}

//funcao principal do programa
int main() {
  pthread_t tid_sistema[NTHREADS];
  int t;
  t_Args *arg; //receberá os argumentos para a thread

  for(t=0; t<NTHREADS; t++) {
    //printf("--Aloca e preenche argumentos para thread %d\n", t);
    arg = malloc(sizeof(t_Args));
    if (arg == NULL) {
      printf("--ERRO: malloc()\n"); exit(-1);
    }
    arg->idThread = t; 
    arg->nThreads = NTHREADS; 
    
    //printf("--Cria a thread %d\n", t+1);
    if (pthread_create(&tid_sistema[t], NULL, A, (void*) arg)) {
      printf("--ERRO: pthread_create()\n"); exit(-1);
    }
  }

  //--espera todas as threads terminarem
  for (t=0; t<NTHREADS; t++) {
    if (pthread_join(tid_sistema[t], NULL)) {
         printf("--ERRO: pthread_join() \n"); exit(-1); 
    } 
  }

  //printf("--Thread principal terminou\n");
  pthread_exit(NULL);
}
