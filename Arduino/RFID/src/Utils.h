// Utils.h

#ifndef _UTILS_h
#define _UTILS_h
#include <SPI.h>
#include <MFRC522.h>
#include "Defines.h"
#if defined(ARDUINO) && ARDUINO >= 100
	#include "arduino.h"
#else
	#include "WProgram.h"
#endif
 
class Utils{
public:
	 Utils();
	 int writeBlock(byte blockNumber, byte data[], MFRC522::MIFARE_Key *key, MFRC522 &Instance);
	 int checkIfTrailer(byte blockNumber);
	 int readBlock(byte blockNumber, byte writeBackBuffer[], byte *bufferSize, MFRC522::MIFARE_Key *key, MFRC522 &Instance);
	 byte getTrailerBlock(byte blockNumber);
	 int Authenticate(MFRC522::PICC_Command CMD, byte trailerBlock, MFRC522::MIFARE_Key *key, MFRC522 &Instance);
	 void dump_byte_array(byte *buffer, byte bufferSize);
private:
	bool m_isAuthenticated = false;
};
#endif

