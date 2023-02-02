<?php
declare(strict_types=1);

namespace Comely\Buffer;

use Comely\Buffer\Traits\CompareBuffersDataTrait;

/**
 * Class Bytes32
 * @package Comely\Buffer
 */
class Bytes32 extends AbstractFixedLenBuffer
{
    /** @var int */
    protected const SIZE = 32;

    use CompareBuffersDataTrait;

    /**
     * @param string $hex
     * @return static
     */
    public static function fromBase16UnPadded(string $hex): static
    {
        return static::fromBase16(str_pad($hex, static::SIZE * 2, "0", STR_PAD_LEFT));
    }
}
