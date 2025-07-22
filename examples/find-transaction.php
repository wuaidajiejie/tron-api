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

$detail = $tron->getTransaction('TxId');
var_dump($detail);