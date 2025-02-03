# Tak-Pesar/Tron
Tron ( TRX ) crypto currency API !
This library facilitates connectivity to the Tron network, enabling you to generate a personalized wallet address. This is achieved through utilization of the Tron API.

## Installation
```bash
composer require takpesar/tron
```

## Requirements

This package requires PHP 8 or later. BCMath and Curl extensions require this package

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

try {
	$send = $tron->sendTron(to : 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR',amount : 10.5);
	print 'transaction : '.var_export($send,true);
} catch(Throwable $e){
	print 'transaction error : '.$e->getMessage();
}
```

## Use Phar _( Faster and easier ! )_

```php
<?php

copy('https://tron.phar.fun/tron.phar','trx.phar');

require_once './trx.phar';

use Tak\Tron\API;

$tron = new API();

$address = $tron->generateAddress();

var_dump($address);
```

> **Note**
> Please see [`examples`](./example) for more examples

## Issues

Should you encounter any issues, please do not hesitate to reach out to us via email at [`loser.man.2007@gmail.com`](mailto:loser.man.2007@gmail.com).

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
