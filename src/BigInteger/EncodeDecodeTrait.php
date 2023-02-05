<?php
declare(strict_types=1);

namespace Comely\Buffer\BigInteger;

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\Buffer;

/**
 * Trait EncodeDecodeTrait
 * @package Comely\Buffer\BigInteger
 */
trait EncodeDecodeTrait
{
    /**
     * @param string $encodedStr
     * @param \Comely\Buffer\BigInteger\BaseCharset $base
     * @return static
     */
    public static function fromCustomBase(string $encodedStr, BaseCharset $base): static
    {
        if (!$base->caseSensitive) {
            $encodedStr = strtolower($encodedStr);
        }

        $len = strlen($encodedStr);
        $value = gmp_init(0, 10);
        $multiplier = gmp_init(1, 10);

        for ($i = $len - 1; $i >= 0; $i--) { // Start in reverse order
            $pos = gmp_mul($multiplier, gmp_init(strpos($base->charset, $encodedStr[$i]), 10));
            $value = gmp_add($value, $pos);
            $multiplier = gmp_mul($multiplier, $base->len);
        }

        return new static($value);
    }

    /**
     * @param \Comely\Buffer\BigInteger\BaseCharset $base
     * @return string
     */
    public function toCustomBase(BaseCharset $base): string
    {
        if (!$this->isUnsigned()) {
            throw new \InvalidArgumentException('Cannot convert a signed BigInteger to custom base');
        }

        $num = $this->int;
        $encoded = "";
        while (true) {
            if (gmp_cmp($num, $base->len) < 0) {
                break;
            }

            $pos = gmp_intval(gmp_mod($num, $base->len));
            $num = gmp_div($num, $base->len);
            $encoded = $base->charset[$pos] . $encoded;
        }

        if (gmp_cmp($num, 0) >= 0) {
            $encoded = $base->charset[gmp_intval($num)] . $encoded;
        }

        return $encoded;
    }

    /**
     * @param string $hex
     * @return static
     */
    public static function fromBase16(string $hex): static
    {
        // Validate string as Hexadecimal
        if (!preg_match('/^(0x)?[a-f0-9]+$/i', $hex)) {
            throw new \InvalidArgumentException('Cannot instantiate BigNumber; expected Hexadecimal string');
        }

        // Remove the "0x" prefix
        if (str_starts_with($hex, "0x")) {
            $hex = substr($hex, 2);
        }

        // Evens-out odd number of hexits
        if (strlen($hex) % 2 !== 0) {
            $hex = "0" . $hex;
        }

        return new static(gmp_init($hex, 16));
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $buffer
     * @return static
     */
    public static function fromBuffer(AbstractByteArray $buffer): static
    {
        return new static(gmp_init($buffer->toBase16(), 16));
    }

    /**
     * @return string
     */
    public function toBase16(): string
    {
        return gmp_strval($this->int, 16);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return gmp_strval($this->int, 10);
    }

    /**
     * @return int
     */
    public function toInt(): int
    {
        if ($this->cmp(PHP_INT_MAX) > 0) {
            throw new \OverflowException('Cannot convert BigInteger to int; Value too long');
        }

        return gmp_intval($this->int);
    }

    /**
     * @return \Comely\Buffer\Buffer
     */
    public function toBuffer(): Buffer
    {
        return Buffer::fromBase16($this->toBase16());
    }
}
