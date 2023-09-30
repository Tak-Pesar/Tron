<?php

use Tak\Tron\API;
// f

/*
$tron = new API(privatekey : 'f13f3ef7734edd1355tf6cf1d90c78520d07388dad500c30322690d483f2efd1',wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
or
$tron = new API();
$tron->setWallet('TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
$tron->setPrivateKey('f13f3ef7734edd1355tf6cf1d90c78520d07388dad500c30322690d483f2efd1');
 */

$tron = new API(privatekey : 'f13f3ef7734edd1355tf6cf1d90c78520d07388dad500c30322690d483f2efd1',wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');

try {
	$send = $tron->createTransaction(to : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR',amount : 10.5,from : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
	if(isset($transaction->result) or $transaction->code === 'SUCCESS'):
		print 'SUCCESS !';
		print PHP_EOL;
	endif;
} catch(\Throwable $error){
	exit($error->getMessage());
}
/*
$send = $tron->createTransaction(to : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR',amount : 10.5,from : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
or
$send = $tron->sendTrx(to : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR',amount : 10.5);
or
$send = $tron->sendTron(to : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR',amount : 10500000,sun : true);
 */

print 'transaction : '.var_export($send,true);
