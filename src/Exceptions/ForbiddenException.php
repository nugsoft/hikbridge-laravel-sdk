<?php

namespace Nugsoft\HikBridge\Exceptions;

class ForbiddenException extends HikBridgeException
{
    public function __construct(string $message, int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
