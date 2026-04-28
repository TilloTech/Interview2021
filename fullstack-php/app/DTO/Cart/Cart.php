<?php

declare(strict_types=1);

namespace App\DTO\Cart;

use App\DTO\Customer;
use App\DTO\PaymentDetails;

readonly class Cart
{
    public function __construct(
        public CartItemCollection $items,
        public float $subtotal,
        public float $tax,
        public float $shipping,
        public float $total,
        public ?PaymentDetails $paymentDetails = null,
        public ?Customer $customer = null
    ) {}

    public function getItems(): CartItemCollection
    {
        return $this->items;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function getShipping(): float
    {
        return $this->shipping;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getPaymentDetails(): ?PaymentDetails
    {
        return $this->paymentDetails;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function hasPaymentDetails(): bool
    {
        return $this->paymentDetails !== null;
    }

    public function hasCustomer(): bool
    {
        return $this->customer !== null;
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function count(): int
    {
        return $this->items->count();
    }
}
