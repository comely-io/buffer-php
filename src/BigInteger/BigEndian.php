<?php
declare(strict_types=1);

namespace Comely\Buffer\BigInteger;

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\BigInteger;

/**
 * Class BigEndian
 * @package Comely\Buffer\BigInteger
 */
class BigEndian
{
    /**
     * @param int $n
     * @return string
     */
    public static function PackUInt8(int $n): string
    {
        return Math::PackUInt8($n);
    }

    /**
     * @param string $bn
     * @return int
     */
    public static function UnpackUInt8(string $bn): int
    {
        return Math::UnpackUInt8($bn);
    }

    /**
     * @param int $n
     * @return string
     */
    public static function PackUInt16(int $n): string
    {
        Math::CheckUInt32($n, 2);
        return pack("n", $n);
    }

    /**
     * @param string $bn
     * @return int
     */
    public static function UnpackUInt16(string $bn): int
    {
        if (strlen($bn) !== 2) {
            throw new \OverflowException('Input exceeds 2 bytes');
        }

        return unpack("n", $bn)[1];
    }

    /**
     * @param int $n
     * @return string
     */
    public static function PackUInt32(int $n): string
    {
        Math::CheckUInt32($n, 4);
        return pack("N", $n);
    }

    /**
     * @param string $bn
     * @return int
     */
    public static function UnpackUInt32(string $bn): int
    {
        if (strlen($bn) !== 4) {
            throw new \OverflowException('Input exceeds 4 bytes');
        }

        return unpack("N", $bn)[1];
    }

    /**
     * @param int|string|\GMP|\Comely\Buffer\AbstractByteArray|\Comely\Buffer\BigInteger $n
     * @return string
     */
    public static function PackUInt64(int|string|\GMP|AbstractByteArray|BigInteger $n): string
    {
        $n = Math::CheckUInt64($n);
        $packed = str_pad(hex2bin($n->toBase16()), 8, "\0", STR_PAD_LEFT);
        if (Math::gmpEndianness() === Math::GMP_LITTLE_ENDIAN) {
            $packed = Math::SwapEndianness($packed, false);
        }

        return $packed;
    }

    /**
     * @param string $bn
     * @param bool $checkEndianness
     * @return \Comely\Buffer\BigInteger
     */
    public static function UnpackUInt64(string $bn, bool $checkEndianness = true): BigInteger
    {
        if (strlen($bn) !== 8) {
            throw new \OverflowException('Input exceeds 8 bytes');
        }

        if ($checkEndianness && Math::gmpEndianness() === Math::GMP_LITTLE_ENDIAN) {
            $bn = Math::SwapEndianness($bn, false);
        }

        return new BigInteger(gmp_init(bin2hex($bn), 16));
    }
}
