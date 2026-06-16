<?php

namespace Nugsoft\HikBridge\Exceptions;

use RuntimeException;

class HikBridgeException extends RuntimeException
{
    public function httpStatus(): int
    {
        return $this->getCode();
    }
}
