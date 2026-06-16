<?php

namespace Nugsoft\HikBridge\Exceptions;

class AuthenticationException extends HikBridgeException
{
    public function __construct(string $message, int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
