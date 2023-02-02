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
}
