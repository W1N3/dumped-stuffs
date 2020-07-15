import pacote.*;
import java.util.Random;
import java.util.Scanner;

public class Main {

	private static int quantidade_de_threads;
	private static int quantidade_de_assentos;
	private static int i;
	private static String caminho;
	private static Usuaria[] u;
	private static Mapa map;
	private static Buffer log;

	public static void main (String[] args) {
		if(args.length == 3) {
			try {
				caminho = args[0];
				quantidade_de_threads = Integer.parseInt(args[1]);
				quantidade_de_assentos = Integer.parseInt(args[2]);
			} catch(NumberFormatException e) {
				e.printStackTrace();
			}
		}
		else {
			System.out.println("Digite corretamente os parametros : <arquivo do log de saída> <quantidade de threads> <quantidade de assentos>");
			return;
		}

		log = new Buffer(10, caminho);
		map = new Mapa(quantidade_de_assentos, log);
		u = new Usuaria[quantidade_de_threads];
		
		for(i = 0 ; i < quantidade_de_threads ; i++) {
			u[i] = new Usuaria(i, map);
			u[i].start();
	    }

	   	for(i = quantidade_de_threads-1 ; i >= 0 ; i--) {
	   		try {
	            if(i == 0) {
	            	while(map.estaRodando()) {
	            		map.Parar();
	            	}
	            }
				u[i].join();
			} catch (InterruptedException e) {
	            System.out.println("Programa interrompido!");
	        }
	    }
		System.out.println("Programa encerrado com sucesso.");
	}
}
