
class Aluno extends Thread {
	static Banheiro b = new Banheiro();
	public void run() {
		try {
			b.vaso();
			Thread.sleep(50); //Indo para a pia
			b.pia();
		}
		catch (InterruptedException e) {
			e.printStackTrace();
		}
	}
}
