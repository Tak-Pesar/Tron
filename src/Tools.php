<?php

declare(strict_types = 1);

namespace Tak\Tron;

use Tak\Tron\Crypto\Base58;

abstract class Tools {
	public function address2hex(string $address) : string {
		if(strlen($address) == 42 and str_starts_with($address,strval(41))):
			return $address;
		else:
			return Base58::decodeAddress($address);
		endif;
	}
	public function hex2address(string $address) : string {
		if(ctype_xdigit($address)):
			return Base58::encodeAddress($address);
		else:
			return $address;
		endif;
	}
	public function validation(string $address) : bool {
		if(preg_match('/^T[A-HJ-NP-Za-km-z1-9]{33}$/',$address)):
			$hex = $this->address2hex($address);
			$wallet = $this->hex2address($hex);
			return $wallet === $address;
		else:
			return false;
		endif;
	}
}

?>