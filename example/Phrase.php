<?php

use Tak\Tron\API;

$tron = new API();

$privatekey1 = 'f13f3ef7734edd1355tf6cf1d90c78520d07388dad500c30322690d483f2efd1';

$phrase = $tron->getPhraseFromPrivateKey($privatekey1);

print $phrase;

$privatekey2 = $tron->getPrivateKeyFromPhrase($phrase);

var_dump($privatekey1 === $privatekey2);
