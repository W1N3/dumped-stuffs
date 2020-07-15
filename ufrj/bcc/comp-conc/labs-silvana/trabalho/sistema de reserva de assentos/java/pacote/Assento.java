package pacote;

// classe que representa um assento
public class Assento {
    private int tid;
    private int pos;


    Assento() {
        this.tid = 0;
        this.pos = -1;
    }
    Assento(Assento a) {
        this.tid = a.tid;
        this.pos = a.pos;
    }
    Assento(int pos, int tid) {
        this.tid = tid;
        this.pos = pos;
    }

    public Object clone() throws CloneNotSupportedException {
        return new Assento( this );
    }

    public void Reserva(int tid) {
       this.tid = tid;
    }
    public void Libera() {
       this.tid = 0;
    }
    public int getTid() {
        return this.tid;
    }
    public int getPos() {
        return this.pos;
    }
}
