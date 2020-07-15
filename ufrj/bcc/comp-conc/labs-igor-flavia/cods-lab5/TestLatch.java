import java.util.concurrent.*;
/**
 * Escreva a descrição da classe TestLatch aqui.
 * 
 * @author (seu nome) 
 * @version (número de versão ou data)
 */
public class TestLatch
{
    
    public static void main(String [] args)
    {
        CountDownLatch latch = new CountDownLatch(3);
        Waiter      waiter      = new Waiter(latch);
        Decrementer decrementer = new Decrementer(latch);
        new Thread(waiter).start();
        new Thread(decrementer).start();
       
        try {
        Thread.sleep(4000);
    }
    catch (InterruptedException e) {}

    }
}
