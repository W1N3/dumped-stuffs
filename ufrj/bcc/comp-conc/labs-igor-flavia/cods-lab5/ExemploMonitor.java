
public class ExemploMonitor { //Exemplo do uso do wait/notify
	public static void main(String[] args) {
		//laço para criar 10 alunos (objetos da classe Aluno) e inciar
		for (int i = 1; i < 10; i++) {
			Aluno a = new Aluno();
			a.start();
		}
	}
}
