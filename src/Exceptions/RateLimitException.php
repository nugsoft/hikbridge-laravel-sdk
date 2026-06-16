<?php

namespace Nugsoft\HikBridge\Exceptions;

class RateLimitException extends HikBridgeException
{
    public function __construct(
        string $message,
        int $code = 429,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $code);
    }

    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
