import java.util.concurrent.*;

public class Decrementer implements Runnable {
    CountDownLatch latch = null;
    public Decrementer(CountDownLatch latch) {
        System.out.println ("Construtor do Decrementer");
        this.latch = latch;
    }
    public void run() {
        try {
            System.out.println ("Decrementer vai dormir e decrementar a primeira vez");
            Thread.sleep(1000);
            this.latch.countDown();
            System.out.println ("Decrementer vai dormir e decrementar a segunda vez");
            Thread.sleep(1000);
            this.latch.countDown();
            System.out.println ("Decrementer vai dormir e decrementar a terceira vez");
            Thread.sleep(1000);
            this.latch.countDown();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}