<?php

use Tak\Tron\API;

$tron = new API();

$address = $tron->generateAddress();

print 'your wallet address base58 : '.$address->wallet;
print PHP_EOL;
print 'your wallet address hex : '.$address->address;
print PHP_EOL;
print 'your public key : '.$address->publickey;
print PHP_EOL;
print 'your private key : '.$address->privatekey;
