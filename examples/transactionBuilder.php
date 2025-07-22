<?php
$fullNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
$solidityNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
$eventServer = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');

try {
    $tron = new \Wuaidajiejie\TronAPI\Tron($fullNode, $solidityNode, $eventServer);
} catch (\Wuaidajiejie\TronAPI\Exception\TronException $e) {
    exit($e->getMessage());
}


try {
    $transaction = $tron->getTransactionBuilder()->sendTrx('to', 2,'fromAddress');
    $signedTransaction = $tron->signTransaction($transaction);
    $response = $tron->sendRawTransaction($signedTransaction);
} catch (\Wuaidajiejie\TronAPI\Exception\TronException $e) {
    die($e->getMessage());
}
