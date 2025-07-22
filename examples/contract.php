<?php

include_once '../vendor/autoload.php';

use Wuaidajiejie\TronAPI\Tron;


try {
    $fullNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
    $solidityNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
    $eventServer = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
} catch (\Wuaidajiejie\TronAPI\Exception\TronException $e) {
    echo $e->getMessage();
}


try {
    $tron = new Tron($fullNode, $solidityNode, $eventServer, null, true);
    $contract = $tron->contract('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t');  // Tether USDT https://tronscan.org/#/token20/TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t

    // Data
    echo $contract->name();
    echo $contract->symbol();
    echo $contract->balanceOf();
    echo $contract->totalSupply();
    //echo  $contract->transfer('to', 'amount', 'from');


} catch (\Wuaidajiejie\TronAPI\Exception\TronException $e) {
    echo $e->getMessage();
}