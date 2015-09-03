// 
// 
// 

#include "Utils.h"

Utils::Utils()
{

}

int Utils::writeBlock(byte blockNumber, byte data[], MFRC522::MIFARE_Key *key, MFRC522 &Instance)
{
	
	byte status;
	if (!m_isAuthenticated)
	{
		this->Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, getTrailerBlock(blockNumber), key, Instance);
	}
	if (checkIfTrailer(blockNumber) == ERROR_TRAILER_BLOCK_DET)
	{
		Serial.println("[WARNING] - THIS IS A TRAILER BLOCK -> READ_ONLY (write denied!)");
		return ERROR_TRAILER_BLOCK_DET;
	}

	status = Instance.MIFARE_Write(blockNumber, data, MIFARE_BUFFER_SIZE);

	if (status != MFRC522::STATUS_OK)
	{
		return ERROR_MFRC522_WRITE;
	}
	else
	{
		return STAT_OK;
	}
}

int Utils::readBlock(byte blockNumber, byte *writeBackBuffer, byte *bufferSize, MFRC522::MIFARE_Key *key, MFRC522 &Instance)
{
	byte status;
	if (!m_isAuthenticated)
	{
		this->Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, getTrailerBlock(blockNumber), key, Instance);
	}

	if (checkIfTrailer(blockNumber) == ERROR_TRAILER_BLOCK_DET)
	{
		Serial.println("[WARNING] - THIS IS A TRAILER BLOCK -> READ_ONLY (reading!)");
	}

	status = Instance.MIFARE_Read(blockNumber, writeBackBuffer,bufferSize);

	if (status != MFRC522::STATUS_OK)
	{
		return ERROR_MFRC522_READ;
	}
	else
	{
		return STAT_OK;
	}
	

}

int Utils::checkIfTrailer(byte blockNumber)
{
	
	byte trailerBlock = getTrailerBlock(blockNumber);

	if (blockNumber > 2 && (blockNumber + 1) % 4 == 0)
	{
		return ERROR_TRAILER_BLOCK_DET;
	}
	else
	{
		return STAT_OK;
	}
}

byte Utils::getTrailerBlock(byte blockNumber)
{
	byte largestModulo4 = blockNumber / 4 * 4;
	byte trailerBlock = largestModulo4 + 3;
	return trailerBlock;
}

void Utils::dump_byte_array(byte *buffer, byte bufferSize) {
	for (byte i = 0; i < bufferSize; i++) {
		Serial.print(buffer[i] < 0x10 ? " 0" : " ");
		Serial.print(buffer[i], HEX);
	}
	Serial.println("");
	for (byte i = 0; i < bufferSize; i++) {
		Serial.print(buffer[i] < 0x10 ? " 0" : " ");
		Serial.write(buffer[i]);
	}
	Serial.println("");

}

int Utils::Authenticate(MFRC522::PICC_Command CMD, byte trailerBlock, MFRC522::MIFARE_Key *key, MFRC522 &Instance)
{
	if (!m_isAuthenticated){
		byte status;
		status = Instance.PCD_Authenticate(CMD, trailerBlock, key, &(Instance.uid));
		if (status != MFRC522::STATUS_OK)
		{
			return ERROR_MFRC522_AUTH;
			m_isAuthenticated = false;
		}
		else
		{
			return STAT_OK;
			m_isAuthenticated = true;
		}
	}
	else
	{
		return STAT_OK;
	}
}