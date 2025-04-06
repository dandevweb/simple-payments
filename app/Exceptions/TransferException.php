<?php

namespace App\Exceptions;

use Exception;

class TransferException extends Exception
{
    public function __construct(string $message, protected int $statusCode = 400)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
