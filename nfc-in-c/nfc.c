#include <unistd.h>
#include <stdio.h>
#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>

#include <ctacs/ct_api.h>

/*
Programa para explorar o leitor/escritor ACR122U - ACS
*/

/*
int main(int argc, char **argv) {
	int file_desc_temp;
	long int file_size;
	char *find_usb, *find_device, *file_content;
	regex_t regex_structure;
	regmatch_t matches[2];

	find_usb = (char*) malloc(sizeof(char)*12);
	find_usb[0] = 0;
	strcpy(find_usb, "Bus\\s[0-9]*");

	find_device = (char*) malloc(sizeof(char)*15);
	find_device[0] = 0;
	strcpy(find_device, "Device\\s[0-9]*");

	regcomp(&regex_structure, find_usb, REG_NOSUB | REG_ICASE | REG_EXTENDED);
	regcomp(&regex_structure, find_device, REG_NOSUB | REG_ICASE | REG_EXTENDED);


	system("lsusb | grep -e \"ACR122U\" > /tmp/acs.temp");
	file_desc_temp = open("/tmp/acs.temp", O_RDONLY, S_IRUSR | S_IRGRP | S_IROTH);
	if(file_desc_temp == -1) {
		printf("file_desc_temp - error: %s", strerror(errno));
		exit(EXIT_FAILURE);
	}
	file_size = lseek(file_desc_temp, 0, SEEK_END);
	file_content = (char *) malloc(sizeof(char)*file_size);
	if(read(file_desc_temp, file_content, file_size) == -1) {
		printf("read() - error: %s", strerror(errno));
		exit(EXIT_FAILURE);
	}
	system("shred -zn3 /tmp/acs.temp && rm /tmp/acs.temp");

	regexec(&regex_structure, file_content, 2, &matches, 0);

	free(find_usb);
	free(find_device);
	free(file_content);

	return 0;
}
*/


//*
/*

Coisas a serem feitas para isso rodar :
	
	rodar o ./install do ACS-Unified-LIB
	rodar os comandos abaixos relativos ao ACS-Unified-Driver:
		./configure
		make
		make install

    dnf install pcsc-lite
    dnf install pcsc-tools
    
    sudo nano /etc/modprobe.d/blacklist.conf (verificar se é isso mesmo no fedora)
        install nfc /bin/false
        install pn533 /bin/false

    modprobe -r np533_usb
    modprobe -r np533
    modprobe -r nfc


	Compilar com a opção -lctacs : 
		gcc -o nfc nfc.c -lctacs

	ctacs.ini deve ser exatamente :
		[CardTerminal]
		CTN1=ACR122U

		[ACR122U]
		ICC1=ACS ACR122U 00 00


*/

int main(int argc, char *argv[])
{
    char ret;
    unsigned short ctn;
    unsigned short pn;
    unsigned char sad;
    unsigned char dad;

    // REQUEST ICC
    /*
    commands
        Get UID APDU Format (5 bytes)
            Get UID Response Format (UID + 2 bytes) if P1 = 00h
            0xFF, 0xCA, 0x00, 0x00, 0x00
            Get ATS of a ISO 14443 A card (ATS + 2 bytes) if P1 = 01h
            0xFF, 0xCA, 0x00, 0x00, 0x00

        Load Authentication Keys APDU Format (11 bytes)
            0xFF, 0x82, 0x00, 0x00|1, 0x00, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF

        
    */
    unsigned char command[] = { 0x20, 0x12, 0x01, 0x00, 0x00 };
    unsigned short lenc = sizeof(command);

    unsigned char response[300];
    unsigned short lenr = sizeof(response);
    unsigned short i;

    ctn = 1;
    pn = 1;

    // Initialize card terminal
    ret = CT_init(ctn, pn);
    if (ret != OK)
    {
        printf("Error: CT_init failed with error %d\n", ret);
		return 1;
    }

    sad = 2; // Source = Host
    dad = 1; // Destination = Card Terminal
 
    // Send command
    ret = CT_data(ctn, &dad, &sad, lenc, command, &lenr, response);
    if (ret != OK) {
        printf("Error: CT_data failed with error %d\n", ret);
    }
    else {
        // Display response
        //printf("Response: ");
        for (i = 0; i < lenr; i++)
            printf("%02X ", response[i]);
        printf("\n");
    }
 
    // Close card terminal
    ret = CT_close(ctn);
    if (ret != OK)
        printf("Error: CT_close failed with error %d\n", ret);
 
    return 0;
}

//*/