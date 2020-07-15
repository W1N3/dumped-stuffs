package pacote;

public class Log {
	private int oper;
	private int user;
	private int seat;
	private String cart;
	
	public Log() {
		this.oper = 0;
		this.user = 0;
		this.seat = 0;
		this.cart = "";
	}
	public Log(int o, int u, int s, String c) {
		this.oper = o;
		this.user = u;
		this.seat = s;
		this.cart = c;
	}

	public String toString() {
		return ""+this.oper+","+this.user+","+this.seat+","+this.cart;
	}
}