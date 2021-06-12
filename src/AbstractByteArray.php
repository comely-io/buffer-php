<?php
declare(strict_types=1);

namespace Comely\Buffer;

/**
 * Class AbstractByteArray
 * @package Comely\Buffer
 * @property-read bool $_machine_isLE
 * @property-read bool $_gmp_isLE
 */
class AbstractByteArray
{
    public const GMP_LITTLE_ENDIAN = 0x01;
    public const GMP_BIG_ENDIAN = 0x02;

    /** @var string */
    protected string $data = "";
    /** @var int */
    protected int $len = 0;
    /** @var bool */
    protected bool $readOnly = true;
    /** @var bool */
    protected bool $_machine_isLE;
    /** @var bool */
    protected bool $_gmp_isLE;

    /**
     * @return bool
     */
    public static function isLittleEndian(): bool
    {
        return pack("S", 1) === pack("v", 1);
    }

    /**
     * @return int
     */
    public static function gmpEndianess(): int
    {
        return gmp_strval(gmp_init(65534, 10), 16) === "feff" ? self::GMP_LITTLE_ENDIAN : self::GMP_BIG_ENDIAN;
    }

    /**
     * @param string $inp
     * @param bool $checkHex
     * @return string
     */
    public static function swapEndianess(string $inp, bool $checkHex = true): string
    {
        $isHex = $checkHex && preg_match('/^[a-f0-9]+$/i', $inp);
        return implode("", array_reverse(str_split($inp, $isHex ? 2 : 1)));
    }

    /**
     * AbstractByteArray constructor.
     * @param string|null $data
     */
    public function __construct(?string $data = null)
    {
        if ($data) {
            $this->setBuffer($data, false);
        }

        if (!$this->len) {
            $this->writable();
        }

        $this->_machine_isLE = self::isLittleEndian();
        $this->_gmp_isLE = self::gmpEndianess() === self::GMP_LITTLE_ENDIAN;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            "size" => $this->len,
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->data;
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "_machine_isLE":
            case "_gmp_isLE":
                return $this->$prop;
        }

        throw new \DomainException('Cannot read value of a protected property');
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [
            $this->readOnly,
            $this->len,
            base64_encode($this->data)
        ];
    }

    /**
     * @param array $data
     */
    public function __unserialize(array $data)
    {
        $len = intval($data[1]);
        if ($len > 0) {
            $bytes = base64_decode($data[2], true);
            if ($bytes === false) {
                throw new \UnexpectedValueException('Could not decode base64 encoded data');
            }

            if (strlen($bytes) !== $len) {
                throw new \LengthException(
                    sprintf('Buffer serialized with len of %d cannot be reinstated with %d bytes', $len, strlen($bytes))
                );
            }

            $this->setBuffer($bytes, false);
        }

        $this->readOnly = intval($data[0]) === 1;
        $this->_machine_isLE = self::isLittleEndian();
        $this->_gmp_isLE = self::gmpEndianess() === self::GMP_LITTLE_ENDIAN;
    }

    /**
     * @return int
     */
    public function len(): int
    {
        return $this->len;
    }

    /**
     * @return $this
     */
    public function clean(): self
    {
        $this->data = "";
        $this->len = 0;
        return $this;
    }

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
     * @param AbstractByteArray|string|null $bytes
     * @return $this
     */
    public function append(AbstractByteArray|string|null $bytes): self
    {
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
    public function prepend(AbstractByteArray|string|null $bytes): self
    {
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
    public function appendUInt8(int $uint): self
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
    public function appendUInt16LE(int $uint): self
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
    public function appendUInt16BE(int $uint): self
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
    public function appendUInt32LE(int $uint): self
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
    public function appendUInt32BE(int $uint): self
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
    public function appendUInt64LE(int|string $uint): self
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
    public function appendUInt64BE(int|string $uint): self
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
     * @return string
     */
    public function raw(): string
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function byteArray(): array
    {
        $bA = [];
        for ($i = 0; $i < $this->len; $i++) {
            $bA[] = ord($this->data[$i]);
        }

        return $bA;
    }

    /**
     * @param int|null $start
     * @param int|null $length
     * @return $this
     */
    public function copy(?int $start = null, ?int $length = null): static
    {
        $bytes = $this->data;
        if (is_int($start)) {
            $bytes = is_int($length) ? substr($bytes, $start, $length) : substr($bytes, $start);
        }

        return new static($bytes);
    }

    /**
     * @param AbstractByteArray|string $cmp
     * @return bool
     */
    public function equals(AbstractByteArray|string $cmp): bool
    {
        if ($cmp instanceof static) {
            $cmp = $cmp->raw();
        }

        return $this->len === strlen($cmp) && $this->data === $cmp;
    }

    /**
     * @param \Closure $func
     * @return $this
     */
    public function apply(\Closure $func): static
    {
        $applied = $func($this->data);
        if (!is_string($applied)) {
            throw new \UnexpectedValueException(sprintf('Expected string from apply callback, got "%s"', gettype($applied)));
        }

        return new static($applied);
    }

    /**
     * @return ByteReader
     */
    public function read(): ByteReader
    {
        return new ByteReader($this);
    }

    /**
     * @return ByteDigest
     */
    public function hash(): ByteDigest
    {
        return new ByteDigest($this);
    }

    /**
     * @return $this
     */
    public function switchEndianness(): static
    {
        $flipped = new static(self::swapEndianess($this->raw()));
        if ($this->isWritable()) {
            $flipped->writable();
        } else {
            $flipped->readOnly();
        }

        return $flipped;
    }

    /**
     * @return array
     */
    protected function dump(): array
    {
        return [
            "bytes" => $this->byteArray(),
            "size" => $this->len,
            "readOnly" => $this->readOnly(),
            "_machineIsLE" => $this->_machine_isLE,
            "_gmpIsLE" => $this->_gmp_isLE,
        ];
    }

    /**
     * @param string $bytes
     * @param bool $checkWritable
     */
    protected function setBuffer(string $bytes, bool $checkWritable = true): void
    {
        if ($checkWritable) {
            $this->checkWritable();
        }

        $this->data = $bytes;
        $this->len = strlen($this->data);
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
}
