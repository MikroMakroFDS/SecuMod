#!/usr/bin/php -q

<?php
error_reporting (E_ALL);

/** erzeugt eine zufaellige Zeichenfolge **/
function zufallsstring($laenge) {
   //MÃ¶gliche Zeichen fÃ¼r den String
   $zeichen = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ()!.:=';
 
   //String wird generiert
   $str = '';
   $anz = strlen($zeichen);
   for ($i=0; $i<$laenge; $i++) {
      $str .= $zeichen[rand(0,$anz-1)];
   }
   return $str;
}

// SchlieÃŸt die Socket-VBerbindung und loescht sie aus dem Array
function closeSocket($key) {
global $verbindungen;
           socket_close($verbindungen["RID"][$key]);
           echo "connection closed: $key [".$verbindungen["IP"][$key]."] \n";

           unset($verbindungen["RID"][$key]);
           unset($verbindungen["state"][$key]);
           unset($verbindungen["key"][$key]);
           unset($verbindungen["IP"][$key]);


};


/* Das Skript wartet auf hereinkommende Verbindungsanforderungen. */
set_time_limit (0);


$address = '127.0.0.1';
//$address = '10.1.2.6';
//$address = '10.32.100.1';
$port = 10000;

// ipV4 (AF_INET) - zuverlaessigen (SOCK_STREAM) - TCP (SOL_TCP) -  Socket erzeugen 
(($sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) !== false)  or die("socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()) . "\n");

// Socket fuer ReUse vorbereiten
if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo socket_strerror(socket_last_error($sock));
    exit;
} 


// Socket an Adresse binden
(@socket_bind($sock, $address, $port) !== false) or die("socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)) . "\n");


// am Socket hoeren / maximal 5 herinkommende Anfragen in der Warteschlange
(@socket_listen($sock, 5) !== false) or die("socket_listen() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)) . "\n");

//mehrere Verbindungen zulassen, indem der socket accept nicht blockiert
@socket_set_nonblock($sock);


//Array fuer die Verbundenen Client-Sockets
$verbindungen = array(); 

//Running-File schreiben
$datei=fopen("server_running","w");
fputs($datei,"Server gestartet: ".date("j.n.Y G:i:s u")."\n");
fclose($datei);


//$verbindungsarray anlegen
$verbindungen["RID"] = array();

echo "Server: Mainloop gestartet auf [$address | $port] ...\n";



//Mainloop
do {
    
    //Neue Verbindung hereinlassen
    if (($msgsock = @socket_accept($sock)) !== false) {

        $i = time();
        socket_getpeername($msgsock,$verbindungen["IP"][$i]);

        $verbindungen["RID"][$i]      = $msgsock;
        $verbindungen["state"][$i]    = 0;
        $verbindungen["key"][$i]      = zufallsstring(100);

        openssl_public_encrypt($verbindungen["key"][$i], $encrypted,  "file://client.id_rsa.pub");
        $msg = $verbindungen["IP"][$i]." HELLO ".base64_encode($encrypted)."\n";

        socket_write($msgsock, $msg, strlen($msg));

        echo "$msgsock [".$verbindungen["IP"][$i]."] - new connection\n";
        echo "DEBUG: ".$verbindungen["key"][$i]."\n";
    };


    if (count($verbindungen["RID"])>0) {
      $read   = $verbindungen["RID"];   // Lese auf diesen Sockets
      $write  = NULL;                   // Sockets zum Schreiben nicht prüfen
      $except = NULL;                   // Fehlerprüfung für keine Sockets durchführen

      // Hat sich an den Sockets was getan?
      (($Anzahl = socket_select($read,$write,$except,0))!== false) or die("Fehler bei socket_select!");


      if ($Anzahl>0) {
        foreach($read as $aktsock) {

          // aus dem Socket lesen
          if (($buf = @socket_read ($aktsock, 2048)) === false) {
             if (socket_last_error($aktsock)==104) {
               $key = array_search($aktsock, $verbindungen["RID"]);
               closeSocket($key);
             } else {
               echo ("socket_read() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($aktsock)) . "[".socket_last_error($aktsock)."]\n");
             };
          } else {
             $buf = trim ($buf); //Leerzeichen entfernen
             if ($buf!="") {
                echo "Message: $buf\n";
                openssl_private_decrypt(base64_decode($buf),$decrypted,"file://server.id_rsa");
                $inarr = explode(" || ",$decrypted);
                $key = array_search($aktsock, $verbindungen["RID"]);
                echo "Uebermittelter Key: ".$inarr[0]." \n";
                echo "korrekter Key:      ".$verbindungen["key"][$key]." - diese stimmen ".(($inarr[0]!=$verbindungen["key"][$key])?"nicht":"")." ueberein!\n";
                echo "entschluesselte Nachricht: ".$inarr[1]."\n";
                echo "Message-Antwort gesendet: OK || Antwort\n";
                openssl_public_encrypt("OK || Antwort", $encrypted,  "file://client.id_rsa.pub");
                $msg = base64_encode($encrypted);
                socket_write($aktsock, $msg, strlen($msg));
                Closesocket($key);
             };
          };
        };
      };

      // Connection-Timeout nach 5 Sekunden
      foreach(array_keys($verbindungen["RID"]) as $key) {
        if ($key+5<time()) {
           Closesocket($key);
        };
      };
   };
} while (file_exists("server_running"));

if (count($verbindungen)>0) { foreach ($verbindungen["RID"] as $aktsock) { echo "shutdown: $aktsock\n"; socket_close($aktsock); } };

socket_close ($sock);
@unlink("server_running");

echo "Server stopped\n";
?>