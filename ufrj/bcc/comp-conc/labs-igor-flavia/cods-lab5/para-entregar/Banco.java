public class Banco {
	
	private int[] contas;
	private int qtd_contas;
	private long num_transacoes;

	Banco(int n, int v) {
		this.qtd_contas = n;
		this.num_transacoes = 0;
		this.contas = new int[n];
		for(int i = 0 ; i < n ; i++) {
			this.contas[i] = v;
		}
	}

	public void transfer(int from, int to, int amount) {
		if(contas[from] < amount) {
			System.out.println("Saldo insuficiente");
		}
		else {
			try {
				this.CriticalSection(from, to, amount);
			} catch (InterruptedException exception) {
				exception.printStackTrace();
			}
			this.saldo_total();
		}

	}

	private void CriticalSection(int f, int t, int a) throws InterruptedException {
		this.contas[f] -= a;
		this.contas[t] += a;
		this.num_transacoes++;
	}

	public int size() {
		return this.qtd_contas;
	}

	public void saldo_total() {
		int tot=0, i;
		for(i = 0 ; i < this.size() ; i++) {
			tot += this.contas[i];
		}
		System.out.println("Total de dinheiro depositado neste banco : " + tot);
	}
	
}