import java.lang.Math;

public class TransferThread extends Thread {
	private Banco bank;
	private int origin;
	private int max_trans_value;

	TransferThread(Banco b, int o, int t) {
		this.bank = b;
		this.origin = o;
		this.max_trans_value = t;
	}

	public void run() {
		int value = 0, destiny;
		while(!interrupted()) {
			destiny = this.origin;
			while(destiny != this.origin) {
				destiny = (int ) (Math.random() * this.bank.size());
			}
			value = (int ) (Math.random() * this.max_trans_value);
			this.bank.transfer(this.origin, destiny, value);
		}
	}
}