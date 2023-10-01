<?php

use Tak\Tron\API;

$tron = new API();

$privatekey1 = 'd2fd310eff9fc9ac6b4b65ad042bcc9a592847e7d9a21c66c4e8dd57d1e60f3d';

$phrase = $tron->getPhraseFromPrivateKey($privatekey1);

$privatekey2 = $tron->getPrivateKeyFromPhrase($phrase);

var_dump($privatekey1 === $privatekey2);

print $phrase;
