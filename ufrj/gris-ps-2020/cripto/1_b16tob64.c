#include <stdio.h>
#include <stdlib.h>

/*
	
	Cryptopals
	Challenge 1 Set 1 - Convert hex to base 64
	the string :
		49276d206b696c6c696e6720796f757220627261696e206c696b65206120706f69736f6e6f7573206d757368726f6f6d
	should produce :
		SSdtIGtpbGxpbmcgeW91ciBicmFpbiBsaWtlIGEgcG9pc29ub3VzIG11c2hyb29t
	...
*/

typedef struct _b64 {
	int size_hex;
	int size_bin;
	int size_64;
	char *raw_hex;
	char *pure_binary;
	char *encoded_64;
} b64;


/*
	function to define if string is hexadecimal or not
	return values :
		0 -> Is not hex
		positive value -> is hex
	positive value is the size of the string;
*/
int is_hexadecimal(char* string) {
	char* head = string;
	int ret = 1;
	while(*(head) != '\0' ) {
		if( !((*(head) > 48 && *(head) < 57) || (*(head) > 65 && *(head) < 70) ||
			(*(head) > 97 && *(head) < 102)) ) {
			ret = 0;
		}
		head++;
	}
	if(ret == 1) {
		ret = (int) (head - string);
	}
	return ret;
}

/*
	function to convert an hex string to binary bytes that correlates
	return values :
		0 -> if success
		1 or whatelse -> in case of error or another stuff

*/
int hex_to_bin(b64* obj) {
	char* hex;

	obj->size_bin = obj->size_hex / 2;
	obj->pure_binary = (char *) malloc(sizeof(char)*obj->size_bin);
	hex = obj->raw_hex;
	while(*(hex) != "\0") {
	}

	return ret;
}

/*
*/
char* bin_to_b64(char* string) {
}

int main (int argc, char** argv) {
	b64 my_example;
	b64 from_arg;
	if(argc > 1) {
		from_arg.size_hex = is_hexadecimal(argv[1]);
		from_arg.raw_hex = argv[1];
		if(from_arg.size_hex > 0) {
			hex_to_bin(&from_arg);
		}
		else {
			// return 2 when argument given is not hexadecimal
			printf("\n Error  - String is not hexadecimal\n");
			return 2;
		}
	}
	else {
		// return 1 when no arguments have been given
		printf("\nError - No arguments given!\n");
		return 1;
	}
	// return 0 on sucess
	return 0;
}
