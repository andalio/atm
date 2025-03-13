<?php

namespace App\Domain\Commission\Exception;

use Exception;

class ExchangeRateException extends Exception
{
    public function __construct(
        string $message = "Failed to retrieve exchange rates.",
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
