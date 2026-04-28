<?php

declare(strict_types=1);

namespace App\DTO;

readonly class Customer
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public string $address,
        public ?string $address2,
        public string $city,
        public string $postcode,
        public string $country
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'address2' => $this->address2,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'country' => $this->country,
        ];
    }
}
