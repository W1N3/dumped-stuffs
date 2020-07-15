

public class ThreadTester2 {

	public static void main( String args[] ) {
		PrintThread thread1, thread2, thread3, thread4,thread5;
		thread1 = new PrintThread( "thread1" );
		thread2 = new PrintThread( "thread2" );
		thread3 = new PrintThread( "thread3" );
		thread4 = new PrintThread( "thread4" );
		thread5 = new PrintThread( "thread5" );
		System.err.println( "\n Configurando diferentes prioridades para as threads" );

		thread5.setPriority (Thread.MAX_PRIORITY);
		thread1.setPriority (1);
		thread2.setPriority (2);
		thread3.setPriority (3);
		thread4.setPriority (4);




		System.err.println( "\nIniciando threads" );
		thread1.start();
		thread2.start();
		thread3.start();
		thread4.start();
		thread5.start();
		System.err.println( "Threads iniciadas\n" );
	}
}

class PrintThread extends Thread {
	private int sleepTime;
	private int counter = 0;
	public PrintThread( String name ) {
		super( name );
		// dorme 5 segundos
		sleepTime = 5000;

		System.err.println( "Nome: " + getName() +
		";  dorme: " + sleepTime );
	}
	// executa a thread
	public void run() {
		// coloca thread para dormir por um intervalo fixo
		try {
			System.out.println( getName() + " entrou em execucao e vai dormir" );
			Thread.sleep( sleepTime );
			System.out.println( getName() + " acordou " );
		}
		catch ( InterruptedException exception ) {
			System.err.println( exception.toString() );
		}


		for (int i = 0; i < 100000000; i++) {
			// Just increment a counter.
			counter++;
		}

		System.out.println("Thread " + Thread.currentThread().getName() +
			" Com ID = " + Thread.currentThread().getId() + "  e prioridade " +
			Thread.currentThread().getPriority() + " terminou de executar.");
	}
}
