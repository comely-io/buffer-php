<?php
declare(strict_types=1);

namespace Comely\Buffer;

use Comely\Buffer\Exception\ByteReaderUnderflowException;

/**
 * Class ByteReader
 * @package Comely\Buffer
 */
class ByteReader
{
    /** @var string */
    private readonly string $buffer;
    /** @var int */
    public readonly int $len;
    /** @var int */
    private int $pointer = 0;
    /** @var bool */
    public bool $throwUnderflowEx = true;
    /** @var bool */
    private bool $_gmp_isLE;

    /**
     * ByteReader constructor.
     * @param AbstractByteArray $buffer
     */
    public function __construct(AbstractByteArray $buffer)
    {
        $this->buffer = $buffer->raw();
        $this->len = strlen($this->buffer);
        $this->_gmp_isLE = $buffer->_gmp_isLE;
    }

    /**
     * @return $this
     */
    public function ignoreUnderflow(): self
    {
        $this->throwUnderflowEx = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnd(): bool
    {
        return $this->pointer >= $this->len;
    }

    /**
     * @return int
     */
    public function len(): int
    {
        return $this->len;
    }

    /**
     * @return int
     */
    public function pos(): int
    {
        return $this->pointer;
    }

    /**
     * Start reading from beginning
     * @return ByteReader
     */
    public function reset(): self
    {
        $this->pointer = 0;
        return $this;
    }

    /**
     * Resets pointer, gets next N bytes from top
     * @param int $bytes
     * @return string
     * @throws ByteReaderUnderflowException
     */
    public function first(int $bytes): string
    {
        return $this->reset()->next($bytes);
    }

    /**
     * Reads last N bytes previously read (does NOT update internal pointer)
     * @param int $bytes
     * @return string
     */
    public function lookBehind(int $bytes): string
    {
        $goBack = $this->pointer - $bytes;
        if ($bytes < 1 || $goBack < 0) {
            throw new \InvalidArgumentException('Expected positive number of bytes to read');
        }


        return substr($this->buffer, $goBack, $bytes);
    }

    /**
     * Reads next N bytes but does NOT update internal pointer
     * @param int $bytes
     * @return string
     */
    public function lookAhead(int $bytes): string
    {
        if ($bytes < 1) {
            throw new \InvalidArgumentException('Expected positive number of bytes to read');
        }

        return substr($this->buffer, $this->pointer, $bytes);
    }

    /**
     * Reads next N bytes while updating the pointer
     * @param int $bytes
     * @return string
     * @throws ByteReaderUnderflowException
     */
    public function next(int $bytes): string
    {
        if ($this->throwUnderflowEx) {
            if (($this->pointer + $bytes) > $this->len) {
                throw new ByteReaderUnderflowException(sprintf(
                    'Attempt to read next %d bytes, while only %d available',
                    $bytes,
                    ($this->len - $this->pointer)
                ));
            }
        }

        $read = $this->lookAhead($bytes);
        if (strlen($read) === $bytes) {
            $this->pointer += $bytes;
            return $read;
        }

        if ($this->throwUnderflowEx) {
            throw new ByteReaderUnderflowException(sprintf('ByteReader ran out of bytes at pos %d', $this->pointer));
        }

        return $read;
    }

    /**
     * @return int
     * @throws ByteReaderUnderflowException
     */
    public function readUInt8(): int
    {
        return ord($this->next(1));
    }

    /**
     * @return int
     * @throws ByteReaderUnderflowException
     */
    public function readUInt16LE(): int
    {
        return unpack("v", $this->next(2))[1];
    }

    /**
     * @return int
     * @throws ByteReaderUnderflowException
     */
    public function readUInt16BE(): int
    {
        return unpack("n", $this->next(2))[1];
    }

    /**
     * @return int
     * @throws ByteReaderUnderflowException
     */
    public function readUInt32LE(): int
    {
        return unpack("V", $this->next(4))[1];
    }

    /**
     * @return int
     * @throws ByteReaderUnderflowException
     */
    public function readUInt32BE(): int
    {
        return unpack("N", $this->next(4))[1];
    }

    /**
     * @return int|string
     * @throws ByteReaderUnderflowException
     */
    public function readUInt64LE(): int|string
    {
        return $this->readUInt64(true);
    }

    /**
     * @return int|string
     * @throws ByteReaderUnderflowException
     */
    public function readUint64BE(): int|string
    {
        return $this->readUInt64(false);
    }

    /**
     * @param bool $isLE
     * @return int|string
     * @throws ByteReaderUnderflowException
     */
    private function readUInt64(bool $isLE): int|string
    {
        $bytes = $this->next(8);
        if ($isLE && !$this->_gmp_isLE) {
            $bytes = AbstractByteArray::swapEndianess($bytes, false);
        }

        if (!$isLE && $this->_gmp_isLE) {
            $bytes = AbstractByteArray::swapEndianess($bytes, false);
        }

        $dec = gmp_strval(gmp_init(bin2hex($bytes), 16), 10);
        return gmp_cmp($dec, PHP_INT_MAX) !== -1 ? (int)$dec : $dec;
    }

    /**
     * @param int $pos
     * @return $this
     */
    public function setPointer(int $pos): self
    {
        if ($pos < 0 || $pos > $this->len) {
            throw new \RangeException('Invalid pointer position or is out of range');
        }

        $this->pointer = $pos;
        return $this;
    }

    /**
     * @return string
     */
    public function remaining(): string
    {
        return substr($this->buffer, $this->pointer);
    }
}
