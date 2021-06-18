<?php
declare(strict_types=1);

namespace Comely\Buffer;

/**
 * Class Buffer
 * @package Comely\Buffer
 */
class Buffer extends AbstractWritableBuffer
{
    /**
     * Buffer constructor.
     * @param string|null $data
     */
    public function __construct(?string $data = null)
    {
        parent::__construct($data);
        $this->readOnly = false;
    }
}
