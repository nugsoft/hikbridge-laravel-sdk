<?php

namespace Nugsoft\HikBridge\Exceptions;

class NotFoundException extends HikBridgeException
{
    public function __construct(string $message, int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
