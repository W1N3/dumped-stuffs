/*
Multiples of 3 and 5

	If we list all the natural numbers below 10 that are multiples of 3 or 5, we get 3, 5, 6 and 9. The sum of these multiples is 23.

	Find the sum of all the multiples of 3 or 5 below 1000.
*/

#include <stdio.h>

void solution() {
	int i, total;
	total = 0;
	for(i = 0; i < 1000 ; i++) {
		if((i%3 == 0) || (i%5 == 0)) {
			total += i;
		}
	}
	printf("\n\t\tThe sum of multiples of 3 or 5 until 1000 is = %d\n", total);
}

int main(int argc, char **argv) {
	printf("\nMultiples of 3 and 5\n");
	printf("\n\tIf we list all the natural numbers below 10 that are multiples of 3 or 5, we get 3, 5, 6 and 9. The sum of these multiples is 23.");
	printf("\n\tFind the sum of all the multiples of 3 or 5 below 1000.");
	solution();
	return 0;
}
// solved by W1N3

