#!/bin/bash
openssl genrsa -out $1.id_rsa
chmod 400 $1.id_rsa
openssl rsa -in $1.id_rsa -pubout           -outform PEM -out $1.id_rsa.pub
openssl rsa -in $1.id_rsa -RSAPublicKey_out -outform PEM -out $1.id_rsa.pypub