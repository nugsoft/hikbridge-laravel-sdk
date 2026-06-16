<?php

namespace Nugsoft\HikBridge\Exceptions;

class ServerException extends HikBridgeException
{
    public function __construct(string $message, int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
