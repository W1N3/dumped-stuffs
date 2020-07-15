#include <sys/stat.h>
#include <stdlib.h>
#include <unistd.h>
#include <signal.h>
#include <string.h>
#include <regex.h>
#include <stdio.h>
#include <fcntl.h>
#include <errno.h>
#include <time.h>

#define _GNU_SOURCE

// função que recebe um sinal para ser ignorado continuamente
void ignore(int sig) {
        signal(sig, ignore);
}

int main(int argc, char **argv) {
    int n, it;
    char *command, *file_name, *reader, *daemon_log_file_name, *temp_file_name, *regex_pattern;
    time_t timezz;
    struct tm local_time;
    FILE *log, *temporary;
    regex_t find_ppid_number, find_pid_number, find_process_name;
    regmatch_t matches[2];

    if(argc < 2) {
        printf("\n Use: %s [seconds] \n", *(argv));
            exit(0);
    }
    else {
        n = atol(*(argv+1));
        if(n <= 0) {
            printf("\n [second]=<%s> must be a number! \n", *(argv+1));
            exit(0);
        }
    }

    // alocação de memória necessária
    reader = (char *) malloc(sizeof(char)*1025);
    command = (char *) malloc(sizeof(char)*150);
    regex_pattern = (char *) malloc(sizeof(char)*100);
    temp_file_name = (char *) malloc(sizeof(char)*50);
    daemon_log_file_name = (char *) malloc(sizeof(char)*50);

    // Observei que os SIG são valores entre 1 e 31
    
    for(it = 1; it < 32; it++)
            signal(it, ignore);
    errno = 0;

    if(fork()) {
            exit(0);
    } // Passa para background
    
    // cria a regex para achar o PID
    regex_pattern[0] = '\0';
    strcat(regex_pattern, "^[0-9]{4,6}"); 
    regcomp(&find_pid_number, regex_pattern, REG_ICASE | REG_EXTENDED);

    // cria a regex para achar o PPID
    regex_pattern[0] = '\0';
    strcat(regex_pattern, ".[0-9][0-9]{4,6}"); 
    regcomp(&find_ppid_number, regex_pattern, REG_ICASE | REG_EXTENDED);
    
    // cria a regex para achar o nome do processo
    regex_pattern[0] = '\0';
    strcat(regex_pattern, "\\[[a-z]*\\]");
    regcomp(&find_process_name, regex_pattern, REG_ICASE | REG_EXTENDED);

    // cria o arquivo de log do daemon e o inicializa
    timezz = time(NULL);
    local_time = *localtime(&timezz);
    sprintf(daemon_log_file_name, "daemon-%d-%.4d.log", local_time.tm_year+1900, ((local_time.tm_min+1)*(local_time.tm_sec+1)));
    log = fopen(daemon_log_file_name, "w");
    fprintf(log, "\t\tArquivo de log do Daemon que verifica processos zumbis \n\n");
    fclose(log);

    // começa a parte central do daemon
    while(n > 0) {
        // Abre o arquivo de log do daemon
        log = fopen(daemon_log_file_name, "a");
        timezz = time(NULL);
        local_time = *localtime(&timezz);
        fprintf(log, "\n\ttimestamp : %d:%d:%d\n", local_time.tm_hour, local_time.tm_min, local_time.tm_sec);
        fprintf(log, "=======================================\n");
        fprintf(log, "PID\tPPID\tNome do Programa\n");

        // Gera o nome do arquivo temporário
        timezz = time(NULL);
        local_time = *localtime(&timezz);
        sprintf(temp_file_name, "/tmp/%d-%d-%.4d.temp", local_time.tm_year+1900, local_time.tm_mon+1, ((local_time.tm_min+1)*(local_time.tm_sec+1)));
        // Coloca o comando somado ao nome do arquivo temporário
        sprintf(command, "ps -eo pid,ppid,args,stat | grep -E -e 'Z' > %s", temp_file_name);
        // Executa o commando que lê as informações dos processos no SO
        system(command);

        // Abre o arquivo temporário
        errno = 0;
        temporary = fopen(temp_file_name, "r");
        if(errno != 0)
            printf("error opening temp file : %s\n", strerror(errno));

        
        while(!feof(temporary)) {
            // Lê uma linha do arquivo temporário
            //fscanf(temporary, "%[^\n]s", reader);
            fgets(reader, 1024, temporary);
            // Verifica se tem a palavra 'grep' na linha lida
            if(strstr(reader, "grep") == NULL) {
                // Coloca a linha lida no arquivo de log
                //printf("%s\n", reader);
                if(regexec(&find_pid_number, reader, 2, matches, 0) == 0) {
                    it = matches[0].rm_so;
                    while(it < matches[0].rm_eo) {
                        fprintf(log, "%c", reader[it]);
                        it++;
                    }
                    fprintf(log, "\t");
                }
                if(regexec(&find_ppid_number, reader, 2, matches, 0) == 0) {
                    it = matches[0].rm_so;
                    while(it < matches[0].rm_eo) {
                        fprintf(log, "%c", reader[it]);
                        it++;
                    }
                    fprintf(log, "\t");
                }
                if(regexec(&find_process_name, reader, 2, matches, 0) == 0) {
                    it = matches[0].rm_so+1;
                    while(it < matches[0].rm_eo-1) {
                        fprintf(log, "%c", reader[it]);
                        it++;
                    }
                    fprintf(log, "\t");
                }
                fprintf(log, "\n");
                /*
                caso de merda com regex, voltar pra apenas isso aqui
                fprintf(log, "%s\n", reader);
                */
            }
        }
        

        /*
        Fecha o arquivo temporário
        Coloca o nome do arquivo temporário na string 'command',
        para ser removido
        Executa 'command'
        */
        fclose(temporary);
        sprintf(command, "rm -f %s", temp_file_name);
        system(command);
        
        // Fecha o arquivo de log do daemon
        fclose(log);

        // espera por 'n' segundos
        sleep(n);
    }

    // liberação de memória
    free(reader);
    free(command);
    free(regex_pattern);
    free(temp_file_name);
    free(daemon_log_file_name);

    return 0;
}

