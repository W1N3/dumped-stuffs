
public class ThreadTester {

	public static void main( String args[] ) {
		PrintThread thread1, thread2, thread3, thread4;
		thread1 = new PrintThread( "thread1" );
		thread2 = new PrintThread( "thread2" );
		thread3 = new PrintThread( "thread3" );
		thread4 = new PrintThread( "thread4" );
		System.err.println( "\nIniciando threads" );
		thread1.start();
		thread2.start();
		thread3.start();
		thread4.start();
		System.err.println( "Threads iniciadas\n" );
	}
}

class PrintThread extends Thread {
	private int sleepTime;
	public PrintThread( String name ) {
		super( name );
		// dorme entre 0 e 5 segundos
		sleepTime = (int) ( Math.random() * 5000 );

		System.err.println( "Nome: " + getName() +
		";  dorme: " + sleepTime );
	}
	// executa a thread
	public void run() {
		// coloca thread para dormir por um intervalo aleatório
		try {
			System.err.println( getName() + " vai dormir" );
			Thread.sleep( sleepTime );
		}
		catch ( InterruptedException exception ) {
			System.err.println( exception.toString() );
		}
		// escreve nome da thread 
		System.err.println( getName() + " terminou de dormir" );
	}
}

