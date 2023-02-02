<?php
declare(strict_types=1);

namespace Comely\Buffer;

use Comely\Buffer\Traits\CompareBuffersDataTrait;

/**
 * Class Bytes20
 * @package Comely\Buffer
 */
class Bytes20 extends AbstractFixedLenBuffer
{
    /** @var int */
    public const SIZE = 20;

    use CompareBuffersDataTrait;
}
