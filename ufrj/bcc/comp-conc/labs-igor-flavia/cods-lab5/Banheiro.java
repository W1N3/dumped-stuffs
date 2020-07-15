
class Banheiro { //Banheiro tem apenas um vaso sanitário (região crítica)
    int i = 1;
    synchronized void vaso() throws InterruptedException {
        while (i <= 0) { //Banheiro ocupado
            System.out.println("Sou a " + Thread.currentThread().getName() +
            " e entrei na fila do banheiro (WAIT).");
            wait(); //Espera na fila do banheiro
        }
        i--;
        System.out.println("---Sou a " + Thread.currentThread().getName()
        + " e estou no vaso sanitario => i = " + i);
        Thread.sleep(2000); //Fazendo as necessidades
        System.out.println("---Sou a " + Thread.currentThread().getName()
        + " e estou saindo do vaso sanitario => i = " + i);
    }

    synchronized void pia() throws InterruptedException {
        i++;
        System.out.println("---Sou a " + Thread.currentThread().getName() + " e estou na pia lavando as mãos => i = " + i);
        Thread.sleep(1000); //Lavando as mãos
        System.out.println("---Sou a " + Thread.currentThread().getName() + " e sai do pia => i = " + i);
        System.out.println();
        notifyAll(); //Avisa todos que o banheiro está livre
    }

}
