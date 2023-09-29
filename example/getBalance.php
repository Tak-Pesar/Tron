<?php

use Tak\Tron\API;

/*
$tron = new API(wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
or
$tron = new API();
$tron->setWallet('TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
 */

$tron = new API(wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');

$balance = $tron->getBalance();

/*
$balance = $tron->getBalance();
or
$balance = $tron->getBalance('TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
or
$balance = $tron->getBalance(sun : true); // balance * 10^6 ( 1e6 )
 */

print 'your balance  : '.$balance;
