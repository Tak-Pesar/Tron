<?php

declare(strict_types = 1);

namespace Tak\Tron;

use CurlHandle;

final class Requests {
	private CurlHandle $curl;

	public function __construct(public string $url){
		$this->curl = curl_init();
		curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);
	}
	public function request(string $method,string $path,array $datas = array(),array $headers = array()) : mixed {
		switch($method){
			case 'POST':
				curl_setopt($this->curl,CURLOPT_URL,(filter_var($path,FILTER_VALIDATE_URL) ? $path : $this->url.'/'.$path));
				curl_setopt($this->curl,CURLOPT_POSTFIELDS,json_encode($datas));
			break;
			case 'GET':
				curl_setopt($this->curl,CURLOPT_URL,(filter_var($path,FILTER_VALIDATE_URL) ? $path : $this->url.'/'.$path.'?'.http_build_query($datas)));
				curl_setopt($this->curl,CURLOPT_CUSTOMREQUEST,$method);
			break;
			default:
				error_log('The request method is inappropriate for the URL !');
			break;
		}
		curl_setopt($this->curl,CURLOPT_HTTPHEADER,$headers);
		$result = curl_exec($this->curl);
		$error = curl_error($this->curl);
		return is_bool($result) ? (object) array('connection_error'=>$error) : json_decode($result);
	}
	public function __destruct(){
		curl_close($this->curl);
	}
	public function __clone() : void {
		$this->curl = curl_init();
		curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);
	}
}

?>