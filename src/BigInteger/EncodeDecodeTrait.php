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
     * @return int
     */
    public function toInt(): int
    {
        if ($this->cmp("18446744073709551615") > 0) {
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
