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
    /** @var null|int Fixed size of buffer in bytes */
    protected const SIZE = null;
    /** @var null|int Pads data if smaller than expected len; set with either STR_PAD_* const */
    protected const PAD_TO_LENGTH = null;

    /**
     * @param string $bytes
     * @throws FixedBufferLenException
     */
    protected function setBuffer(string $bytes): void
    {
        if (is_int(static::PAD_TO_LENGTH)) {
            $bytes = str_pad($bytes, static::SIZE, "\0", static::PAD_TO_LENGTH);
        }

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
