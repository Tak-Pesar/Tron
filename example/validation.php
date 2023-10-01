<?php

use Tak\Tron\API;

$tron = new API();

$wallet = 'TJZfm2PSQ38WNwzPqSBpTbVAynZpMEmfKR';

var_dump($tron->validation($wallet));
