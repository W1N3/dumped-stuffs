
import java.util.concurrent.*;



public class AccountWithoutSync2 {

  private static Account account = new Account();



  public static void main(String[] args) {

    
      Thread[] threads = new Thread[100];

      //--PASSO 2: transformar o objeto Runnable em Thread
      for (int i=0; i<threads.length; i++) {
                  threads[i] = new Thread(new AddAPennyThread());
      }

      //- iniciar as threads
      for (int i=0; i<threads.length; i++) {
         threads[i].start();
      }

      
   // Wait until all tasks are finished
    
       for (int i=0; i<threads.length; i++) {
         try { threads[i].join(); } 
         catch (InterruptedException e) { return; }
      }




    System.out.println("What is balance ? " + account.getBalance());

  }



  // A thread for adding a penny to the account
  private static class AddAPennyThread implements Runnable {

    public void run() {

      account.deposit(1);

    }

  }



  // An inner class for account
  private static class Account {

    private int balance = 0;



    public int getBalance() {

      return balance;

    }



    public void deposit(int amount) {
      
      int newBalance = balance + amount;



      // This delay is deliberately added to magnify the
      // data-corruption problem and make it easy to see.
      try {

        Thread.sleep(1);

      }

      catch (InterruptedException ex) {

      }



      balance = newBalance;

    }

  }

}

