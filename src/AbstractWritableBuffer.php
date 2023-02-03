<?php
declare(strict_types=1);

namespace Comely\Buffer;

use Comely\Buffer\BigInteger\BigEndian;
use Comely\Buffer\BigInteger\LittleEndian;
use Comely\Buffer\BigInteger\Math;

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
        $bytes = $bytes instanceof AbstractByteArray ? $bytes->raw() : $bytes;
        if (is_string($bytes) && strlen($bytes)) {
            $this->setBuffer($this->data . $bytes);
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
        $bytes = $bytes instanceof AbstractByteArray ? $bytes->raw() : $bytes;
        if (is_string($bytes) && strlen($bytes)) {
            $this->setBuffer($bytes . $this->data);
        }

        return $this;
    }

    /**
     * @param int $uint
     * @return $this
     */
    public function prependUInt8(int $uint): static
    {
        $this->checkWritable();
        $this->data = Math::PackUInt8($uint) . $this->data;
        $this->len++;
        return $this;
    }

    /**
     * @param int $uint
     * @return $this
     */
    public function appendUInt8(int $uint): static
    {
        $this->checkWritable();
        $this->data .= Math::PackUInt8($uint);
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
        $this->data .= LittleEndian::PackUInt16($uint);
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
        $this->data .= BigEndian::PackUInt16($uint);
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
        $this->data .= LittleEndian::PackUInt32($uint);
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
        $this->data .= BigEndian::PackUInt32($uint);
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
        $this->data .= LittleEndian::PackUInt64($uint);
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
        $this->data .= BigEndian::PackUInt64($uint);
        $this->len += 8;
        return $this;
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
