#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <signal.h>
#include <errno.h>

// função chamada caso o sinal seja enviado ao processo
void ignore(int sig) {
    printf("\n OUCH! - sig is %d", sig);
}

// função para retornar a palavra correspondente ao sinal
char* return_sig_word(int sig) {
    char* ret;
    ret = (char *) malloc(sizeof(char)*20);
    ret[0] = '\0';
    switch(sig) {
        case SIGABRT:
            strcpy(ret, "SIGABRT");
            break;
        case SIGALRM:
            strcpy(ret, "SIGALRM");
            break;
         
        case SIGBUS:
            strcpy(ret, "SIGBUS");
            break;
     
        case SIGCHLD:
            strcpy(ret, "SIGCHLD");
            break;
     
        case SIGCONT:
            strcpy(ret, "SIGCONT");
            break;
     
        case SIGFPE:
            strcpy(ret, "SIGFPE");
            break;
     
        case SIGHUP:
            strcpy(ret, "SIGHUP");
            break;
     
        case SIGILL:
            strcpy(ret, "SIGILL");
            break;
     
        case SIGINT:
            strcpy(ret, "SIGINT");
            break;

        case SIGKILL:
            strcpy(ret, "SIGKILL");
            break;
     
        case SIGPIPE:
            strcpy(ret, "SIGPIPE");
            break;
     
        case SIGQUIT:
            strcpy(ret, "SIGQUIT");
            break;
     
        case SIGSEGV:
            strcpy(ret, "SIGSEGV");
            break;
     
        case SIGSTOP:
            strcpy(ret, "SIGSTOP");
            break;
     
        case SIGTERM:
            strcpy(ret, "SIGTERM");
            break;
     
        case SIGTSTP:
            strcpy(ret, "SIGTSTP");
            break;
     
        case SIGTTIN:
            strcpy(ret, "SIGTTIN");
            break;
     
        case SIGTTOU:
            strcpy(ret, "SIGTTOU");
            break;
     
        case SIGUSR1:
            strcpy(ret, "SIGUSR1");
            break;
     
        case SIGUSR2:
            strcpy(ret, "SIGUSR2");
            break;
     
        case SIGPOLL:
            strcpy(ret, "SIGPOLL");
            break;
     
        case SIGPROF:
            strcpy(ret, "SIGPROF");
            break;
     
        case SIGSYS:
            strcpy(ret, "SIGSYS");
            break;
     
        case SIGTRAP:
            strcpy(ret, "SIGTRAP");
            break;
     
        case SIGURG:
            strcpy(ret, "SIGURG");
            break;
     
        case SIGVTALRM:
            strcpy(ret, "SIGVTALRM");
            break;
     
        case SIGXCPU:
            strcpy(ret, "SIGXCPU");
            break;
     
        case SIGXFSZ:
            strcpy(ret, "SIGXFSZ");
            break;

        default:
            strcpy(ret, "What that heck?!");
            break;
    }
    return ret;
}

/*
Um programa que mostra quais sinais causam a mudança do errno
*/
int main () {
    int i;
    errno = 0;

    for(i = 1 ; i < 32 ; i++) {
        signal(i, ignore);
        if(errno != 0) {
            printf("%s! - error message : %s\n",
                return_sig_word(i), // retorno da palavra relativa ao nome do sinal
                strerror(errno)); // mensagem de erro causada pela chamada da signal
            errno = 0; // muda errno para 0, pois na próxima iteração do laço deve ser verificado se o errno foi modificado
        }
    }
}