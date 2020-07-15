/* Disciplina: Computacao Concorrente */
/* Prof.: Silvana Rossetto */
/* Laboratório: 2 */
/* Codigo: Multiplica uma matriz por um vetor */

#include <stdio.h>
#include <stdlib.h>
#include <pthread.h>
#include "timer.h"

//variaveis globais
//float *matA; //matriz de entrada
//float *vetX; //vetor de entrada
//float *vetB; //vetor de saida

typedef struct _Matrix_Args {
   int id, slice_size, thrs_qtd, linhas, colunas;
   float *matA;
   float *vetX;
   float *vetB;
} T_Args;

void* MMV_T(void* args) {
   int i, j, start, block;
   T_Args obj = *(T_Args *) args;

   start = obj.id * obj.slice_size;
   if(obj.thrs_qtd == (obj.id+1) ) {
      block = obj.linhas;
   }
   else {
      block = (start + obj.slice_size);
   }
   for (i=start; i < block ; i++) {
      obj.vetB[i] = 0;
      for (j=0; j<obj.colunas; j++) {
         obj.vetB[i] += obj.matA[i*obj.colunas+j] * obj.vetX[j];
      }
   }
   free(args);
   pthread_exit(NULL);
}

//funcao que multiplica matriz por vetor (A * X = B)
//entrada: matriz de entrada, vetor de entrada, vetor de saida, dimensoes da matriz
//requisito 1: o numero de colunas da matriz eh igual ao numero de elementos do vetor de entrada
//requisito 2: o numero de linhas da matriz eh igual ao numero de elementos do vetor de saida
void multiplicaMatrizVetor(const float *a, const float *x, float *b, int linhas, int colunas) {
   int i, j;
   for (i=0; i<linhas; i++) {
      b[i] = 0;
      for (j=0; j<colunas; j++) {
         b[i] += a[i*colunas+j] * x[j];
      }
   }
}

//funcao que aloca espaco para uma matriz e preenche seus valores
//entrada: matriz de entrada, dimensoes da matriz
//saida: retorna 1 se a matriz foi preenchida com sucesso e 0 caso contrario
int preencheMatriz(float **mat, int linhas, int colunas, FILE *arq) {
   int i, j;
   //aloca espaco de memoria para a matriz
   *mat = (float*) malloc(sizeof(float) * linhas * colunas);
   if (mat == NULL) return 0;
   //preenche o vetor
   for (i=0; i<linhas; i++) {
      for (j=0; j<colunas; j++) {
         //fscanf(arq, "%f", *( (*mat) + (i*colunas+j) ) );
         fscanf(arq, "%f", (*mat) + (i*colunas+j));
      }
   }
   return 1;
}

//funcao que imprime uma matriz
//entrada: matriz de entrada, dimensoes da matriz
//saida: matriz impressa na tela
void imprimeMatriz(float *mat, int linhas, int colunas, FILE *arq) {
   int i, j;
   for (i=0; i<linhas; i++) {
      for (j=0; j<colunas; j++) {
         fprintf(arq, "%.1f ", mat[i*colunas+j]);
      }
      fprintf(arq, "\n");
   }
}

//funcao que aloca espaco para um vetor e preenche seus valores
//entrada: vetor de entrada, dimensao do vetor
//saida: retorna 1 se o vetor foi preenchido com sucesso e 0 caso contrario
int preencheVetor(float **vet, int dim, FILE *arq) {
   int i;
   //aloca espaco de memoria para o vetor
   *vet = (float*) malloc(sizeof(float) * dim);
   if (vet == NULL) return 0;
   //preenche o vetor
   for (i=0; i<dim; i++) {
       //*( (*vet)+i ) = 1.0;
       fscanf(arq, "%f", (*vet) + i);
   }
   return 1;
}

//funcao que imprime um vetor
//entrada: vetor de entrada, dimensao do vetor
//saida: vetor impresso na tela
void imprimeVetor(float *vet, int dim, FILE *arq) {
   int i;
   for (i=0; i<dim; i++) {
      fprintf(arq, "%.1f ", vet[i]);
   }
   fprintf(arq, "\n");
}

