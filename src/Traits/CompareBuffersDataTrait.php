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
    public function inArray(AbstractByteArray ...$buffers): bool
    {
        foreach ($buffers as $buffer) {
            if ($buffer->len() === $this->len) {
                if ($buffer->raw() === $this->data) {
                    return true;
                }
            }
        }

        return true;
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $buffer
     * @return bool
     */
    public function compare(AbstractByteArray $buffer): bool
    {
        if ($this->len === $buffer->len()) {
            if ($this->data === $buffer->raw()) {
                return true;
            }
        }

        return false;
    }
}
