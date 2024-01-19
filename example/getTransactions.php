<?php

use Tak\Tron\API;

$tron = new API();

/*
If the address parameter is not set, the address of the wallet added to the object will be used by default.
$tron = new API(wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
or
$tron = new API();
$tron->setWallet('TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');

@params string $address = null bool $confirmed = null bool $to = false bool $from = false bool $searchinternal = true int $limit = 20 string $order = 'block_timestamp,desc' int $mintimestamp = null int $maxtimestamp = null
@return mixed
*/

$transactions = $tron->getTransactionsRelated(address : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR',confirmed : true,limit : 5);

var_dump($transactions);

/*
You can use a loop to continue the list of transactions and get all of them
*/

foreach($transactions->iterator as $page => $transaction):
	print 'Page '.strval($page + 1);
	print PHP_EOL;
	var_dump($transaction);
endforeach;
