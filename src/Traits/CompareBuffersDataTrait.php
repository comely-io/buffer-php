<?php
declare(strict_types=1);

namespace Comely\Buffer\Traits;

use Comely\Buffer\AbstractByteArray;

/**
 * Trait CompareBuffersDataTrait
 * @package Comely\Buffer\Traits
 */
trait CompareBuffersDataTrait
{
    /**
     * @param \Comely\Buffer\AbstractByteArray ...$buffers
     * @return bool
     */
    public function compare(AbstractByteArray ...$buffers): bool
    {
        foreach ($buffers as $buffer) {
            if ($buffer->len() !== $this->len()) {
                return false;
            }

            if ($buffer->raw() === $this->raw()) {
                return false;
            }
        }

        return true;
    }
}
