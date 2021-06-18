<?php
declare(strict_types=1);

namespace Comely\Buffer;

/**
 * Class AbstractWritableBuffer
 * @package Comely\Buffer
 */
abstract class AbstractWritableBuffer extends AbstractByteArray
{
    /**
     * @return $this
     */
    public function readOnly(): self
    {
        $this->readOnly = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function writable(): self
    {
        $this->readOnly = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return !$this->readOnly;
    }

    /**
     * @return $this
     */
    public function flush(): self
    {
        $this->checkWritable();
        $this->data = "";
        $this->len = 0;
        return $this;
    }

    /**
     * @param AbstractByteArray|string|null $bytes
     * @return $this
     */
    public function append(AbstractByteArray|string|null $bytes): static
    {
        $this->checkWritable();
        if ($bytes) {
            $bytes = $bytes instanceof static ? $bytes->raw() : $bytes;
            if ($bytes) {
                $this->setBuffer($this->data . $bytes);
            }
        }

        return $this;
    }

    /**
     * @param AbstractByteArray|string|null $bytes
     * @return $this
     */
    public function prepend(AbstractByteArray|string|null $bytes): static
    {
        $this->checkWritable();
        if ($bytes) {
            $bytes = $bytes instanceof static ? $bytes->raw() : $bytes;
            if ($bytes) {
                $this->setBuffer($bytes . $this->data);
            }
        }

        return $this;
    }

    /**
     * @param int $uint
     * @return $this
     */
    public function appendUInt8(int $uint): static
    {
        $this->checkWritable();
        $this->checkUint($uint, 8, 0xff);
        $this->data .= hex2bin(str_pad(dechex($uint), 2, "0", STR_PAD_LEFT));
        $this->len++;
        return $this;
    }

    /**
     * @param int $uint
     * @return $this
     */
    public function appendUInt16LE(int $uint): static
    {
        $this->checkWritable();
        $this->checkUint($uint, 16, 0xffff);
        $this->data .= pack("v", $uint);
        $this->len += 2;
        return $this;
    }

    /**
     * @param int $uint
     * @return $this
     */
    public function appendUInt16BE(int $uint): static
    {
        $this->checkWritable();
        $this->checkUint($uint, 16, 0xffff);
        $this->data .= pack("n", $uint);
        $this->len += 2;
        return $this;
    }

    /**
     * @param int $uint
     * @return $this
     */
    public function appendUInt32LE(int $uint): static
    {
        $this->checkWritable();
        $this->checkUint($uint, 32, 0xffffffff);
        $this->data .= pack("V", $uint);
        $this->len += 4;
        return $this;
    }

    /**
     * @param int $uint
     * @return $this
     */
    public function appendUInt32BE(int $uint): static
    {
        $this->checkWritable();
        $this->checkUint($uint, 32, 0xffffffff);
        $this->data .= pack("N", $uint);
        $this->len += 4;
        return $this;
    }

    /**
     * @param int|string $uint
     * @return $this
     */
    public function appendUInt64LE(int|string $uint): static
    {
        $this->checkWritable();
        $this->checkUint64($uint);
        $packed = str_pad(hex2bin(gmp_strval(gmp_init($uint, 10), 16)), 8, "\0", STR_PAD_LEFT);
        if (!$this->_gmp_isLE) {
            $packed = self::swapEndianess($packed, false);
        }

        $this->data .= $packed;
        $this->len += 8;
        return $this;
    }

    /**
     * @param int|string $uint
     * @return $this
     */
    public function appendUInt64BE(int|string $uint): static
    {
        $this->checkWritable();
        $this->checkUint64($uint);
        $packed = str_pad(hex2bin(gmp_strval(gmp_init($uint, 10), 16)), 8, "\0", STR_PAD_LEFT);
        if ($this->_gmp_isLE) {
            $packed = self::swapEndianess($packed, false);
        }

        $this->data .= $packed;
        $this->len += 8;
        return $this;
    }

    /**
     * @param int $uint
     * @param int $size
     * @param int $max
     */
    protected function checkUint(int $uint, int $size, int $max): void
    {
        if ($uint < 0) {
            throw new \UnderflowException("Cannot appendUint{$size}; Argument is signed integer");
        }

        if ($uint > $max) {
            throw new \OverflowException("Cannot appendUint{$size}; Argument must not exceed {$max}");
        }
    }

    /**
     * @param int|string $val
     */
    protected function checkUint64(int|string $val): void
    {
        if (is_int($val)) {
            $val = strval($val);
        }

        if (!preg_match('/^[1-9]+[0-9]*$/', $val)) {
            throw new \InvalidArgumentException('Invalid/malformed value for Uint64');
        }

        $val = gmp_init($val, 10);
        if (gmp_cmp($val, "0") === -1) {
            throw new \UnderflowException("Cannot appendUint64; Argument is signed integer");
        }

        if (gmp_cmp($val, "18446744073709551615") === 1) {
            throw new \OverflowException("Cannot appendUint64; Argument must not exceed 18,446,744,073,709,551,615");
        }
    }

    /**
     * @return void
     */
    protected function checkWritable(): void
    {
        if ($this->readOnly) {
            throw new \BadMethodCallException('Buffer is in readonly state');
        }
    }
}
