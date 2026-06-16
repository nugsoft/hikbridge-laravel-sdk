<?php

namespace Nugsoft\HikBridge\Exceptions;

class ValidationException extends HikBridgeException
{
    public function __construct(
        string $message,
        private readonly array $validationErrors = [],
        int $code = 422,
    ) {
        parent::__construct($message, $code);
    }

    public function errors(): array
    {
        return $this->validationErrors;
    }
}
