<?php

declare(strict_types = 1);

namespace Tak\Tron;

use Tak\Tron\Crypto\Keccak;

use Tak\Tron\Crypto\Base58;

use Tak\Tron\Crypto\Secp;

use Elliptic\EC;

use InvalidArgumentException;

use Exception;

final class API extends Tools {
	protected Requests $sender;
	private string $privatekey;
	private string $wallet;

	public function __construct(string $apiurl = 'https://api.trongrid.io',string $privatekey = null,string $wallet = null){
		$this->sender = new Requests($apiurl);
		if(is_null($privatekey) === false) $this->privatekey = $privatekey;
		if(is_null($wallet) === false) $this->wallet = $this->hex2address($wallet);
	}
	public function setPrivateKey(string $privatekey) : void {
		$this->privatekey = $privatekey;
	}
	public function setWallet(string $wallet) : void {
		$this->wallet = $this->hex2address($wallet);
	}
	public function createTransaction(string $to,float $amount,string $from = null,string $extradata = null,bool $sun = false) : object {
		$to = $this->address2hex($to);
		if(is_null($from) and isset($this->wallet) === false) throw new InvalidArgumentException('The from argument is empty and no wallet is set by default !');
		$from = $this->address2hex(is_null($from) ? $this->wallet : $from);
		if($from === $to) throw new InvalidArgumentException('The from and to arguments cannot be the same !');
		$data = [
			'owner_address'=>$from,
			'to_address'=>$to,
			'amount'=>($sun ? $amount : $amount * 1e6)
		];
		if(is_null($extradata) === false) $data['extra_data'] = bin2hex($extradata);
		$transaction = (array) $this->sender->request('POST','wallet/createtransaction',$data);
		$signature = $this->signature($transaction);
		if(is_null($extradata) === false) $signature['raw_data']->data = bin2hex($extradata);
		$broadcast = (array) $this->broadcast($signature);
		return (object) array_merge($broadcast,$signature);
	}
	public function transferAsset(string $to,string $tokenid,float $amount,string $from = null,string $extradata = null,bool $sun = false) : object {
		$to = $this->address2hex($to);
		if(is_null($from) and isset($this->wallet) === false) throw new InvalidArgumentException('The from argument is empty and no wallet is set by default !');
		$from = $this->address2hex(is_null($from) ? $this->wallet : $from);
		if($from === $to) throw new InvalidArgumentException('The from and to arguments cannot be the same !');
		$data = [
			'owner_address'=>$from,
			'to_address'=>$to,
			'asset_name'=>bin2hex($tokenid),
			'amount'=>($sun ? $amount : $amount * 1e6)
		];
		if(is_null($extradata) === false) $data['extra_data'] = bin2hex($extradata);
		$transaction = (array) $this->sender->request('POST','wallet/transferasset',$data);
		$signature = $this->signature($transaction);
		if(is_null($extradata) === false) $signature['raw_data']->data = bin2hex($extradata);
		$broadcast = (array) $this->broadcast($signature);
		return (object) array_merge($broadcast,$signature);
	}
	public function generateAddress() : object {
		$ec = new EC('secp256k1');
		$key = $ec->genKeyPair();
		$priv = $ec->keyFromPrivate($key->priv);
		$privKey = $priv->getPrivate(enc : 'hex');
		$pubKey = $priv->getPublic(enc : 'hex');
		$address = $this->getAddressHexFromPublicKey($pubKey);
		$wallet = $this->hex2address($address);
		if(isset($this->privatekey) === false) $this->privatekey = $privKey;
		if(isset($this->wallet) === false) $this->wallet = $wallet;
		return (object) array('privatekey'=>$privKey,'publickey'=>$pubKey,'address'=>$address,'wallet'=>$wallet);
	}
	public function getAddressHexFromPublicKey(string $publickey) : string {
		$publickey = hex2bin($publickey);
		$publickey = substr($publickey,-64);
		$hash = Keccak::hash($publickey,256);
		return strval(41).substr($hash,24);
	}
	private function getWords() : array {
		if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'english.txt') === false) throw new Exception('english.txt file doesn\'t exists !');
		return explode(PHP_EOL,file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'english.txt'));
	}
	public function getPhraseFromPrivateKey(string $privatekey,int $base = 16) : string {
		$words = $this->getWords();
		srand($base);
		shuffle($words);
		$integer = gmp_init($privatekey,$base);
		$split = str_split(gmp_strval($integer),3);
		foreach($split as $number => $i):
			if(count($split) === ($number + 1)):
				if(str_starts_with($i,'00')): // strlen($i) === 2 || 3 && $i in range(0,9)
					$phrases []= $words[intval($i) + 2000 + (strlen($i) * 10)];
				elseif(str_starts_with($i,'0')): // strlen($i) === 1 || 2 || 3 && $i in range(0,99)
					$phrases []= $words[intval($i) + 1000 + (strlen($i) * 100)];
				else:
					$phrases []= $words[intval($i) + 0];
				endif;
			else:
				$phrases []= $words[intval($i)];
			endif;
		endforeach;
		return implode(chr(32),$phrases);
	}
	public function getPrivateKeyFromPhrase(string $phrase,int $base = 16) : string {
		$words = $this->getWords();
		srand($base);
		shuffle($words);
		$split = explode(chr(32),$phrase);
		foreach($split as $number => $i):
			$index = array_search($i,$words);
			if($index === false):
				throw new Exception('The word '.$i.' was not found !');
			else:
				if(count($split) === ($number + 1)):
					if($index >= 2000):
						$index -= 2000; // A number to recognize zeros
						$repeat = intdiv($index,10);
						$index -= ($repeat * 10); // strlen($i) === 2 || 3
						$last = str_pad(strval($index),$repeat,strval(0),STR_PAD_LEFT);
					elseif($index >= 1000):
						$index -= 1000; // A number to recognize zeros
						$repeat = intdiv($index,100); // strlen($i) === 1 || 2 || 3
						$index -= ($repeat * 100);
						$last = str_pad(strval($index),$repeat,strval(0),STR_PAD_LEFT);
					else:
						$last = strval($index);
					endif;
					$privatekey = gmp_strval(implode($integer).$last,$base);
					return strlen($privatekey) % 2 ? strval(0).$privatekey : $privatekey;
				else:
					$index = str_pad(strval($index),3,strval(0),STR_PAD_LEFT);
					$integer []= $index;
				endif;
			endif;
		endforeach;
	}
	public function getTransactionById(string $txID,bool $visible = true) : object {
		$data = [
			'value'=>$txID,
			'visible'=>$visible
		];
		$transaction = $this->sender->request('POST','wallet/gettransactionbyid',$data);
		return $transaction;
	}
	public function getTransactionInfoById(string $txID) : object {
		$data = [
			'value'=>$txID
		];
		$transaction = $this->sender->request('POST','wallet/gettransactioninfobyid',$data);
		return $transaction;
	}
	public function getTransactionInfoByBlockNum(int $num) : object {
		$data = [
			'num'=>$num
		];
		$transaction = $this->sender->request('POST','wallet/gettransactioninfobyblocknum',$data);
		return $transaction;
	}
	public function getTransactionsRelated(string $address = null,bool $confirmed = null,bool $to = false,bool $from = false,bool $searchinternal = true,int $limit = 20,string $order = 'desc',int $mintimestamp = null,int $maxtimestamp = null) : mixed {
		$data = array();
		if(is_null($confirmed) === false):
			$data[$confirmed ? 'only_confirmed' : 'only_unconfirmed'] = true;
		endif;
		$data['only_to'] = $to;
		$data['only_from'] = $from;
		$data['search_internal'] = $searchinternal;
		if($limit >= 1 and $limit <= 200):
			$data['limit'] = $limit;
		endif;
		$data['order_by'] = $order;
		if(is_null($mintimestamp) === false):
			$data['min_timestamp'] = date('Y-m-d\TH:i:s.v\Z',$mintimestamp);
		endif;
		if(is_null($maxtimestamp) === false):
			$data['max_timestamp'] = date('Y-m-d\TH:i:s.v\Z',$maxtimestamp);
		endif;
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$address = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$transactions = $this->sender->request('GET','v1/accounts/'.$address.'/transactions');
		return $transactions;
	}
	public function getTransactionsFromAddress(string $address = null,int $limit = 20) : mixed {
		return $this->getTransactionsRelated(address : $address,limit : $limit,from : true);
	}
	public function getTransactionsToAddress(string $address = null,int $limit = 20) : mixed {
		return $this->getTransactionsRelated(address : $address,limit : $limit,to : true);
	}
	public function createAccount(string $newaddress,string $address = null) : object {
		$newaddress = $this->address2hex($newaddress);
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$address = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$data = [
			'owner_address'=>$address,
			'account_address'=>$newaddress
		];
		$account = (array) $this->sender->request('POST','wallet/createaccount',$data);
		$signature = $this->signature($account);
		$broadcast = (array) $this->broadcast($signature);
		return (object) array_merge($broadcast,$signature);
	}
	public function getAccount(string $address = null) : object {
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$address = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$data = [
			'address'=>$address
		];
		$account = $this->sender->request('POST','walletsolidity/getaccount',$data);
		return $account;
	}
	public function getAccountNet(string $address = null) : object {
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$address = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$data = [
			'address'=>$address
		];
		$accountnet = $this->sender->request('POST','wallet/getaccountnet',$data);
		return $accountnet;
	}
	public function getAccountResource(string $address = null) : object {
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$address = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$data = [
			'address'=>$address
		];
		$accountresource = $this->sender->request('POST','wallet/getaccountresource',$data);
		return $accountresource;
	}
	public function getBalance(string $address = null,bool $sun = false) : float {
		$account = $this->getAccount($address);
		$balance = isset($account->balance) ? $account->balance : 0;
		return ($sun ? $balance : $balance / 1e6);
	}
	public function getAccountName(string $address = null,bool $hex = false) : mixed {
		$account = $this->getAccount($address);
		$accountname = isset($account->account_name) ? $account->account_name : null;
		return ($accountname ? ($hex ? $accountname : hex2bin($accountname)) : null);
	}
	public function freezeBalance(string $address = null,int $balance = 0,int $duration = 3,string $resource = 'ENERGY',string $receiver = null,bool $sun = false) : object {
		$data = array();
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$data['owner_address'] = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$data['frozen_balance'] = ($sun ? $balance : $balance * 1e6);
		$data['frozen_duration'] = $duration;
		if(in_array($resource,['BANDWIDTH','ENERGY'])):
			$data['resource'] = $resource;
		else:
			throw new InvalidArgumentException('The resource argument must be ENERGY or BANDWIDTH');
		endif;
		if(is_null($receiver) === false):
			$data['receiver_address'] = $this->address2hex($receiver);
		endif;
		$freezebalance = (array) $this->sender->request('POST','wallet/freezebalance',$data);
		$signature = $this->signature($freezebalance);
		$broadcast = (array) $this->broadcast($signature);
		return (object) array_merge($broadcast,$signature);
	}
	public function unfreezeBalance(string $address = null,string $resource = 'ENERGY',string $receiver = null) : object {
		$data = array();
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$data['owner_address'] = $this->address2hex(is_null($address) ? $this->wallet : $address);
		if(in_array($resource,['BANDWIDTH','ENERGY'])):
			$data['resource'] = $resource;
		else:
			throw new InvalidArgumentException('The resource argument must be ENERGY or BANDWIDTH');
		endif;
		if(is_null($receiver) === false):
			$data['receiver_address'] = $this->address2hex($receiver);
		endif;
		$unfreezebalance = (array) $this->sender->request('POST','wallet/unfreezebalance',$data);
		$signature = $this->signature($unfreezebalance);
		$broadcast = (array) $this->broadcast($signature);
		return (object) array_merge($broadcast,$signature);
	}
	public function getDelegatedResource(string $to,string $from = null) : object {
		$to = $this->address2hex($to);
		if(is_null($from) and isset($this->wallet) === false) throw new InvalidArgumentException('The from argument is empty and no wallet is set by default !');
		$from = $this->address2hex(is_null($from) ? $this->wallet : $from);
		$data = [
			'fromAddress'=>$from,
			'toAddress'=>$to
		];
		$delegatedresource = $this->sender->request('POST','wallet/getdelegatedresource',$data);
		return $delegatedresource;
	}
	public function getDelegatedResourceAccountIndex(string $address = null) : object {
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$address = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$data = [
			'value'=>$address
		];
		$delegatedresourceaccountindex = $this->sender->request('POST','wallet/getdelegatedresourceaccountindex',$data);
		return $delegatedresourceaccountindex;
	}
	public function changeAccountName(string $name,string $address = null) : object {
		if(is_null($address) and isset($this->wallet) === false) throw new InvalidArgumentException('The address argument is empty and no wallet is set by default !');
		$address = $this->address2hex(is_null($address) ? $this->wallet : $address);
		$data = [
			'account_name'=>bin2hex($name),
			'owner_address'=>$address
		];
		$account = (array) $this->sender->request('POST','wallet/updateaccount',$data);
		$signature = $this->signature($account);
		$broadcast = $this->broadcast($signature);
		return $broadcast;
	}
	public function getBlock(string $idornum,bool $detail = false) : object {
		$data = [
			'id_or_num'=>$id,
			'detail'=>$detail
		];
		$block = $this->sender->request('POST','wallet/getblock',$data);
		return $block;
	}
	public function getBlockByNum(int $num) : object {
		$data = [
			'num'=>$num
		];
		$block = $this->sender->request('POST','wallet/getblockbynum',$data);
		return $block;
	}
	public function getBlockById(int $id) : object {
		$data = [
			'value'=>$id
		];
		$block = $this->sender->request('POST','wallet/getblockbyid',$data);
		return $block;
	}
	public function signature(array $response) : array {
		if(isset($this->privatekey)):
			if(isset($response['Error'])):
				throw new Exception($response['Error']);
			else:
				if(isset($response['signature'])):
					throw new Exception('response is already signed !');
				elseif(isset($response['txID']) === false):
					throw new Exception('The response does not have txID key !');
				else:
					$signature = Secp::sign($response['txID'],$this->privatekey);
					$response['signature'] = array($signature);
				endif;
			endif;
		else:
			throw new Exception('private key is not set');
		endif;
		return $response;
	}
	public function broadcast(array $response) : object {
		if(isset($response['signature']) === false or is_array($response['signature']) === false) throw new InvalidArgumentException('response has not been signature !');
		$broadcast = $this->sender->request('POST','wallet/broadcasttransaction',$response);
		return $broadcast;
	}
	public function __call(string $method,array $arguments) : mixed {
		return match($method){
			'sendTrx' , 'sendTron' , 'send' , 'sendTransaction' => $this->createTransaction(...$arguments),
			'sendToken' , 'sendTokenTransaction' => $this->transferAsset(...$arguments),
			'getBandwidth' => $this->getAccountNet(...$arguments),
			'registerAccount' => $this->createAccount(...$arguments),
			'createAddress' => $this->generateAddress(...$arguments),
			default => throw new Exception('Call to undefined method '.self::class.'::'.$method.'()')
		};
	}
}

?>