#define _GNU_SOURCE
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <dirent.h>


// variaveis globais.... porque!
int globalz_count;

int walk_dir (const char *path, void (*func) (const char *)) {
    DIR *dirp;
    struct dirent *dp;
    char *p, *full_path;
    int len;
    
    /* abre o diretório */
    if ((dirp = opendir (path)) == NULL)
        return (-1);
    len = strlen (path);
    
    /* aloca uma área na qual, garantidamente, o caminho caberá */
    if ((full_path = malloc (len + NAME_MAX + 2)) == NULL) {
        closedir (dirp);
        return (-1);
    }
    
    /* copia o prefixo e acrescenta a ‘/’ ao final */
    
    memcpy (full_path, path, len);
    p = full_path + len; *p++ = '/';

    /* deixa "p" no lugar certo! */
    while ((dp = readdir (dirp)) != NULL) {
        /* ignora as entradas "." e ".." */
        if (strcmp (dp->d_name, ".") == 0 || strcmp (dp->d_name, "..") == 0)
            continue;
        strcpy (p, dp->d_name);
        /* “full_path” armazena o caminho */
        (*func) (full_path);
    }
    free (full_path);
    closedir (dirp);
    return (0);
}
/* end walk_dir */

void catch_file_path_generic(const char * path, char opt) {
    struct stat file_infos;
    stat(path, &file_infos);
    switch(opt) {
        case 'r':
            if((file_infos.st_mode & S_IFMT) == S_IFREG) {
                globalz_count++;
            }
            break;
        case 'd':
            if((file_infos.st_mode & S_IFMT) == S_IFDIR) {
                globalz_count++;
            }
            break;
        case 'l':
            if((file_infos.st_mode & S_IFMT) == S_IFLNK) {
                globalz_count++;
            }
            break;
        case 'b':
            if((file_infos.st_mode & S_IFMT) == S_IFBLK) {
                globalz_count++;
            }
            break;
        case 'c':
            if((file_infos.st_mode & S_IFMT) == S_IFCHR) {
                globalz_count++;
            }
            break;
        default:
            printf("\n Deu uma merda federal");
            break;
    }
}

void catch_file_path_r(const char * path) {
    catch_file_path_generic(path, 'r');
}

void catch_file_path_d(const char * path) {
    catch_file_path_generic(path, 'd');
}

void catch_file_path_l(const char * path) {
    catch_file_path_generic(path, 'l');
}

void catch_file_path_b(const char * path) {
    catch_file_path_generic(path, 'b');
}

void catch_file_path_c(const char * path) {
    catch_file_path_generic(path, 'c');
}

void print_man_user() {
    printf("\n\t!!!ICOUNT!!!\n\n\tInforma a quantidade de INODEs de um determinado tipo em cada um dos diretórios cujos caminhos são dados como argumentos:\n\n\ticount [-rdlbc] [<dir> ...]\n\n\tonde os modificadores rdlbc têm os seguintes significados [Atenção - os modificadores são mutuamente exclusivos. E o programa foi implementado de tal forma que a primeira opção é considerada]:\n\n\t\t-r:arquivo regular (S_IFREG)\n\t\t-d:diretório (S_IFDIR)\n\t\t-l:elo simbólico (S_IFLNK)\n\t\t-b:dispositivo estruturado (S_IFBLK)\n\t\t-c:dispositivo não-estruturado (S_IFCHR)\n\t\t<dir>:caminho para o diretório\n\n");
}

int main(int argc, char** argv) {
    char option;
    char *path;
    globalz_count = 0;
    option = getopt(argc, argv, "+r:d:l:b:c:");
    switch(option) {
        case 'r':
        case 'd':
        case 'l':
        case 'b':
        case 'c':
            if(optarg != NULL) {
                path = optarg;
            }
            else {
                path = argv[optind];
            }
            break;
        default:
            option = 'r';
            if(optarg != NULL) {
                path = optarg;
            }
            else {
                path = argv[optind];
            }
            break;
    }

    if(path != NULL) {
        switch(option) {
            case 'r':
                walk_dir(path, &catch_file_path_r);
                printf(" Esse diretório possui %d arquivo(s) regulare(s)!\n\n", globalz_count);
                break;
            case 'd':
                walk_dir(path, &catch_file_path_d);
                printf(" Esse diretório possui %d diretório(s)!\n\n", globalz_count);
                break;
            case 'l':
                walk_dir(path, &catch_file_path_l);
                printf(" Esse diretório possui %d elo(s) simbólico(s)!\n\n", globalz_count);
                break;
            case 'b':
                walk_dir(path, &catch_file_path_b);
                printf(" Esse diretório possui %d dispositivo(s) estruturado(s)!\n\n", globalz_count);
                break;
            case 'c':
                walk_dir(path, &catch_file_path_c);
                printf(" Esse diretório possui %d dispositivo(s) não-estruturado(s)!\n\n", globalz_count);
                break;
        }
    }
    else {
        print_man_user();
    }
    return 0;
}