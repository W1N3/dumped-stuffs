
public class TestSynchronization4{  
    public static void main(String t[]){  
	MyThread1 t1=new MyThread1();  
	MyThread2 t2=new MyThread2();  
	MyThread3 t3=new MyThread3();  
	MyThread4 t4=new MyThread4();  
	t1.start();  
	t2.start();  
	t3.start();  
	t4.start();  }  }  
