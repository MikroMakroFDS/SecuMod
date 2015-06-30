#! /usr/bin/python3

# Module importieren
import socket              # TCP Sockets
import string, base64      # Stringbearbeitung und Base64-En- und Decoding
import rsa                 # RSA-Modul

# Host-IP und Port festlegen
host = "127.0.0.1"
#host = "10.32.100.1"
port = 10000                

# Socket oeffnen
s = socket.socket()

# Socket verbinden
print('\n\nConnecting ' + str(host) + ' Port: ' + str(port) + "\n"  )
s.connect((host, port))       

# Nachricht von Server holen
msg = s.recv(2048);
print("Antwort: " + msg.decode())

# Antwort in die drei Teile zerlegen: IP HELLO BASE64-encodierte Nachricht
erg = msg.strip().split(b' ')

# erster Check: Stimmt die IP, die beim Server ankam mit unserer ueberein?
if ( erg[0] == socket.gethostbyname(socket.gethostname())) :
  print('Von Server uebermittelte IP ' + erg[0].decode() + ' stimmt mit ermittelter IP: ' + socket.gethostbyname(socket.gethostname()) + ' ueberein!\n')
elif ( erg[0] == b"127.0.0.1" ) :
  print('localhost-Zugriff - Von Server uebermittelte IP ' + erg[0].decode() + '!\n')
else :
  print('Server denkt ich habe IP ' + erg[0].decode() + ' stimmt nicht mit ermittelter IP: ' + socket.gethostbyname(socket.gethostname()) + ' ueberein!  => ABBRUCH\n\n')
  exit (1)


# zweiter Check: Protokoll sieht ein HELLO an zweiter Stelle vor
if ( erg[1] != b"HELLO" ) :
   print ("Server sagt nicht HELLO! => ABBRUCH\n\n")
   exit(2)

# Ausgabe des vom Server uebermittelten Nutztextes (verschluesselt und base64-encodiert)
print("DEBUG erg[2] = " + erg[2].decode() + "\n")

# Private und PubKey laden
privkey = rsa.PrivateKey.load_pkcs1(open('client.id_rsa').read().encode(),'PEM')
pubkey = rsa.PublicKey.load_pkcs1(open('server.id_rsa.pypub').read().encode())

# Mit Client-private-key entschluesseln
decrypted = rsa.decrypt(base64.b64decode(erg[2]), privkey)
print("Entschluesselt (Zufallswort): " + decrypted.decode() + "\n")

# Nutzdaten anhaengen
msg = decrypted.decode() + " || " + "Dies sind meine Nutzdaten - hoffentlich kannst du sie lesen! (Python3-Client)"

# Mit Server-public-key verschluesseln und base64-encodieren
encrypted = rsa.encrypt(msg.encode(), pubkey)
msg = base64.b64encode(encrypted)

# Ausgabe des gesendeten Strings
print ("gesendet: " + msg.decode() + "\n\n")

# Senden
s.send(msg)

# Antwort des Servers holen
msg = s.recv(2048);
print("Antwort: " + msg.decode())

# Mit Client-private-key entschluesseln
decrypted = rsa.decrypt(base64.b64decode(msg), privkey)
print("Entschluesselt: " + decrypted.decode() + "\n")

# Antwort in die zwei Teile zerlegen: OK || Nutzdaten
erg = decrypted.strip().split(b' || ')

# Hat der Server OK geantwortet?
if ( erg[0] != b"OK") :
  print("Server hat nicht OK geantwortet -> Fehler!")

print("Nutzdaten der Antwort: " + erg[1].decode() )


# Socket schliessen
s.close()          