<?php

declare(strict_types=1);

namespace App\DTO\Cart;

class CartItemCollection
{
    /** @var CartItem[] */
    private array $items = [];

    public function addItem(array $itemData): self
    {
        $this->items[] = CartItem::fromArray($itemData);

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getSubtotal(): float
    {
        return array_sum(array_map(fn (CartItem $item) => $item->getSubtotal(), $this->items));
    }

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return array_map(fn (CartItem $item) => $item->toArray(), $this->items);
    }
}
