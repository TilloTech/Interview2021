<?php

declare(strict_types=1);

namespace App\DTO;

readonly class PaymentResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?string $transactionId
    ) {}

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public static function success(string $message, ?string $transactionId = null): self
    {
        return new self(true, $message, $transactionId);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message, null);
    }
}
