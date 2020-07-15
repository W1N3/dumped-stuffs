package pacote;

import pacote.Log;
import java.io.*;
// Produtor/Consumidor
//classe que registra as operações das threads e grava o arquivo de log de saída
public class Buffer {
	private String caminho;
	private PrintWriter writer;
	private Log[] data;
	private int ondeInsere;
	private int ondeRetira;
	private boolean podeInserir;
	private boolean podeRetirar;
	private int acumulo;


	public Buffer(int size, String caminho) {
		this.caminho = caminho;
		try {
			this.writer = new PrintWriter((new FileOutputStream(new File(this.caminho+".log"), false)));
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		}

		this.data = new Log[size];
		this.ondeInsere = 0;
		this.ondeRetira = 0;
		this.podeInserir = true;
		this.podeRetirar = false;
		this.acumulo = 0;
	}

	public int getAcumulo() {
		return this.acumulo;
	}

	public synchronized void insereBuffer(Log a) throws InterruptedException {
		while(this.podeInserir == false) {
			try {
				this.wait();
			} catch (InterruptedException e) {
				e.printStackTrace();
			}
		}
		this.podeInserir = false;
		this.podeRetirar = false;

		this.data[this.ondeInsere] = a;
		this.ondeInsere = (this.ondeInsere + 1) % this.data.length;
		this.acumulo++;

		if(this.acumulo < this.data.length) {
			this.podeInserir = true;
		}
		this.podeRetirar = true;
		this.notifyAll();
	}
	public synchronized int retiraBuffer() throws InterruptedException {
		while(this.podeRetirar == false) {
			try {
				if(this.acumulo == 0) {
					return 1;
				}
				else {
					this.wait();
				}
			}
			catch (InterruptedException e) {
				e.printStackTrace();
			}
		}
		this.podeRetirar = false;
		this.podeInserir = false;

		try {
			this.writer = new PrintWriter((new FileOutputStream(new File(this.caminho+".log"), true)));
			this.writer.println(this.data[this.ondeRetira].toString());
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		}
		finally {
			this.writer.close();
		}
		this.ondeRetira = (this.ondeRetira + 1) % this.data.length;
		acumulo--;

		if(this.acumulo > 0) {
			this.podeRetirar = true;
		}
		this.podeInserir = true;
		this.notifyAll();
		return 0;
	}
}
