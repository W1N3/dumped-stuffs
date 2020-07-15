typedef struct t_Assento {
	int estado;
	int pos;
	int tid;
} Assento;

typedef struct t_Mapa {
    int quantidade;
    Assento **poltronas;
} Mapa;

#define LIVRE 0
#define OCUPADO 1


Mapa* CriaVeiculo(int n_quantidade);

int DestroiVeiculo(Mapa *n_veiculo);

void visualizaAssentos(Mapa *v);

int alocaAssentoLivre(Mapa *v, int id);

int alocaAssentoDado(Assento a, int id);

int liberaAssento(Assento a, int id);

void* usuario (void *arg);

int main(int argc, char* argv[]);
