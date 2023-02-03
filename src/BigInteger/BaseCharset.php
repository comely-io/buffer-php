<?php
declare(strict_types=1);

namespace Comely\Buffer\BigInteger;

/**
 * Class BaseCharset
 * @package Comely\Buffer\BigInteger
 */
class BaseCharset
{
    /** @var int */
    public readonly int $len;

    /**
     * @param string $charset
     * @param bool $caseSensitive
     */
    public function __construct(
        public readonly string $charset,
        public readonly bool   $caseSensitive
    )
    {
        $this->len = strlen($this->charset);
    }
}
