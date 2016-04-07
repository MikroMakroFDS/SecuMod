#!/usr/bin/php -q

<?php
error_reporting (E_ALL);


$address = '127.0.0.1';
#$address = '10.32.100.1';
#$address = '10.1.2.6';
$port = 10000;

// ipV4 (AF_INET) - zuverlaessigen (SOCK_STREAM) - TCP (SOL_TCP) -  Socket erzeugen 
(($sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) !== false)  or die("socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()) . "\n");

// Socket connecten
(@socket_connect($sock, $address, $port) !== false) or die("socket_connect() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)) . "\n");

$in = trim(socket_read($sock,2048));
$inarr = explode(" ",$in);

$myIP = gethostbyname(exec("hostname"));

//Debugging
//echo $in."\n$myIP\n";
//print_r($inarr);

if ($inarr[0]!=$myIP)   { echo "Warn: Server means IP is ".$inarr[0]." but IP is $myIP\n";};
if ($inarr[1]!="HELLO") { echo "Err:  Server speaks not HELLO - exiting!"; socket_close($sock); };

openssl_private_decrypt(base64_decode($inarr[2]),$decrypted,"file://client.id_rsa");

echo "Entschlüsselt: $decrypted\n";

$msg="$decrypted || Hallo das sind meine Nutzdaten - hoffentlich kannst du sie lesen!";

openssl_public_encrypt($msg, $encrypted,  "file://server.id_rsa.pub");
$encrypted_b64=base64_encode($encrypted);

echo "Nutzdaten: $msg\nVerschluesselt: $encrypted_b64\n\n";

socket_write($sock,$encrypted_b64,strlen($encrypted_b64));

$in = trim(socket_read($sock,2048));
echo "IN: ".$in."\n\n";

openssl_private_decrypt(base64_decode($in),$decrypted,"file://client.id_rsa");

echo "Entschluesselt: $decrypted\n\n";

$inarr = explode(" || ",$decrypted);

if ($inarr[0]!="OK") { echo "Err:  Server don't speaks OK!"; };
echo "Antwortdaten: ".$inarr[1]."\n\n";



socket_close ($sock);
?>