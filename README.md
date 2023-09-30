# Tak-Pesar/Tron
Tron ( TRX ) crypto currency API !
This library facilitates connectivity to the Tron network, enabling you to generate a personalized wallet address. This is achieved through utilization of the Tron API.

## Installation
```bash
composer require takpesar/tron
```

## Requirements

This package requires PHP 8 or later. GMP and curl extensions require this library

## Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Tak\Tron\API;

$tron = new API();

$address = $tron->generateAddress();

var_dump($address);

$tron = new API(privatekey : $address->privatekey,wallet : $address->wallet);

$balance = $tron->getBalance();
print 'your balance : '.$balance;
print PHP_EOL;

$send = $tron->sendTron();
print 'transaction : '.var_export($send,true);
print PHP_EOL;
```
