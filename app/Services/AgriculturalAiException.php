<?php

namespace App\Services;

use RuntimeException;

class AgriculturalAiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 503,
        public readonly string $errorCode = 'ai_unavailable',
    ) {
        parent::__construct($message);
    }
}
