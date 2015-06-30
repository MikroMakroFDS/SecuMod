#! /usr/bin/python3

# Config-Klasse des MikroMakro Zutrittskontrollservers
# (C) 2015 Kepler-Gymnasium Freudenstadt
#
# Die gesamte Software unterliegt der GNU GPLv3
# http://www.gnu.org/licenses/gpl-3.0.html
#

import array


class _Config :
    
    _C={}
    Err    =  0;
    ErrMsg = "";

    def get(self,ConfigName) :
      if (ConfigName in self._C) :
        self.Err = 0;
        self.ErrMsg = "";
        return self._C[ConfigName];
      else :
        self.Err = 1;
        self.ErrMsg = ConfigName + " not a valid Configuration-Option!";
        return False;
      

class S_Config(_Config) :

    def __init__() :
      self._C['SERVERIP']    = '127.0.0.1';
      self._C['SERVERPORT']  = '10000';
      self._C['SERVERHELLO'] = 'HELLO';
      self._C['SERVERLOG']   = '/var/log/mikromakro.log';
      self._C['MYSQLHOST']   = '127.0.0.1';
      self._C['MYSQLPORT']   = '3306';
      self._C['MYSQLDB']     = 'mikromakro';
      self._C['MYSQLUSER']   = 'mikromakro';
      self._C['MYSQLPASS']   = 'mikromakro';
    
class _C_Config(_Config) :

    def __init__(self) :
      self._C['SERVERIP']    = '127.0.0.1';
      self._C['SERVERPORT']  = '10000';
      self._C['SERVERHELLO'] = 'HELLO';
      self._C['_CLIENTIP']    = '127.0.0.1';
      self._C['_CLIENTPORT']  = '10000';
      self._C['_CLIENTLOG']   = '/var/log/mikromakro.log';

_Client = _C_Config()

Inhalt = _Client.get("SERVERIP")
if (Inhalt) :
    print(Inhalt + "\n")
else :
    print("Fehler: " + str(_Client.Err) + " - " + _Client.ErrMsg + "\n")

Inhalt = _Client.get("Phantasie")
if (Inhalt) :
    print(Inhalt + "\n")
else :
    print("Fehler: " + str(_Client.Err) + " - " + _Client.ErrMsg + "\n")



print ("ALLES OK\n\n")