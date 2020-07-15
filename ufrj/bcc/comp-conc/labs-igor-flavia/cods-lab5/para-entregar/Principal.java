public class Principal {

	private static int num_contas;
	private static int valor_inicial;

	public static void main(String[] args) {
		
		int i;
		Banco b;
		TransferThread thrs[];

		num_contas = 20;
		valor_inicial = 1000;
		thrs = new TransferThread[num_contas];


		b = new Banco(num_contas, valor_inicial);
		b.saldo_total();
		for(i = 0 ; i < num_contas ; i++) {
			//criar threads de transferencia
			thrs[i] = new TransferThread(b, i, 500);
			thrs[i].start();
		}
	}
}