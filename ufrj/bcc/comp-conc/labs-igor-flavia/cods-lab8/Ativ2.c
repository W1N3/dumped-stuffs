/* Disciplina: Computacao Concorrente */
/* Prof.: Silvana Rossetto */
/* Laboratório: 1 */
/* Codigo: "Hello World" usando threads em C e a funcao join*/

#include <stdio.h>
#include <stdlib.h> 
#include <pthread.h>

#define N_LEIT 2
#define N_ESCR 2
#define NTHREADS N_LEIT+N_ESCR
#define PASSOS 5

//cria a estrutura de dados para armazenar os argumentos da thread
typedef struct {
   int idThread, nThreads;
} t_Args;

typedef struct {
   int cont, id_thread;
} t_Compartilhada;

t_Compartilhada* compartilhada;

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

pthread_cond_t cond_leit;
pthread_cond_t cond_escr;
int leit=0, escr=0; //globais
void EntraLeitura() {
    pthread_mutex_lock(&mutex);
    while(escr > 0) {
        pthread_cond_wait(&cond_leit, &mutex);
    }
    leit++;
    pthread_mutex_unlock(&mutex);
}
void SaiLeitura() {
    pthread_mutex_lock(&mutex);
    leit--;
    if(leit==0) pthread_cond_signal(&cond_escr);
    pthread_mutex_unlock(&mutex);
}
void EntraEscrita () {
    pthread_mutex_lock(&mutex);
    while((leit>0) || (escr>0)) {
        pthread_cond_wait(&cond_escr, &mutex);
    }
    escr++;
    pthread_mutex_unlock(&mutex);
}
void SaiEscrita () {
    pthread_mutex_lock(&mutex);
    escr--;
    pthread_cond_signal(&cond_escr);
    pthread_cond_broadcast(&cond_leit);
    pthread_mutex_unlock(&mutex);
}


//funcao executada pelas threads
void *leitor (void *arg) {
    int idThread = *(int *) arg;
    int cont;
    int idescr;
    while(cont<10) {
        EntraLeitura();
        //le algo...
        cont = compartilhada->cont;
        idescr = compartilhada->id_thread;
        SaiLeitura();
        //faz outra coisa...
        
        printf("--Thread leitora=%d leu cont=%d preenchido por escritor=%d\n", idThread, cont,idescr);
    }
}
void *escritor (void *arg) {
    int idThread = *(int *) arg;
    int cont;
    while(cont<10) {
        EntraEscrita();
        //escreve algo...
        cont = compartilhada->cont;
        compartilhada->cont++;
        compartilhada->id_thread = idThread;
        SaiEscrita();
        //faz outra coisa...
        printf("--Thread escritora=%d incrementou\n", idThread);
    }
}


//funcao principal do programa
int main() {
  pthread_t tid_sistema[NTHREADS];
  int t;
  t_Args *arg; //receberá os argumentos para a thread
  
  compartilhada = malloc(sizeof(t_Compartilhada));
  if (compartilhada == NULL) {
    printf("--ERRO: malloc()\n"); exit(-1);
  }
  compartilhada->cont = 0; 
  compartilhada->id_thread = 0; 
    
  for(t=0; t<N_LEIT; t++) {
    //printf("--Aloca e preenche argumentos para thread %d\n", t);
    arg = malloc(sizeof(t_Args));
    if (arg == NULL) {
      printf("--ERRO: malloc()\n"); exit(-1);
    }
    arg->idThread = t; 
    arg->nThreads = NTHREADS; 
    
    //printf("--Cria a thread %d\n", t+1);
    if (pthread_create(&tid_sistema[t], NULL, leitor, (void*) arg)) {
      printf("--ERRO: pthread_create()\n"); exit(-1);
    }
  }
  
  for(t=0; t<N_ESCR; t++) {
    //printf("--Aloca e preenche argumentos para thread %d\n", t);
    arg = malloc(sizeof(t_Args));
    if (arg == NULL) {
      printf("--ERRO: malloc()\n"); exit(-1);
    }
    arg->idThread = t; 
    arg->nThreads = NTHREADS; 
    
    //printf("--Cria a thread %d\n", t+1);
    if (pthread_create(&tid_sistema[t], NULL, escritor, (void*) arg)) {
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
