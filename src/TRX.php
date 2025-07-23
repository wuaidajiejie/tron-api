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
    public function balance(string $address = null, bool $fromTron = false): float
    {
        $account = $this->tron->getAccount($address);

        if(!array_key_exists('balance', $account)) {
            return 0;
        }
        return ($fromTron == true ?
            $this->tron->fromTron($account['balance']) :
            $account['balance']);
    }
}