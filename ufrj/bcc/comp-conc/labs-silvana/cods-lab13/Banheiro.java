/* Disciplina: Computacao Concorrente */
/* Prof.: Silvana Rossetto */
/* Lab 13: Banheiro unissex usando monitores */

/* completar trechos com ... */
/* -------------------------------------------------------------------*/

// Monitor
class BanheiroUni {
  //declarar atributos do monitor
  ...

  // Construtor
  BanheiroUni() { 
    //inicializar os atributos do monitor
    ...
  } 
  
  // Entrada para mulheres
  public synchronized void EntraMulher (int id) {
    try { 
      ...
      System.out.println ("M[" + id + "]: entrou, total de " + ??? + " mulheres no banheiro");
    } catch (InterruptedException e) { }
  }
  
  // Saida para mulheres
  public synchronized void SaiMulher (int id) {
      ...
      System.out.println ("M[" + id + "]: saiu, restam " + ??? + " mulheres no banheiro");
  }
  
  // Entrada para homens
  public synchronized void EntraHomem (int id) {
    try { 
       ...
       System.out.println ("H[" + id + "]: entrou, total de " + ??? + " homens no banheiro");
    } catch (InterruptedException e) { }
  }
  
  // Saida para homens
  public synchronized void SaiHomem (int id) {
       ...
       System.out.println ("H[" + id + "]: saiu, restam " + ??? + " homens no banheiro");
  }
}

//--------------------------------------------------------
// Mulher
class Mulher extends Thread {
  int id;
  int delay;
  BanheiroUni b;

  // Construtor
  Mulher (int id, int delayTime, BanheiroUni b) {
    this.id = id;
    this.delay = delayTime;
    this.b = b;
  }

  // Método executado pela thread
  public void run () {
    double j=777777777.7, i;
    try {
      for (;;) {
        this.b.EntraMulher(this.id);
        for (i=0; i<100000000; i++) {j=j/2;} //...loop bobo para simbolizar o tempo no banheiro
        this.b.SaiMulher(this.id);
        sleep(this.delay); 
      }
    } catch (InterruptedException e) { return; }
  }
}

//--------------------------------------------------------
// Homem
class Homem extends Thread {
  int id;
  int delay;
  BanheiroUni b;

  // Construtor
  Homem (int id, int delayTime, BanheiroUni b) {
    this.id = id;
    this.delay = delayTime;
    this.b = b;
  }

  // Método executado pelo thread
  public void run () {
    double j=777777777.7, i;
    try {
      for (;;) {
        this.b.EntraHomem(this.id); 
        for (i=0; i<100000000; i++) {j=j/2;} //...loop bobo para simbolizar o tempo no banheiro
        this.b.SaiHomem(this.id); 
        sleep(this.delay);
      }
    } catch (InterruptedException e) { return; }
  }
}

//--------------------------------------------------------
// Classe principal
class Banheiro {
  static final int M = 8;
  static final int H = 7;

  public static void main (String[] args) {
    int i;
    BanheiroUni b = new BanheiroUni();// Monitor
    Mulher[] m = new Mulher[M];       // Mulheres
    Homem[] h = new Homem[H];         // Homens

    for (i=0; i<M; i++) {
       m[i] = new Mulher(i+1, (i+1)*500, b);
       m[i].start(); 
    }
    for (i=0; i<H; i++) {
       h[i] = new Homem(i+1, (i+1)*500, b);
       h[i].start(); 
    }
  }
}
