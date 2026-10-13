<?php

namespace App\Services\FasahPay;

use RuntimeException;

class FasahPayRequestException extends RuntimeException
{
    /**
     * @param  array<string, mixed>|null  $responseData
     */
    public function __construct(
        public readonly int $httpStatus,
        string $message,
        public readonly ?array $responseData = null,
    ) {
        parent::__construct($message);
    }
}
