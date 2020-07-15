
public class CounterThread extends Thread{

     protected Counter counter = null;

     public CounterThread(Counter counter){
        this.counter = counter;
     }

     public void run() {
	
         try {
         
         for(int i=0; i<10; i++){
           System.out.println ("thread :+ " +this.getName() +" contador : " +  counter.inc());
        }
        
         
     }  
    catch ( InterruptedException exception ) {
      System.err.println( exception.toString() );
}
    }
}