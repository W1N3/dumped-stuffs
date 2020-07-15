package pacote;

import pacote.Mapa;
import pacote.Assento;
import java.lang.Thread;
import java.util.Random;
import java.io.IOException;

// Thread usuária variação 1
public class Usuaria extends Thread {

    private int id;
    private Mapa map;

    public Usuaria(int id, Mapa map) {
      this.id = id;
      this.map = map;
    }

    public void run() {
        int passos, desvio, qualquer=-1;
        if(this.id == 0) {
            while(this.map.estaRodando()) {
                this.map.retiraBuffer();
            }
        }
        else {
            Random gerador = new Random();
            Assento ticket = new Assento();
            passos = gerador.nextInt(6)+1;
            while(passos > 0) {
                // Define o desvio
                desvio = gerador.nextInt(4);
                qualquer = gerador.nextInt(this.map.getQuantidade());

                // Visualiza Assento    
                if(desvio == 0) {
                    this.map.visualizaAssentos(this.id);
                }

                // Aloca Assento Livre
                else if(desvio == 1) {
                    qualquer = gerador.nextInt(map.getQuantidade());
                    this.map.alocaAssentoLivre(ticket, this.id);
                }

                // Aloca Assento Dado
                else if(desvio == 2) {
                    this.map.alocaAssentoDado(map.getAssento(qualquer), this.id);
                }

                // Libera Assento
                else if(desvio == 3) {
                    qualquer = gerador.nextInt(this.map.getQuantidade());
                    this.map.liberaAssento(this.map.getAssento(qualquer), this.id);
                }
                passos--;
            }
        }
    }
}
