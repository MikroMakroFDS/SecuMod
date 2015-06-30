#! /usr/bin/python3

import socket              # TCP Sockets
import string, base64      # Stringbearbeitung und Base64-En- und Decoding
import rsa                 # RSA-Modul
import random

def id_generator(size=6, chars=string.ascii_uppercase + string.digits):    return ''.join(random.choice(chars) for _ in range(size))


s = socket.socket()         # Create a socket object
host = "127.0.0.1"
port = 10000                # Reserve a port for your service.

#host = socket.gethostname() # Get local machine name
print ("Server started on " + host + " Port: " + str(port))

s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)

s.bind(("127.0.0.1", port))        # Bind to the port

s.listen(5)                 # Now wait for client connection.
while True:
   c, addr = s.accept()     # Establish connection with client.
   print ('Got connection from', addr)

   # Zufallswort verschluesseln mit Client-pub-key
   skey=id_generator(100)
   print("sKey: " + skey)
   client_key = rsa.PublicKey.load_pkcs1(open('client.id_rsa.pypub').read().encode())
   encrypted  = rsa.encrypt(skey.encode(), client_key)

   # Nachricht verfassen, an Client senden
   msg = addr[0].encode() + b' HELLO ' + base64.b64encode(encrypted)
   print("gesendet: " + msg.decode())
   c.send(msg)

   # Nachricht aus Socket lesen
   msg=c.recv(2048)
   print ("Antwort: " + msg.decode())

   # Mit Server-private-key entschluesseln
   server_key = rsa.PrivateKey.load_pkcs1(open('server.id_rsa').read().encode(),'PEM')
   decrypted  = rsa.decrypt(base64.b64decode(msg), server_key)

   print ("Entschluesselt: " + decrypted.decode())
   erg = decrypted.split(b" || ")

   if ( erg[0] == skey ) :
     print ("Sicherheitskey: " + erg[0].decode() + " - ist korrekt!")
   else :
     print ("Sicherheitskey: " + erg[0].decode() + " - stimmt nicht !!!!!!!!!!!!!!!")

   print ("Nutzdaten: " + erg[1].decode())

   # Antwort senden
   msg =  "OK || Antwort von Python 2"
   print ("gesendet: " + msg + "\n\n")
   encrypted = rsa.encrypt(msg.encode(), client_key)   
   
   c.send(base64.b64encode(encrypted))
  


   c.close()                # Close the connection