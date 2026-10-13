<?php

namespace App\Services\FasahPay;

use RuntimeException;

class FasahPayRequestException extends RuntimeException
{
    public function __construct(public readonly int $httpStatus, string $message)
    {
        parent::__construct($message);
    }
}
