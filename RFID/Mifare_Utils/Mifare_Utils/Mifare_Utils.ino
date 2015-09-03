/*
 Name:		Mifare_Utils.ino
 Created:	9/2/2015 5:52:01 PM
 Author:	Genkinger Lukas
*/



#include "Defines.h"
#include <SPI.h>
#include <MFRC522.h>
#include "Utils.h"

MFRC522 mifare(SS_PIN, RST_PIN);
MFRC522::MIFARE_Key key;
Utils MUtils;

bool check = false;
bool isString = true;

String inputData;

byte blockNumber;
byte data[16];
byte bufferSize = 18;
byte buffer[18];


void setup() {

	Serial.begin(9600);
	//wait
	while (!Serial);

	//INIT the reader
	SPI.begin();
	mifare.PCD_Init();

	//set the default key
	for (int i = 0; i < 6; i++)
	{
		key.keyByte[i] = 0xFF;
	}
	

}




void loop() {

	if (!check){
		Serial.println("Please enter the block to write to: ");
		while (Serial.available() == 0);
		blockNumber = Serial.parseInt();
		Serial.flush();

		Serial.println("[1] - String");
		Serial.println("[2] - Bytes");
		while (Serial.available() == 0);
		int res = Serial.parseInt();
		if (res == 1)
		{
			isString = true;
		}
		else if(res  == 2)
		{
			isString = false;
		}
		else
		{
			Serial.println("ERROR");
			check = true;
		}

		Serial.flush();

		if (isString){
			Serial.println("Please enter the data to write: ");
			while (Serial.available() == 0);
			inputData = Serial.readString();
			inputData.getBytes((unsigned char*)data, 16);
			Serial.flush();
		}
		else if (!isString)
		{
			Serial.println("Please enter the data to write: ");
			while (Serial.available() == 0);
			*data = (byte)Serial.parseInt();
			Serial.flush();
		}
		check = true;
	}

	if (!mifare.PICC_IsNewCardPresent())
	{
		return;
	}
	if (!mifare.PICC_ReadCardSerial())
	{
		return;
	}

	MUtils.writeBlock(blockNumber, data, &key, mifare);
	

	MUtils.readBlock(blockNumber, buffer,&bufferSize,&key,mifare);
	MUtils.dump_byte_array(buffer, bufferSize -2);
	mifare.PICC_HaltA();
	mifare.PCD_StopCrypto1();
	check = false;
	for (int l = 0; l < 16; l++)
	{
		data[l] = 0;
		buffer[l] = 0;
	}
	delay(1000);

}
