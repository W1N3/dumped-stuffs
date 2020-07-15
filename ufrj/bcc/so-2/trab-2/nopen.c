// foi necessário esse define para que funcione o O_TMPFILE
#define _GNU_SOURCE
#include <stdio.h>
#include <time.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>

// o objetivo dessa função é :
//      receber um "file descriptor" para então saber se ele está aberto ou não
//      retornar 0 - quando o arquivo não está aberto
//      retornar 1 - quando o arquivo está aberto
int isopen (int fd) {
    // inteiro utilizado para retornar algum valor pela função
    int ret;
    // não faz nada no ponteiro do arquivo
    if(lseek(fd, 0, SEEK_SET) == -1) {
        // caso o tipo do erro acuse que o "file descriptor" não está aberto
        if(errno == EBADF) {
            printf("\nfd[%d] !Erro! - %s;", fd, strerror(errno));
            ret = 0;
        }
        // se for outro erro(EINVAL, ENXIO, EOVERFLOW, ESPIPE) , o arquivo está aberto
        else {
            ret = 1;
        }
    }
    // caso dê tudo certo o arquivo está aberto
    else {
        ret = 1;
    }
    return ret;
}

// o objetivo dessa função é :
//      abrir arquivos temporários aleatórios entre 1 e 23
//      retornar um vetor de inteiros que correspondem aos "file descriptors" dos respectivos arquivos
int* open_random_files() {
    // quantidade de arquivos, iterador
    int n_files, n;
    // vetor de "file descriptors"
    int *fds;

    srandom(time(NULL));
    // randomiza a quantidade de arquivos de 2 a 24
    n_files = (rand() % 23) + 2;


    printf("\nAbrirá %d arquivos temporários\n", n_files-1);
    // aloca o vetor de "file descriptors" de arquivos temporários
    fds = (int *) malloc(sizeof(int) * n_files);
    if(fds == NULL) {
        printf("\n!@Malloc(fds)@! - %s;", strerror(errno));
    }
    // a iteração vai até no maximo o tamanho do vetor subtraido de 1
    for(n = 0; n < (n_files-1) ; n++) {
        // cria arquivos temporários
        fds[n] = open("/tmp/", O_TMPFILE | O_RDWR, S_IRUSR | S_IWUSR);
        if(fds[n] == -1) {
            printf("\n!@Open(fds[%d])@! - %s;", n, strerror(errno));
        }
    }
    // ultima posição do vetor reservado para o caracter "\0"
    *(fds+(n_files-1)) = 0;
    return fds;
}

// o objetivo dessa função é:
//      receber um vetor de "file descriptors"
//      fechar todos os arquivos respectivos ao vetor de "file descriptors" recebido
void close_random_files(int* fds) {
    int n = 0;
    // aqui está a razão do porque o tamanho do fds no open_random_files varia de 2 a 24
    // fica mais fácil de iterar o vetor
    while(fds[n] != 0) {
        // tentar fechar os arquivos temporários
        if(close(fds[n]) == -1) {
            printf("\n!@Close(fds[%d])@! - %s;", n, strerror(errno));
        }
        n++;
    }
    // libero o vetor de "file descriptors" de arquivos temporários
    free(fds);
}

int main () {
    
    // quantidade de descritores abertos a ser mostrado
    int nopen; 
    // "file descriptor" iterador a ser passado para isopen
    int fd;
    // semente para número aleatório
    int seed;
    // "file descriptor" do /dev/null
    int trash;
    // vetor de "file descriptors" de arquivos temporários
    int *tmp_files;
    // string lixo para ser lido do /dev/null
    char *garbage;

    tmp_files = open_random_files();
    nopen = 0;
    for (fd = 0; fd < getdtablesize(); fd++) {
        if (isopen (fd))
            nopen++;
    }
    close_random_files(tmp_files);
    printf ("\nExistem %d descritores abertos\n", nopen);
    return 0;
}
/* end main */
