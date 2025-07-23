<?php

namespace Wuaidajiejie\TronAPI;

use InvalidArgumentException;
use Wuaidajiejie\TronAPI\Exception\TronException;
use Wuaidajiejie\TronAPI\Support\Utils;
use Wuaidajiejie\TronAPI\Tron;
class TRC20
{
    protected \Wuaidajiejie\TronAPI\Tron $tron;
    /**
     * @var mixed
     */
    protected $contractAddress;
    /**
     * @var mixed
     */
    protected $decimals;

    public function __construct(Tron $tron, array $config)
    {
        $this->tron = $tron;

        $this->contractAddress = $config['contract_address'];
        $this->decimals = $config['decimals'];
    }

    /**
     * @throws TronException
     */
    public function balance(string $address): string
    {
        $address = $this->tron->address2HexString($address);
        $format = Utils::toAddressFormat($address);
        $body = $this->tron->getManager()->request('/wallet/triggersmartcontract', [
            'contract_address' => $this->tron->address2HexString($this->contractAddress),
            'function_selector' => 'balanceOf(address)',
            'parameter' => $format,
            'owner_address' => $address,
        ]);
        if (isset($body['result']['code'])) {
            throw new TronException(hex2bin($body['result']['message']));
        }
        try {
            $balance = Utils::toDisplayAmount(hexdec($body['constant_result'][0]), $this->decimals);
        } catch (InvalidArgumentException $e) {
            throw new TronException($e->getMessage());
        }
        return $balance;
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
        $toFormat = Utils::toAddressFormat($this->tron->address2HexString($to));
        try {
            $amount = Utils::toMinUnitByDecimals($amount, $this->decimals);
        } catch (InvalidArgumentException $e) {
            throw new TronException($e->getMessage());
        }
        $numberFormat = Utils::toIntegerFormat($amount);
        $body = $this->tron->getManager()->request('/wallet/triggersmartcontract', [
            'contract_address' => $this->tron->address2HexString($this->contractAddress),
            'function_selector' => 'transfer(address,uint256)',
            'parameter' => "{$toFormat}{$numberFormat}",
            'fee_limit' => 100000000,
            'call_value' => 0,
            'owner_address' => $this->tron->address2HexString($from),
        ]);

        if (isset($body['result']['code'])) {
            throw new TronException(hex2bin($body['result']['message']));
        }

        try {
            $tradeobj = $this->tron->signTransaction($body['transaction']);
            $response = $this->tron->sendRawTransaction($tradeobj);
        } catch (TronException $e) {
            throw new TronException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result']) {
            return new Transaction(
                $body['transaction']['txID'],
                $body['transaction']['raw_data'],
                'PACKING'
            );
        } else {
            throw new TronException(hex2bin($response['message']));
        }
    }
}