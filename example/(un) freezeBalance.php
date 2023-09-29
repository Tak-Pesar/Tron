<?php

use Tak\Tron\API;

/*
$tron = new API(privatekey : 'f13f3ef7734edd1355tf6cf1d90c78520d07388dad500c30322690d483f2efd1',wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
or
$tron = new API();
$tron->setWallet('TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
$tron->setPrivateKey('f13f3ef7734edd1355tf6cf1d90c78520d07388dad500c30322690d483f2efd1');
 */

$tron = new API(privatekey : 'f13f3ef7734edd1355tf6cf1d90c78520d07388dad500c30322690d483f2efd1',wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');

$freeze = $tron->freezeBalance(balance : 100);

$unfreeze = $tron->unfreezeBalance(balance : 100);

print 'freeze balance : '.var_export($freeze,true);
print PHP_EOL;
print 'unfreeze balance : '.var_export($unfreeze,true);
