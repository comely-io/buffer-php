<?php
declare(strict_types=1);

namespace Comely\Buffer\BigInteger;

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\BigInteger;

/**
 * Class Math
 * @package Comely\Buffer\BigInteger
 */
class Math
{
    /** @var int */
    public const GMP_LITTLE_ENDIAN = 0x01;
    /** @var int */
    public const GMP_BIG_ENDIAN = 0x02;

    /**
     * @param string $inp
     * @param bool $checkHex
     * @return string
     */
    public static function SwapEndianness(string $inp, bool $checkHex = true): string
    {
        $isHex = $checkHex && preg_match('/^[a-f0-9]+$/i', $inp);
        return implode("", array_reverse(str_split($inp, $isHex ? 2 : 1)));
    }

    /**
     * @return int
     */
    public static function gmpEndianness(): int
    {
        return gmp_strval(gmp_init(65534, 10), 16) === "feff" ? self::GMP_LITTLE_ENDIAN : self::GMP_BIG_ENDIAN;
    }

    /**
     * @return bool
     */
    public static function isLittleEndian(): bool
    {
        return pack("S", 1) === pack("v", 1);
    }

    /**
     * @param int $n
     * @return string
     */
    public static function PackUInt8(int $n): string
    {
        static::CheckUInt32($n, 1);
        return chr($n);
    }

    /**
     * @param string $bn
     * @return int
     */
    public static function UnpackUInt8(string $bn): int
    {
        return ord($bn);
    }

    /**
     * @param int $n
     * @param int $byteLen
     * @return void
     */
    public static function CheckUInt32(int $n, int $byteLen): void
    {
        $max = match ($byteLen) {
            1 => 0xff,
            2 => 0xffff,
            4 => 0xffffffff,
            default => throw new \OutOfBoundsException("Invalid integer byte len")
        };

        if ($n < 0) {
            throw new \UnderflowException("Expected unsigned integer; got a signed/negative value");
        }

        if ($n > $max) {
            throw new \OverflowException("Argument integer cannot be packed in $byteLen bytes");
        }
    }

    /**
     * @param int|string|\GMP|\Comely\Buffer\AbstractByteArray|\Comely\Buffer\BigInteger $n
     * @return \Comely\Buffer\BigInteger
     */
    public static function CheckUInt64(int|string|\GMP|AbstractByteArray|BigInteger $n): BigInteger
    {
        if (!$n instanceof BigInteger) {
            $n = new BigInteger($n);
        }

        if ($n->isSigned()) {
            throw new \UnderflowException("Expected unsigned integer; got a signed/negative value");
        }

        if ($n->greaterThan("18446744073709551615")) {
            throw new \OverflowException("Value cannot exceed 18,446,744,073,709,551,615 to be packed in 8 bytes");
        }

        return $n;
    }
}
