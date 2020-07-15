
public class Example {

    public static void main(String[] args){
      
        System.out.println ("Criando objeto contador");
        Counter counter = new Counter();
         System.out.println ("Criando duas threads");
      Thread  threadA = new CounterThread(counter);
      Thread  threadB = new CounterThread(counter);
     
      threadA.start();
      threadB.start(); 
    }
  }