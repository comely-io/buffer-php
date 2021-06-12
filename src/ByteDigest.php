<?php
declare(strict_types=1);

namespace Comely\Buffer;

/**
 * Class ByteDigest
 * @package Comely\Buffer
 */
class ByteDigest
{
    /** @var string */
    private string $val;
    /** @var bool */
    private bool $returnBuffer = true;

    /**
     * ByteDigest constructor.
     * @param AbstractByteArray $bA
     */
    public function __construct(AbstractByteArray $bA)
    {
        $this->val = $bA->raw();
    }

    /**
     * @return $this
     */
    public function toString(): self
    {
        $this->returnBuffer = false;
        return $this;
    }

    /**
     * @param string $algo
     * @param int $iterations
     * @param int|null $len
     * @return string|Buffer
     */
    public function hash(string $algo, int $iterations = 1, ?int $len = null): string|Buffer
    {
        if (!in_array($algo, hash_algos())) {
            throw new \OutOfBoundsException('Invalid/unsupported hash algorithm');
        }

        $digest = $this->val;
        for ($i = 0; $i < $iterations; $i++) {
            $digest = hash($algo, $digest, true);
        }

        if ($len > 0) {
            $digest = substr($digest, 0, $len);
        }

        return $this->result($digest);
    }

    /**
     * @param string $algo
     * @param Buffer|string $key
     * @return string|Buffer
     */
    public function hmac(string $algo, Buffer|string $key): string|Buffer
    {
        if (!in_array($algo, hash_hmac_algos())) {
            throw new \OutOfBoundsException('Invalid/unsupported hmac algorithm');
        }

        $key = $key instanceof Buffer ? $key->raw() : $key;
        return $this->result(hash_hmac($algo, $this->val, $key, true));
    }

    /**
     * @param string $algo
     * @param Buffer|string $salt
     * @param int $iterations
     * @param int $len
     * @return string|Buffer
     */
    public function pbkdf2(string $algo, Buffer|string $salt, int $iterations, int $len = 0): string|Buffer
    {
        if (!in_array($algo, hash_algos())) {
            throw new \OutOfBoundsException('Invalid/unsupported hash (pbkdf2) algorithm');
        }

        $salt = $salt instanceof Buffer ? $salt->raw() : $salt;
        return $this->result(hash_pbkdf2($algo, $this->val, $salt, $iterations, $len, true));
    }

    /**
     * @return string|Buffer
     */
    public function md5(): string|Buffer
    {
        return $this->hash("md5");
    }

    /**
     * @return string|Buffer
     */
    public function sha1(): string|Buffer
    {
        return $this->hash("sha1");
    }

    /**
     * @return string|Buffer
     */
    public function sha256(): string|Buffer
    {
        return $this->hash("sha256");
    }

    /**
     * @return string|Buffer
     */
    public function sha512(): string|Buffer
    {
        return $this->hash("sha512");
    }

    /**
     * @return string|Buffer
     */
    public function ripeMd160(): string|Buffer
    {
        return $this->hash("ripemd160");
    }

    /**
     * @param string $raw
     * @return string|Buffer
     */
    private function result(string $raw): string|Buffer
    {
        return $this->returnBuffer ? new Buffer($raw) : $raw;
    }
}
