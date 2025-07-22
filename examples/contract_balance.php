<?php
$fullNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
$solidityNode = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');
$eventServer = new \Wuaidajiejie\TronAPI\Provider\HttpProvider('https://api.trongrid.io');

try {
    $tron = new \Wuaidajiejie\TronAPI\Tron($fullNode, $solidityNode, $eventServer);
} catch (\Wuaidajiejie\TronAPI\Exception\TronException $e) {
    exit($e->getMessage());
}


$balance=$tron->getTransactionBuilder()->contractbalance($tron->getAddress);
foreach($balance as $key =>$item)
{
	echo $item["name"]. " (".$item["symbol"].") => " . $item["balance"] . "\n";
}

