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
        if (Utils::isAddress($address)) {
            $address = strtolower($address);

            if (Utils::isZeroPrefixed($address)) {
                $address = Utils::stripZero($address);
            }
        }
        $format = implode('', array_fill(0, 64 - strlen($address), 0)) . $address;
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

}