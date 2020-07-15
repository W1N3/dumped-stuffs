import java.util.concurrent.*;
public class Waiter implements Runnable{

    CountDownLatch latch = null;
    public Waiter(CountDownLatch latch) {
        System.out.println ("Construtor do Waiter");
        this.latch = latch;
    }
    public void run() {
        try {
            System.out.println ("Entrou no método run do Waiter");
            latch.await();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
        System.out.println("Waiter Liberado");
    }
}