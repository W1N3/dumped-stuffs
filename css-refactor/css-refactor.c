#define _GNU_SOURCE
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <sys/stat.h>
#include <sys/types.h>
#include <dirent.h>
#include <fcntl.h>
#include <regex.h>

#include <unistd.h>
#include <errno.h>

#define PATH_MAX        4096    /* # chars in a path name including nul */
/*

Primeira Parte da refatoração do CSS - Retirar os 'inline CSS':
	1. Varrer todos os arquivos *.html *.js existentes procurando 'inline CSS' (style="*");
	2. Identificar e tabelar com uma hash única, possibilitando que repetidas estilos dados sejam associados a uma mesma classe;
	3. Para cada hash identificar e tabelar onde estão localizados esses estilos;
	4. Criar uma classe associada a cada hash, com seu respectivo estilo formatado;
	5. Salvar essas classes em algum arquivo *.css;
Segunda parte da refatoração do CSS - Criar um arquivo *.css para cada arquivo *.html:
	1. Varrer todos os arquivos *.html existentes e identificar as respectivas classes CSS utilizadas;
	2. Verificar em quais arquivos *.css essas classes estão localizadas;
	3. Criar os arquivos *.css respectivos a cada *.html, populando com as classes referenciadas e identificadas na varredura;
	4. Adicionar na referência do <head> do *.html o novo arquivo *.css associado;


*** man page ***
	
	css-refactor [ação] [caminho(opcional)] - Um utilitário linux para refatorar o CSS do seu projeto WEB.

	Usage :

		ação -  
			inlines

			pack-up

		caminho - diretório que será passado para o utilitário ler todos os possíveis arquivos css/js/html a serem refatorados.
			Se não for passado como argumento, o diretório atual será usado.


*/

// lista simplesmente encadeada de Inline CSS 
struct _InlineCss {
	void *hash; // tipo a ser definido
	char *css; // conteúdo do css inline
	int where_start; // onde no arquivo, de forma sequencial, começa o style="*"
	int where_end; // onde no arquivo, de forma sequencial, termina o style="*"
	struct _InlineCss *next;
};

typedef struct _InlineCss InlineCss;

// struct that define the stack of paths, implemented as a pile/stack of plates.
struct _Plate {
	char *path;
	struct _Plate *down;
};
typedef struct _Plate Plate;

// just create a new Plate structure with the pathname inside
Plate* Plate_new(char *name) {
	Plate *new_plate;
	new_plate = (Plate *) malloc(sizeof(Plate));
	new_plate->path = (char *) malloc(sizeof(char) * strlen(name)+1);
	strcpy(new_plate->path, name);
	new_plate->down = NULL;
	return new_plate;
}

// function to free the Plate structure and the path array allocated in Plate_new
void Plate_destroy(Plate *plate) {
	if(plate->path != NULL)
		free(plate->path);
	if(plate != NULL)
		free(plate);
}

/*
stack - Is the pointer to the current top of the used structure, points to the first 'plate' of the structure
	If we want to pass a bunch of 'plates', an other structure with a lot of Plate, and append then in this new structure, like a 'lot of physical plates', we need the last 'plate' and the first 'plate', then: 
		new bottom - Is the last 'plate' in the structure to be appended
		new top - Is the first 'plate' in the structure to be appended
*/
void Plate_append(Plate **stack, Plate *new_bottom, Plate *new_top) {
	
	//-> a pilha não tem nenhum prato
	if(stack[1] == NULL && stack[0] == NULL) {
		//printf("a pilha não tem prato!\n");
		stack[0] = new_top;
		stack[1] = new_bottom;
	}
	else {
		if(new_bottom != NULL)
			new_bottom->down = stack[0];
		if(new_top != NULL)
			stack[0] = new_top;
	}
}

// remove the first 'plate' in the top of the structure
Plate* Plate_remove(Plate **stack) {
	Plate *current;
	current = *stack;
	*stack = (*stack)->down;
	return current;
	//Plate_destroy(current);
}

