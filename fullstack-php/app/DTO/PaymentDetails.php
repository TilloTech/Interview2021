<?php

declare(strict_types=1);

namespace App\DTO;

use InvalidArgumentException;

readonly class PaymentDetails
{
    public function __construct(
        public string $cardNumber,
        public string $expiryDate,
        public string $cvv
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cardNumber: $data['card_number'] ?? throw new InvalidArgumentException('Card number is required'),
            expiryDate: $data['expiry_date'] ?? throw new InvalidArgumentException('Expiry date is required'),
            cvv: $data['cvv'] ?? throw new InvalidArgumentException('CVV is required')
        );
    }

    private function validate(): void
    {
        if (empty($this->cardNumber)) {
            throw new InvalidArgumentException('Card number cannot be empty');
        }

        if (empty($this->expiryDate)) {
            throw new InvalidArgumentException('Expiry date cannot be empty');
        }

        if (empty($this->cvv)) {
            throw new InvalidArgumentException('CVV cannot be empty');
        }

        // Simple validation - card number should be exactly 16 digits
        if (! preg_match('/^\d{16}$/', $this->cardNumber)) {
            throw new InvalidArgumentException('Card number must be exactly 16 digits');
        }

        if (! preg_match('/^\d{2}\/\d{2}$/', $this->expiryDate)) {
            throw new InvalidArgumentException('Invalid expiry date format (MM/YY)');
        }

        if (! preg_match('/^\d{3,4}$/', $this->cvv)) {
            throw new InvalidArgumentException('Invalid CVV format');
        }
    }

    public function toArray(): array
    {
        return [
            'card_number' => $this->cardNumber,
            'expiry_date' => $this->expiryDate,
            'cvv' => $this->cvv,
        ];
    }
}
