<?php
declare(strict_types=1);

namespace Comely\Buffer;

use Comely\Buffer\BigInteger\EncodeDecodeTrait;
use Comely\Buffer\BigInteger\Math;

/**
 * Class BigNumber
 * @package Comely\Buffer
 */
class BigInteger extends Math
{
    /** @var \GMP */
    private readonly \GMP $int;

    use EncodeDecodeTrait;

    /**
     * @param int|string|\Comely\Buffer\AbstractByteArray|\GMP $n
     */
    public function __construct(int|string|AbstractByteArray|\GMP $n)
    {
        $this->int = $this->getGMPn($n);
    }

    /**
     * @return \GMP
     */
    public function toGMP(): \GMP
    {
        return $this->int;
    }

    /**
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return gmp_cmp($this->int, 0) >= 0;
    }

    /**
     * @return bool
     */
    public function isSigned(): bool
    {
        return !$this->isUnsigned();
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return int
     */
    public function cmp(int|string|self|AbstractByteArray|\GMP $n2): int
    {
        return gmp_cmp($this->int, $this->getGMPn($n2));
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return bool
     */
    public function greaterThan(int|string|self|AbstractByteArray|\GMP $n2): bool
    {
        return $this->cmp($n2) > 0;
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return bool
     */
    public function greaterThanOrEquals(int|string|self|AbstractByteArray|\GMP $n2): bool
    {
        return $this->cmp($n2) >= 0;
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return bool
     */
    public function lessThan(int|string|self|AbstractByteArray|\GMP $n2): bool
    {
        return $this->cmp($n2) < 0;
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return bool
     */
    public function lessThanOrEquals(int|string|self|AbstractByteArray|\GMP $n2): bool
    {
        return $this->cmp($n2) <= 0;
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return $this
     */
    public function add(int|string|self|AbstractByteArray|\GMP $n2): static
    {
        return new static(gmp_add($this->int, $this->getGMPn($n2)));
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return $this
     */
    public function sub(int|string|self|AbstractByteArray|\GMP $n2): static
    {
        return new static(gmp_sub($this->int, $this->getGMPn($n2)));
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return $this
     */
    public function mul(int|string|self|AbstractByteArray|\GMP $n2): static
    {
        return new static(gmp_mul($this->int, $this->getGMPn($n2)));
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return $this
     */
    public function div(int|string|self|AbstractByteArray|\GMP $n2): static
    {
        return new static(gmp_div($this->int, $this->getGMPn($n2)));
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $divisor
     * @return $this
     */
    public function mod(int|string|self|AbstractByteArray|\GMP $divisor): static
    {
        return new static(gmp_mod($this->int, $this->getGMPn($divisor)));
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n2
     * @return array|null
     */
    public function squareRoot(int|string|self|AbstractByteArray|\GMP $n2): ?array
    {
        $n2 = $this->getGMPn($n2);
        if (gmp_legendre($this->int, $n2) !== 1) {
            return null;
        }

        $sqrt1 = gmp_powm($this->int, gmp_div_q(gmp_add($n2, gmp_init(1, 10)), gmp_init(4, 10)), $n2);
        $sqrt2 = gmp_mod(gmp_sub($n2, $sqrt1), $n2);
        return [new static($sqrt1), new static($sqrt2)];
    }

    /**
     * @param int $n
     * @return $this
     */
    public function shiftRight(int $n): static
    {
        return new static(gmp_div_q($this->int, gmp_pow(2, $n)));
    }

    /**
     * @param int $n
     * @return $this
     */
    public function shiftLeft(int $n): static
    {
        return new static(gmp_mul($this->int, gmp_pow(2, $n)));
    }

    /**
     * @param int|string|\Comely\Buffer\BigInteger|\Comely\Buffer\AbstractByteArray|\GMP $n
     * @return \GMP
     */
    private function getGMPn(int|string|self|AbstractByteArray|\GMP $n): \GMP
    {
        if ($n instanceof \GMP) {
            return $n;
        }

        if (is_int($n)) {
            return gmp_init($n, 10);
        }

        if (is_string($n)) {
            if (preg_match('/^(0|-?[1-9][0-9]+)$/', $n)) {
                return gmp_init($n, 10);
            } elseif (preg_match('/^(0x)?[a-f0-9]+$/i', $n)) {
                return gmp_init($n, 16);
            }

            throw new \InvalidArgumentException('Invalid/malformed value for BigInteger');
        }

        if ($n instanceof AbstractByteArray) {
            return gmp_init($n->toBase16(), 16);
        }

        if ($n instanceof self) {
            return $n->toGMP();
        }

        throw new \OutOfBoundsException('Cannot use argument value with BigInteger');
    }
}
