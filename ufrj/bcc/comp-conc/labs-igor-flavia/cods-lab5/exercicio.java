import java.util.concurrent.*;

public class Exercicio {

	//private static int numAccounts = 25;
	//private static int initialBalance = 500;

	public static void main(String[] args) {
		
		

		Bank B = new Bank(numAccounts, initialBalance);
		
		for (int i=0; i<threads.length; i++) {
                  threads[i] = new Thread(new TransferThread());
      	}

	}

	public class Bank {
		int[] accounts;
		long int transactions; 
		int accountID;

		Bank(int na, int ib) {
			
			for(accountID = 0; accountID < Accounts.lenght; accountID++){
				this.accounts[accountID] = ib;
			}

			this.transactions = 0;
		}

		public int size(){
			return Accounts.lenght;
		}

		public void transfer(int from, int to, int amount){

		}
	}

	class TransferThread implements Runnable {
		private static Bank ;
		private static int 

		TransferThread() {

		}
	}

}
