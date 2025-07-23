<?php

namespace Wuaidajiejie\TronAPI;

use Wuaidajiejie\TronAPI\Exception\TronException;
use Wuaidajiejie\TronAPI\Tron;
class TRX
{
    protected \Wuaidajiejie\TronAPI\Tron $tron;

    public function __construct(Tron $tron)
    {
        $this->tron = $tron;
    }

    /**
     * Getting a balance
     *
     * @param string $address
     * @param bool $fromTron
     * @return float
     * @throws TronException
     */
    public function balance(string $address, bool $fromTron = false): float
    {
        $account = $this->tron->getAccount($address);

        if(!array_key_exists('balance', $account)) {
            return 0;
        }
        return ($fromTron ?
            $this->tron->fromTron($account['balance']) :
            $account['balance']);
    }

    /**
     * @throws TronException
     */
    public function transfer(string $from, string $to, float $amount, string $privateKey = ''): Transaction
    {
        $this->tron->setAddress($from);

        if(isset($privateKey)){
            $this->tron->setPrivateKey($privateKey);
        }

        try {
            $transaction = $this->tron->getTransactionBuilder()->sendTrx($to, $amount, $from);
            $signedTransaction = $this->tron->signTransaction($transaction);
            $response = $this->tron->sendRawTransaction($signedTransaction);
        } catch (TronException $e) {
            throw new TronException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result']) {
            return new Transaction(
                $transaction['txID'],
                $transaction['raw_data'],
                'PACKING'
            );
        } else {
            throw new TronException(hex2bin($response['message']));
        }
    }
}