// funções relativas a estrutura InlineCss que fazem funcionar como uma lista encadeada
void InlineCss_new() {}
void InlineCss_destroy() {}
void InlineCss_add() {}
void InlineCss_remove() {}


Plate** get_files_recursively_by_given_path(char *path, char **filters) {
	DIR *current_dir;
	struct dirent *entries;
	Plate **top_bottom;
	Plate **temp_tb;
	Plate *new_plate;
	char *full_path;
	char *path_pointer;
	char *regex_pattern;
	int path_len;
	int full_path_len;
	int number_of_filters;
	int size_of_filters;
	int it;
	regex_t regex_structure;

	top_bottom = NULL;
	current_dir = opendir(path);
	path_len = strlen(path);

	if(current_dir == NULL) {
		printf("current_dir == NULL - error: %s, %s", strerror(errno), path);
		exit(EXIT_FAILURE);
	}
	else {
		errno = 0;	
		entries = readdir(current_dir);

		/* 
		Construir a regex a partir de filters 
			filters -> regex_pattern
		*/
		number_of_filters = 0;

		if(filters != NULL) {
			size_of_filters = 0;
			it = 0;
			while(*(filters+it) != NULL) {
				size_of_filters += strlen(*(filters+it));
				it++;
			}
			// (size_of_filters+(2*it)+(it-1)+1)
			regex_pattern = (char*) malloc(sizeof(char)*(size_of_filters+(3*it)));
			*(regex_pattern) = 0;
			it = 0;
			while(*(filters+it) != NULL) {
				strcat(regex_pattern, "\\.");
				strcat(regex_pattern, *(filters+it));
				strcat(regex_pattern, "|");
				it++;
			}
			
			*(regex_pattern+strlen(regex_pattern)-1) = 0;
		}
		regcomp(&regex_structure, regex_pattern, REG_NOSUB | REG_ICASE | REG_EXTENDED);


		top_bottom = (Plate **) malloc(2*sizeof(Plate *));
		top_bottom[0] = NULL;
		top_bottom[1] = NULL;

		while(entries != NULL && errno == 0) {
			if((strcmp(entries->d_name, ".") != 0) && (strcmp(entries->d_name, "..") != 0)) {
				/* constroi o caminho+nome a ser passado ou como diretório ou como arquivo pra pilha */
				full_path_len = 0;
				full_path_len += path_len + strlen(entries->d_name) + 2; /* por conta do '\0' e do '/' */
				if((full_path = (char *) malloc(full_path_len * sizeof(char))) == NULL) {
					closedir(current_dir);
					printf("full_path = malloc() - error: %s", strerror(errno));
					exit(EXIT_FAILURE);
				}
				strcpy(full_path, path);
				path_pointer = full_path + path_len;
				*(path_pointer) = '/';
				path_pointer++;
				strcpy(path_pointer, entries->d_name);
				/* case regular files - concatenate in the list of paths */
				/* caso seja um arquivo comum - concatena na lista de caminhos o caminho+nome */
				if(entries->d_type == DT_REG) {
					/* coloca o caminho+nome na pilha usando regex com os filtros dados em filters */
					/* filtrar d_name com regex */
					if(regexec(&regex_structure, entries->d_name, 0, NULL, 0) == 0) {
						new_plate = Plate_new(full_path);
						Plate_append(top_bottom, new_plate, new_plate);
					}
				}
				/* caso seja um diretório - chama essa função novamente, sendo assim uma função recursiva */
				if(entries->d_type == DT_DIR) {
					// falta passar o caminho completo
					temp_tb = get_files_recursively_by_given_path(full_path, filters);
					if(temp_tb != NULL) {
						Plate_append(top_bottom, temp_tb[1], temp_tb[0]);
						free(temp_tb);
					}
				}
				free(full_path);
				/* another case - ignore */
			}
			entries = readdir(current_dir);
			if(errno != 0) {
				printf("readdir - error: %s", strerror(errno));
				exit(EXIT_FAILURE);
			}
		}

		regfree(&regex_structure);
		free(regex_pattern);
		closedir(current_dir);
	}

	return top_bottom;
}