//funcao principal
int main(int argc, char *argv[]) {
   float *matA; //matriz de entrada
   float *vetX; //vetor de entrada
   float *vetB; //vetor de saida
   FILE *arqA, *arqX, *arqB; //arquivos dos dados de entrada e saida
   int linhas, colunas; //dimensoes da matriz de entrada
   int dim; //dimensao do vetor de entrada
   int t; // contador
   T_Args* arg; // ponteiro suporte para passar parametro para thread

   double i, f; //variáveis para medir tempo
   pthread_t* thrs; //vetor de identificadores das threads no sistema
   int thrs_qtd; //quantidade de threads a serem disparadas
   int slice_size; // tamanho do pedaço a ser destinado a thread

   GET_TIME(i);
   //le e valida os parametros de entrada
   //o arquivo da matriz de entrada deve conter na primeira linha as dimensoes da matriz (linha coluna) seguido dos elementos da matriz separados por espaco
   //o arquivo do vetor de entrada deve conter na primeira linha a dimensao do vetor seguido dos elementos separados por espaco
   if(argc < 5) {
      fprintf(stderr, "Digite: %s <arquivo matriz A> <arquivo vetor X> <arquivo vetor B> <numero de threads>.\n", argv[0]);
      exit(EXIT_FAILURE);
   }

   //checa o numero de threads
   if(atoi(argv[4]) <= 0 || atoi(argv[4]) > 8) {
      fprintf(stderr, "Quantidade inválida de threads.\n");
      exit(EXIT_FAILURE);
   }
   thrs_qtd = atoi(argv[4]);

   //aloca espaco para o vetor de identificadores das threads no sistema
   thrs = (pthread_t *) malloc(sizeof(pthread_t) * thrs_qtd);
   if(thrs==NULL) {
      fprintf(stderr, "--ERRO: malloc()\n");
      exit(EXIT_FAILURE);
   }

   //abre o arquivo da matriz de entrada
   arqA = fopen(argv[1], "r");
   if(arqA == NULL) {
      fprintf(stderr, "Erro ao abrir o arquivo da matriz de entrada.\n");
      exit(EXIT_FAILURE);
   }
   //le as dimensoes da matriz de entrada
   fscanf(arqA, "%d", &linhas);
   fscanf(arqA, "%d", &colunas);

   //abre o arquivo do vetor de entrada
   arqX = fopen(argv[2], "r");
   if(arqX == NULL) {
      fprintf(stderr, "Erro ao abrir o arquivo do vetor de entrada.\n");
      exit(EXIT_FAILURE);
   }
   //le a dimensao do vetor de entrada
   fscanf(arqX, "%d", &dim);

   //valida as dimensoes da matriz e vetor de entrada
   if(colunas != dim) {
      fprintf(stderr, "Erro: as dimensoes da matriz e do vetor de entrada nao sao compativeis.\n");
      exit(EXIT_FAILURE);
   }

   //abre o arquivo do vetor de saida
   arqB = fopen(argv[3], "w");
   if(arqB == NULL) {
      fprintf(stderr, "Erro ao abrir o arquivo do vetor de saida.\n");
      exit(EXIT_FAILURE);
   }

   //aloca e preenche a matriz de entrada
   if(preencheMatriz(&matA, linhas, colunas, arqA) == 0) {
      fprintf(stderr, "Erro de preenchimento da matriz de entrada\n");
      exit(EXIT_FAILURE);
   }
   //aloca e preenche o vetor de entrada
   if(preencheVetor(&vetX, dim, arqX) == 0) {
      fprintf(stderr, "Erro de preenchimento do vetor de entrada\n");
      exit(EXIT_FAILURE);
   }
   //aloca o vetor de saida
   vetB = (float*) malloc(sizeof(float) * linhas);
   if(vetB==NULL) {
      fprintf(stderr, "Erro de alocacao do vetor de saida\n");
      exit(EXIT_FAILURE);
   }

   //dispara as threads
   slice_size = linhas / thrs_qtd;

   GET_TIME(f);
   printf("#1. Inicialização das estruturas de dados : %lfs \n", (f-i));



   GET_TIME(i);
   //multiplica a matriz de entrada pelo vetor de entrada

   for(t=0; t<thrs_qtd; t++) {
      arg = malloc(sizeof(T_Args));
      if(arg==NULL) {
         fprintf(stderr, "--ERRO: malloc()\n");
         exit(EXIT_FAILURE);
      }
      (*arg).id = t;
      (*arg).slice_size = slice_size;
      (*arg).thrs_qtd = thrs_qtd;
      (*arg).linhas = linhas;
      (*arg).colunas = colunas;
      (*arg).matA = matA;
      (*arg).vetX = vetX;
      (*arg).vetB = vetB;

      if(pthread_create(&thrs[t], NULL, MMV_T, (void*) arg)) {
         printf("--ERRO: pthread_create()\n"); exit(-1);
      }
   }


   //espera todas as threads terminarem
   for(t=0; t<thrs_qtd; t++) {
      if (pthread_join(thrs[t], NULL)) {
         fprintf(stderr, "--ERRO: pthread_join()\n");
         exit(EXIT_FAILURE);
      }
   }

   GET_TIME(f);
   printf("#2. Multiplicação das Matrizes : %lfs \n", (f-i));

   GET_TIME(i);
   //imprime o vetor de saida no arquivo de saida
   imprimeVetor(vetB, linhas, arqB);

   //libera os espacos de memoria alocados
   free(matA);  
   free(vetX);  
   free(vetB);
   GET_TIME(f);
   printf("#3. Finalizar o Programa : %lfs \n", (f-i));  
   
   return 0;
}

