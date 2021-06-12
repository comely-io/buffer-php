<?php
declare(strict_types=1);

namespace Comely\Buffer;

/**
 * Class Buffer
 * @package Comely\Buffer
 */
class Buffer extends AbstractByteArray
{
    /**
     * @param string $hex
     * @return static
     */
    public static function fromBase16(string $hex): static
    {
        if ($hex) {
            // Validate string as Hexadecimal
            if (!preg_match('/^(0x)?[a-f0-9]+$/i', $hex)) {
                throw new \InvalidArgumentException('Cannot instantiate Buffer; expected Base16/Hexadecimal string');
            }

            // Remove the "0x" prefix
            if (substr($hex, 0, 2) === "0x") {
                $hex = substr($hex, 2);
            }

            // Evens-out odd number of hexits
            if (strlen($hex) % 2 !== 0) {
                $hex = "0" . $hex;
            }
        }

        return new static(hex2bin($hex));
    }

    /**
     * @param bool $prefix
     * @return string
     */
    public function toBase16(bool $prefix = false): string
    {
        $hexits = bin2hex($this->raw());
        if (strlen($hexits) % 2 !== 0) {
            $hexits = "0" . $hexits;
        }
        return $prefix ? "0x" . $hexits : $hexits;
    }

    /**
     * @param string $b64
     * @return static
     */
    public static function fromBase64(string $b64): static
    {
        $bytes = base64_decode($b64, true);
        if (!$bytes) {
            throw new \InvalidArgumentException('Cannot instantiate Buffer; Invalid base64 encoded data');
        }

        return new static($bytes);
    }

    /**
     * @return string
     */
    public function toBase64(): string
    {
        return base64_encode($this->data);
    }

    /**
     * @param array $bA
     * @return static
     */
    public static function fromByteArray(array $bA): static
    {
        $i = -1;
        $str = "";
        foreach ($bA as $byte) {
            $i++;
            if (!is_int($byte) || $byte < 0 || $byte > 0xff) {
                throw new \InvalidArgumentException(sprintf('Invalid byte at index %d', $i));
            }

            $str .= chr($byte);
        }

        return new static($str);
    }

    /**
     * @param array $bytes
     * @return static
     */
    public static function fromBinary(array $bytes): static
    {
        $bytes = implode(" ", $bytes);
        if (!preg_match('/^[01]{1,8}(\s[01]{1,8})*$/', $bytes)) {
            throw new \InvalidArgumentException('Cannot instantiate Buffer; expected Binary');
        }

        $bytes = explode(" ", $bytes);
        $bA = [];
        foreach ($bytes as $byte) {
            $bA[] = gmp_intval(gmp_init($byte, 2));
        }

        return static::fromByteArray($bA);
    }

    /**
     * @param bool $padded8bits
     * @return array
     */
    public function toBinary(bool $padded8bits = false): array
    {
        $bA = $this->byteArray();
        $bin = [];
        foreach ($bA as $byte) {
            $bin[] = $padded8bits ? str_pad(decbin($byte), 8, "0", STR_PAD_LEFT) : decbin($byte);
        }

        return $bin;
    }
}
