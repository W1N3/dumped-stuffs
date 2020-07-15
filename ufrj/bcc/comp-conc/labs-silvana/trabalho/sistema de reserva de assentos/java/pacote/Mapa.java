package pacote;

import pacote.Log;
import pacote.Buffer;
import pacote.Assento;
import java.util.Random;
import java.util.concurrent.locks.*;

// Leitor/Escritor
// classe que controla o conjunto de assentos
public class Mapa {

    private Buffer log;
    private int quantidade;
    private int alocados;
    private Assento[] poltronas;
    private ReentrantLock leitura;
    private ReentrantLock[] escrita;
    private boolean rodando;


    public Mapa(int quantidade, Buffer log) {
        this.log = log;
        this.quantidade = quantidade;
        this.alocados = 0;
        this.poltronas = new Assento[quantidade];
        this.leitura = new ReentrantLock();
        this.escrita = new ReentrantLock[quantidade];
        for(int i = 0 ; i < quantidade ; i++) {
            this.poltronas[i] = new Assento(i, 0);
            this.escrita[i] = new ReentrantLock();
        }
        this.rodando = true;
    }

    // função que imprime o mapa de assentos
    public void printMapa() {
        System.out.println(" # Mapa de assentos");
        this.leitura.lock();
        try {
            for (int i = 0 ; i < this.quantidade ; i++) {
                System.out.println("\t-> Assento: "+(i+1)+"\tEstado: "+( (this.poltronas[i].getTid() == 0) ? ("Liberado") : ("Reservado\tDona: "+this.poltronas[i].getTid()+"") ) );
            }
        }
        finally {
            this.leitura.unlock();
        }
    }
    // função que gera a string que representa o mapa
    public String toString() {
        String mapa = "[";
        for (int i = 0 ; i < this.quantidade ; i++){
            if (i == (this.quantidade-1)) {
                mapa += poltronas[i].getTid();
            }
            else {
                mapa += poltronas[i].getTid()+",";
            }
        }
        mapa += "]";
        return mapa;
    }

    public boolean checaReserva(int tid) {
        boolean reservou = false;
        for (int i = 0 ; i < this.quantidade ; i++){
            if (poltronas[i].getTid() == tid) {
                reservou = true;
            }
        }
        return reservou;
    }

    public Assento getAssento(int i) throws ArrayIndexOutOfBoundsException {
        try {
            return this.poltronas[i];
        } catch (ArrayIndexOutOfBoundsException e) {
            System.out.println("\t* Assento não existente!");
        }
        return null;
    }

    public int getQuantidade() {
        return this.quantidade;
    }

    public boolean estaRodando() {
        return this.rodando;
    }
    public void Parar() {
        if(this.log.getAcumulo() == 0) {
            this.rodando = false;
        }
    }

    public int retiraBuffer() {
        try {
            return this.log.retiraBuffer();
        } catch (Exception e) {
            e.printStackTrace();
        }
        return 0;
    }


    // função que representa a operação de código 1
    public void visualizaAssentos(int tid) {
        this.leitura.lock();
        try {
            for(int i = 0 ; i < this.quantidade ; i++) {
                this.poltronas[i].getTid();
            }
            try {
                this.log.insereBuffer(new Log(1, tid, 0, this.toString()));

            } catch(InterruptedException e) {
                e.printStackTrace();
            }
        }
        finally {
            this.leitura.unlock();
        }
        System.out.println("\tThread "+tid+": Visualizou mapa de assentos.");
    }

    // função que representa a operação de código 2
    public boolean alocaAssentoLivre(Assento a, int tid) {
        Random gerador = new Random();
        int i;
        i = gerador.nextInt(this.quantidade);
        if (this.alocados < this.quantidade) {

            if(escrita[i].tryLock() && this.poltronas[i].getTid() != tid) {
                this.leitura.lock();
                try {
                    this.poltronas[i].Reserva(tid);
                    this.alocados++;
                    a = new Assento(this.poltronas[i]);
                    System.out.println("\tThread "+tid+": Alocou um assento qualquer.");
                    try {
                        this.log.insereBuffer(new Log(2, tid, this.poltronas[i].getPos()+1, this.toString()));
                    } catch (InterruptedException e) {
                        e.printStackTrace();
                    }
                }
                finally {
                    this.leitura.unlock();
                }
                return true;
            }
            else {
                System.out.println("\t* Thread "+tid+": Alocação de assento negado!");
                return false;
            }
        }
        else if(this.alocados == this.quantidade) {
            System.out.println("\t* Thread "+tid+": Veículo cheio!");
            return false;
        }
        return false;
    }

    // função que representa a operação de código 3
    public boolean alocaAssentoDado(Assento a, int tid) {
        if(escrita[a.getPos()].tryLock() && a.getTid() != tid) {
            this.leitura.lock();
            try {
                a.Reserva(tid);
                this.alocados++;
                System.out.println("\tThread "+tid+": Alocou um assento.");
                try {
                    this.log.insereBuffer(new Log(3, tid, a.getPos()+1, this.toString()));
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }
            finally {
                this.leitura.unlock();
            }
            return true;
        }
        else if(this.alocados >= this.quantidade) {
            System.out.println("\t* Thread "+tid+": Veículo cheio!");
            return false;
        }
        else {
            System.out.println("\t* Thread "+tid+": Alocação de assento negado!");
            return false;
        }
    }

    // função que representa a operação de código 4
    public boolean liberaAssento(Assento a, int tid) {
        if(a.getTid() == tid) {
            this.leitura.lock();
            try {
                a.Libera();
                this.alocados--;
                escrita[a.getPos()].unlock();
                System.out.println("\tThread "+tid+": Liberou o assento.");
                try {
                    this.log.insereBuffer(new Log(4, tid, a.getPos()+1, this.toString()));
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }
            finally {
                this.leitura.unlock();
            }
            return true;
        }
        else {
            System.out.println("\t* Thread "+tid+": Liberação de assento negado!");
            return false;
        }
    }
}

