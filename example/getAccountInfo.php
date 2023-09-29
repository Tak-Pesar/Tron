<?php

use Tak\Tron\API;

/*
$tron = new API(wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
or
$tron = new API();
$tron->setWallet('TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
 */

$tron = new API(wallet : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');

$account = $tron->getAccount();

/*
$account = $tron->getAccount();
or
$account = $tron->getAccount('TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR');
 */

print 'your account info : '.var_export($account,true);
