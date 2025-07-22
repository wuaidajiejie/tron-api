<?php
include_once '../vendor/autoload.php';

$fullNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
$solidityNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
$eventServer = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');

try {
    $tron = new \Wuaidajiejie\TronAPI\Tron($fullNode, $solidityNode, $eventServer);
} catch (\Wuaidajiejie\TronAPI\Exception\TronException $e) {
    exit($e->getMessage());
}

//option 1
$tron->sendTransaction('to',0.1, 'hello');

//option 2
$tron->send('to',0.1);

//option 3
$tron->sendTrx('to',0.1);
