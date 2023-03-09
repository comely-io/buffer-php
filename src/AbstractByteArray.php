<?php
declare(strict_types=1);

namespace Comely\Buffer;

use Comely\Buffer\BigInteger\Math;

/**
 * Class AbstractByteArray
 * @package Comely\Buffer
 * @property-read bool $_machine_isLE
 * @property-read bool $_gmp_isLE
 */
class AbstractByteArray
{
    /** @var string */
    protected string $data = "";
    /** @var int */
    protected int $len = 0;
    /** @var bool */
    protected bool $readOnly = true;
    /** @var bool */
    public readonly bool $_machine_isLE;
    /** @var bool */
    public readonly bool $_gmp_isLE;

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
            if (str_starts_with($hex, "0x")) {
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
     * AbstractByteArray constructor.
     * @param string|null $data
     */
    public function __construct(?string $data = null)
    {
        if ($data) {
            $this->setBuffer($data);
        }

        $this->_machine_isLE = Math::isLittleEndian();
        $this->_gmp_isLE = Math::gmpEndianness() === Math::GMP_LITTLE_ENDIAN;
    }

    /**
     * @param int $bytes
     * @return $this
     */
    public function checkSize(int $bytes): static
    {
        if ($this->len !== $bytes) {
            throw new \LengthException(sprintf('Expected value of %d bytes; got %d', $bytes, $this->len));
        }

        return $this;
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
     * @return string
     */
    public function toBase64(): string
    {
        return base64_encode($this->data);
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

            $this->setBuffer($bytes);
        }

        $this->readOnly = intval($data[0]) === 1;
        $this->_machine_isLE = Math::isLittleEndian();
        $this->_gmp_isLE = Math::gmpEndianness() === Math::GMP_LITTLE_ENDIAN;
    }

    /**
     * @return int
     */
    public function len(): int
    {
        return $this->len;
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
     * @param int $bytes
     * @param bool $changeBuffer
     * @return string|false
     */
    public function pop(int $bytes, bool $changeBuffer = true): string|false
    {
        if ($bytes !== 0) {
            $result = $bytes > 0 ? substr($this->data, 0, $bytes) : substr($this->data, $bytes);
            if (strlen($result) === ($bytes > 0 ? $bytes : $bytes * -1)) {
                if ($changeBuffer) {
                    $this->setBuffer($bytes > 0 ? substr($this->data, $bytes) : substr($this->data, 0, $bytes));
                }

                return $result;
            }
        }

        return false;
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
        return new static(Math::SwapEndianness($this->raw()));
    }

    /**
     * @return array
     */
    public function dump(): array
    {
        return [
            "bytes" => $this->byteArray(),
            "size" => $this->len,
            "readOnly" => $this->readOnly,
            "_machineIsLE" => $this->_machine_isLE,
            "_gmpIsLE" => $this->_gmp_isLE,
        ];
    }

    /**
     * @param \Closure $func
     * @return $this
     */
    public function applyFn(\Closure $func): static
    {
        $applied = $func($this->data);
        if (!is_string($applied)) {
            throw new \UnexpectedValueException(sprintf('Expected string from apply callback, got "%s"', gettype($applied)));
        }

        return new static($applied);
    }

    /**
     * @param string $bytes
     */
    protected function setBuffer(string $bytes): void
    {
        $this->data = $bytes;
        $this->len = strlen($this->data);
    }
}