void find_inlines_in_file(Plate **file, InlineCss **list_of_ic) {
	int file_descriptor;
	long int file_size;
	char *file_content;
	char *regex_pattern;
	regex_t regex_structure;
	regmatch_t *matches;
	int matches_amount, it;
	if(file != NULL) {
		if((*file) != NULL) {
			
			// abre o arquivo via syscall apenas para leitura
			file_descriptor = open((*file)->path, O_RDONLY, S_IRUSR | S_IRGRP | S_IROTH );
			if(file_descriptor == -1) {
				printf("file_descriptor in path (%s) - error: %s", (*file)->path, strerror(errno));
				exit(EXIT_FAILURE);
			}

			// vê qual é o fim do arquivo e guarda em file_size
			file_size = lseek(file_descriptor, 0, SEEK_END);
			// aloca quantidade necessária de memória para receber o texto do arquivo
			file_content = (char*) malloc(sizeof(char)*(file_size+1));
			// volta para ler no começo do arquivo
			lseek(file_descriptor, 0, SEEK_START);

			// efetivamente lê o arquivo todo e guarda em file_content
			if(read(file_descriptor, file_content, file_size) == -1) {
				printf("read() in path (%s) - error: %s", (*file)->path, strerror(errno));
				exit(EXIT_FAILURE);
			}

			file_content[file_size] = '\0';
			regex_pattern = (char*) malloc(sizeof(char)*14);
			regex_pattern[0] = 0;
			
			// padrão regex para achar os css-inlines
			strcpy(regex_pattern, "style=\"[^\"]*\"");
			regcomp(&regex_structure, regex_pattern, REG_ICASE | REG_EXTENDED);
			
			// dado que a proporção de bytes entre o css-inline e o arquivo todo é de 20% (1/5)
			// e que a média que um css-inline possui 40 bytes (1/40)
			// pego o tamanho total do arquivo e divido por (1/200 = 1/40 * 1/5)
			matches_amount = file_size / 200;
			matches = (regmatch_t *) malloc(sizeof(regmatch_t)*matches_amount);
			
			// executa a regex
			regexec(&regex_structure, file_content, matches_amount, matches, 0);

			// itera o resultado da regex procurando os resultados e os guarda na estrutura 	
			for(it = 0 ; it < matches_amount ; it++) {
				// matches[it].rm_so é o começo da expressão encontrada
				// matches[it].rm_eo é o término da expressão encontrada
				if((matches[it].rm_eo != -1) && (matches[it].rm_so != -1)) {
					// Avaliar o impacto de utilizar uma hash global para dectectar colisão e evitar repetição em arquivos e 
					// e css diferentes
					// coloca em list_of_ic
				}
			}

			close(file_descriptor);
			regfree(&regex_structure);
			free(file_content);
			free(regex_pattern);
			free(matches);
		}
	}
}

