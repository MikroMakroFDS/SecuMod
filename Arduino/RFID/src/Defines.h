#pragma once

#define SS_PIN 10
#define RST_PIN 9
#define MIFARE_BUFFER_SIZE 16

/*___ERROR_CODES___*/

#define ERROR_TRAILER_BLOCK_DET -1
#define STAT_OK 0
#define ERROR_MFRC522_AUTH -2
#define ERROR_MFRC522_WRITE -3
#define ERROR_MFRC522_READ -4