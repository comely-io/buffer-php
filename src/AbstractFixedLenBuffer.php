<?php
declare(strict_types=1);

namespace Comely\Buffer;

use Comely\Buffer\Exception\FixedBufferLenException;

/**
 * Class AbstractFixedLenBuffer
 * @package Comely\Buffer
 */
abstract class AbstractFixedLenBuffer extends AbstractByteArray
{
    protected const SIZE = null;

    /**
     * @param string $bytes
     * @throws FixedBufferLenException
     */
    protected function setBuffer(string $bytes): void
    {
        if (strlen($bytes) !== static::SIZE) {
            throw new FixedBufferLenException(sprintf(
                '%s buffer expects fixed length of %d bytes; given %d bytes',
                (new \ReflectionClass($this))->getShortName(),
                static::SIZE,
                strlen($bytes)
            ));
        }

        parent::setBuffer($bytes);
    }
}