void do_inlines(char *path) {
	Plate **html_js_in_stack;
	Plate *stack_pointer;
	InlineCss *chain_of_css;
	int counter;
	char **html_js_paths;
	char **filters;
	filters = (char**) malloc(sizeof(char*)*2);
	*(filters) = (char*) malloc(sizeof(char)*5);
	*(filters+1) = NULL;
	//*(filters+1) = (char*) malloc(sizeof(char)*10);
	//*(filters+2) = NULL;

	strcpy(*(filters), "html");
	//strcpy(*(filters+1), "js");
	html_js_in_stack = get_files_recursively_by_given_path(path, filters);

	free(*(filters));
	free(filters);

	if(html_js_in_stack != NULL) {
		if(html_js_in_stack[0] != NULL && html_js_in_stack[1] != NULL) {
			chain_of_css = InlineCss_new();
			counter = 1;
			stack_pointer = html_js_in_stack[0];
			/* Conta a quantidade de arquivos enquanto o apontador não chega na base da pilha de pratos */
			while(stack_pointer != html_js_in_stack[1]) {
				find_inlines_in_file(&stack_pointer, &chain_of_css);
				stack_pointer = stack_pointer->down;
				counter++;
			}
			printf("\n\tcounter = %d\n\n", counter);

			stack_pointer = html_js_in_stack[0];
			/* Destroi os pratos */
			while(stack_pointer != html_js_in_stack[1]) {
				Plate_destroy(Plate_remove(&stack_pointer));

			}
		}
		free(html_js_in_stack);
	}
	/*
	falta fazer :
		estar bem arrumado em um vetor de strings a caminho completo dos arquivos que serão trabalhados

		realizar uma leitura sobre o conteúdo de cada arquivo

		dado o que foi lido, realizar uma 'regex' procurando "style='*'"

		!!![Futura funcionalidade, não necessária para o refactoring a ser aplicado]!!! : tratar, de alguma forma, a diferença do código dentro do "style='*'", não apenas como uma hash do texto existente, mas também verificando diferenças semânticas

		arrumar uma estrutura para guardar :
			 o caminho completo com o nome do arquivo;
			 !!!	Avaliar se :
			 		para cada nome de arquivo se tem uma lista de classes associadas (provavel implementação interna de um banco de dados);
			 		ou
			 		para cada classe repito o nome do arquivo na estrutura (saída literal de uma tabela de um banco de dados);
			 posição dentro do arquivo que foi achado tal "style='*'";
			 nome da classe gerada a partir do arquivo (implicando que terá uma associação de hash, logo a estrutura será uma 'hash table');

		usar a estrutura para criar de forma formatada as classes em um arquivo 'refactored.css'

		também usar a estrutura para modificar os arquivos *.html associado a cada trocando o "style='*'" pela class="refactored-[#hash#]"



	! avaliar o tamanho dessa função, visto que crescerá dado a quantidade de coisas que ela faz
	*/
}

void do_pack_up(char* path) {
	printf("\nOption still not implemented yet!\n\n");
}


void print_how_to_use() {
	printf("\t! man page !\n");
}

int main (int argc, char** argv) {
	// 
	char *path_to_crawl;
	int path_len;
	//
	DIR *try_open_path;


	if (argc == 2 || argc == 3) {
		// quantidade de argumentos == 1
		if(argc == 2) {
			/* pega o diretório corrente como o caminho */
			path_to_crawl = (char *) malloc(sizeof(char) * PATH_MAX);
			if( getcwd(path_to_crawl, PATH_MAX) == NULL) {
				printf("getcwd - error: %s\n", strerror(errno));
				free(path_to_crawl);
				exit(EXIT_FAILURE);
			}
		}
		// quantidade de argumentos == 2
		else {
			/* testa se o caminho dado é valido */
			try_open_path = opendir(argv[2]);
			if(try_open_path == NULL) {
				printf("try_open_path - error: %s\n", strerror(errno));
				exit(EXIT_FAILURE);
			}
			else {
				if(closedir(try_open_path) == -1) {
					printf("closedir - error(%d): %s\n", errno, strerror(errno));
					exit(EXIT_FAILURE);
				}
				else {
					path_to_crawl = argv[2];
					// corrige o path, caso tenha um '/' no final do argumento, para uniformizá-lo
					path_len = strlen(path_to_crawl);
					if(path_to_crawl[path_len-1] == '/') {
						path_to_crawl[path_len-1] = 0;
					}
				}
			}
		}

		/* teste para sabe se a ação escolhida é valida */
		if(strcmp(argv[1], "inlines") == 0) {
			do_inlines(path_to_crawl);
		}
		else if(strcmp(argv[1], "pack-up") == 0) {
			do_pack_up(path_to_crawl);
		}
		else {
			print_how_to_use();
		}
	}
	else {
		print_how_to_use();
	}
	if(argc == 2)
		free(path_to_crawl);
	return 0;
}