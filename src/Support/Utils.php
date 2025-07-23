<?php
namespace Wuaidajiejie\TronAPI\Support;

use Exception;
use InvalidArgumentException;
use kornrunner\Keccak;
use phpseclib\Math\BigInteger;

class Utils
{
    const SHA3_NULL_HASH = 'c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470';

    /**
     * Link verification
     *
     * @param $url
     * @return bool
     */
    public static function isValidUrl($url) :bool {
        return (bool)parse_url($url);
    }

    /**
     * Check whether the passed parameter is an array
     *
     * @param $array
     * @return bool
     */
    public static function isArray($array) : bool {
        return is_array($array);
    }

    /**
     * isZeroPrefixed
     *
     * @param string
     * @return bool
     */
    public static function isZeroPrefixed($value): bool
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('The value to isZeroPrefixed function must be string.');
        }
        return (strpos($value, '0x') === 0);
    }

    /**
     * stripZero
     *
     * @param string $value
     * @return string
     */
    public static function stripZero($value): string
    {
        if (self::isZeroPrefixed($value)) {
            $count = 1;
            return str_replace('0x', '', $value, $count);
        }
        return $value;
    }

    /**
     * isNegative
     *
     * @param string
     * @return bool
     */
    public static function isNegative($value): bool
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('The value to isNegative function must be string.');
        }
        return (strpos($value, '-') === 0);
    }

    /**
     * Check if the string is a 16th notation
     *
     * @param $str
     * @return bool
     */
    public static function isHex($str) : bool {
        return is_string($str) and ctype_xdigit($str);
    }

    /**
     * hexToBin
     *
     * @param string
     * @return string
     */
    public static function hexToBin($value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('The value to hexToBin function must be string.');
        }
        if (self::isZeroPrefixed($value)) {
            $count = 1;
            $value = str_replace('0x', '', $value, $count);
        }
        return pack('H*', $value);
    }

    /**
     * @param $address
     * @return bool
     * @throws Exception
     */
    public static function validate($address): bool
    {
        $decoded = Base58::decode($address);

        $d1 = hash("sha256", substr($decoded,0,21), true);
        $d2 = hash("sha256", $d1, true);

        if(substr_compare($decoded, $d2, 21, 4)){
            throw new \Exception("bad digest");
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public static function decodeBase58($input): string
    {
        $alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";

        $out = array_fill(0, 25, 0);
        for($i=0;$i<strlen($input);$i++){
            if(($p=strpos($alphabet, $input[$i]))===false){
                throw new Exception("invalid character found");
            }
            $c = $p;
            for ($j = 25; $j--; ) {
                $c += (int)(58 * $out[$j]);
                $out[$j] = (int)($c % 256);
                $c /= 256;
                $c = (int)$c;
            }
            if($c != 0){
                throw new Exception("address too long");
            }
        }

        $result = "";
        foreach($out as $val){
            $result .= chr($val);
        }

        return $result;
    }

    /**
     *
     * @throws Exception
     */
    public static function pubKeyToAddress($pubkey): string
    {
        return '41'. substr(Keccak::hash(substr(hex2bin($pubkey), 1), 256), 24);
    }

    /**
     * Test if a string is prefixed with "0x".
     *
     * @param string $str
     *   String to test prefix.
     *
     * @return bool
     *   TRUE if string has "0x" prefix or FALSE.
     */
    public static function hasHexPrefix($str): bool
    {
        return substr($str, 0, 2) === '0x';
    }

    /**
     * Remove Hex Prefix "0x".
     *
     * @param string $str
     * @return string
     */
    public static function removeHexPrefix(string $str): string
    {
        if (!self::hasHexPrefix($str)) {
            return $str;
        }
        return substr($str, 2);
    }

    /**
     * isAddressChecksum
     *
     * @param string $value
     * @return bool
     */
    public static function isAddressChecksum(string $value): bool
    {
        $value = self::stripZero($value);
        $hash = self::stripZero(self::sha3(mb_strtolower($value)));

        for ($i = 0; $i < 40; $i++) {
            if (
                (intval($hash[$i], 16) > 7 && mb_strtoupper($value[$i]) !== $value[$i]) ||
                (intval($hash[$i], 16) <= 7 && mb_strtolower($value[$i]) !== $value[$i])
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * sha3
     * keccak256
     *
     * @param string $value
     * @return string
     */
    public static function sha3(string $value): ?string
    {
        if (strpos($value, '0x') === 0) {
            $value = self::hexToBin($value);
        }
        try {
            $hash = Keccak::hash($value, 256);
        } catch (Exception $e) {
            return null;
        }

        if ($hash === self::SHA3_NULL_HASH) {
            return null;
        }
        return $hash;
    }

    /**
     * isAddress
     *
     * @param string $value
     * @return bool
     */
    public static function isAddress(string $value): bool
    {
        if (preg_match('/^(0x|0X)?[a-f0-9A-F]{40}$/', $value) !== 1) {
            return false;
        } elseif (preg_match('/^(0x|0X)?[a-f0-9]{40}$/', $value) === 1 || preg_match('/^(0x|0X)?[A-F0-9]{40}$/', $value) === 1) {
            return true;
        }
        return self::isAddressChecksum($value);
    }

    /**
     * toBn
     * Change number or number string to BigInteger.
     *
     * @param BigInteger|string|int $number
     * @return array|BigInteger
     */
    public static function toBn($number)
    {
        if ($number instanceof BigInteger) {
            $bn = $number;
        } elseif (is_int($number)) {
            $bn = new BigInteger($number);
        } elseif (is_numeric($number)) {
            $number = (string) $number;

            if (self::isNegative($number)) {
                $count = 1;
                $number = str_replace('-', '', $number, $count);
                $negative1 = new BigInteger(-1);
            }
            if (strpos($number, '.') > 0) {
                $comps = explode('.', $number);

                if (count($comps) > 2) {
                    throw new InvalidArgumentException('toBn number must be a valid number.');
                }
                $whole = $comps[0];
                $fraction = $comps[1];

                return [
                    new BigInteger($whole),
                    new BigInteger($fraction),
                    strlen($comps[1]),
                    $negative1 ?? false
                ];
            } else {
                $bn = new BigInteger($number);
            }
            if (isset($negative1)) {
                $bn = $bn->multiply($negative1);
            }
        } elseif (is_string($number)) {
            $number = mb_strtolower($number);

            if (self::isNegative($number)) {
                $count = 1;
                $number = str_replace('-', '', $number, $count);
                $negative1 = new BigInteger(-1);
            }
            if (self::isZeroPrefixed($number) || preg_match('/[a-f]+/', $number) === 1) {
                $number = self::stripZero($number);
                $bn = new BigInteger($number, 16);
            } elseif (empty($number)) {
                $bn = new BigInteger(0);
            } else {
                throw new InvalidArgumentException('toBn number must be valid hex string.');
            }
            if (isset($negative1)) {
                $bn = $bn->multiply($negative1);
            }
        } else {
            throw new InvalidArgumentException('toBn number must be BigInteger, string or int.');
        }
        return $bn;
    }

    /**
     * 根据精度展示资产
     * @param $number
     * @param int $decimals
     * @return string
     */
    public static function toDisplayAmount($number, int $decimals): string
    {
        $number = number_format($number,0,'.','');//格式化
        $bn = self::toBn($number);
        $bnt = self::toBn(pow(10, $decimals));

        return self::divideDisplay($bn->divide($bnt), $decimals);
    }

    public static function divideDisplay(array $divResult, int $decimals): string
    {
        list($bnq, $bnr) = $divResult;
        $ret = "$bnq->value";
        if ($bnr->value > 0) {
            $ret .= '.' . rtrim(sprintf("%0{$decimals}d", $bnr->value), '0');
        }

        return $ret;
    }

    public static function toMinUnitByDecimals($number, int $decimals): BigInteger
    {
        $number = self::safeNumber($number, $decimals); // 先安全格式化
        if (strpos($number, '.') !== false) {
            list($intPart, $decPart) = explode('.', $number);
            $decPart = str_pad(substr($decPart, 0, $decimals), $decimals, '0');
            $number = $intPart . $decPart;
        } else {
            $number .= str_repeat('0', $decimals);
        }
        return new BigInteger($number);
    }


    /**
     * 将数字安全转换为十进制字符串，避免科学计数法
     *
     * @param int|float|string $number
     * @param int $scale 保留的小数位数（默认18位，适合区块链精度）
     * @return string
     */
    public static function safeNumber($number, int $scale = 18): string
    {
        if ($number instanceof \phpseclib\Math\BigInteger) {
            return $number->toString();
        }

        // 如果是int，直接转string
        if (is_int($number)) {
            return (string)$number;
        }

        // 如果是float，强制转换为string
        if (is_float($number)) {
            // 使用 number_format 保留精度
            return number_format($number, $scale, '.', '');
        }

        // 如果是string
        if (is_string($number)) {
            $number = trim($number);

            // 检查是否是科学计数法
            if (stripos($number, 'e') !== false) {
                $floatVal = (float)$number;
                return number_format($floatVal, $scale, '.', '');
            }

            return $number;
        }

        throw new InvalidArgumentException('safeNumber only supports int, float, string, or BigInteger.');
    }


    /**
     * 地址签名
     * @param $address
     * @return string
     */
    public static function toAddressFormat($address): string
    {
        if (Utils::isAddress($address)) {
            $address = strtolower($address);

            if (Utils::isZeroPrefixed($address)) {
                $address = Utils::stripZero($address);
            }
        }
        return implode('', array_fill(0, 64 - strlen($address), 0)) . $address;
    }

    /**
     * 数字签名
     * @param $value
     * @param int $digit
     * @return string
     */
    public static function toIntegerFormat($value, int $digit = 64): string
    {
        $bn = Utils::toBn($value);
        $bnHex = $bn->toHex(true);
        $padded = mb_substr($bnHex, 0, 1);

        if ($padded !== 'f') {
            $padded = '0';
        }
        return implode('', array_fill(0, $digit - mb_strlen($bnHex), $padded)) . $bnHex;
    }
}
