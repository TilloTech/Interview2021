<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Cart\Cart;
use App\DTO\Cart\CartItemCollection;
use App\DTO\Customer;
use App\DTO\PaymentDetails;
use App\Http\Requests\StoreOrderRequest;

class CartService
{
    private const TAX_RATE = 0.20;

    private const SHIPPING_COST = 5.99;

    public function createCartFromRequest(StoreOrderRequest $request): Cart
    {
        // Extract cart items from request
        $cartItems = new CartItemCollection;
        foreach ($request->input('cart_items', []) as $itemData) {
            $cartItems->addItem($itemData);
        }

        $subtotal = $cartItems->getSubtotal();
        $tax = $subtotal * self::TAX_RATE;
        $shipping = self::SHIPPING_COST;
        $total = $subtotal + $tax + $shipping;

        // Create customer from shipping information
        $customer = new Customer(
            $request->shipping_name,
            $request->shipping_email,
            $request->shipping_phone,
            $request->shipping_address,
            $request->shipping_address2 ?? null,
            $request->shipping_city,
            $request->shipping_postcode,
            $request->shipping_country
        );

        $paymentDetails = $request->has(['card_number', 'expiry_date', 'cvv'])
            ? PaymentDetails::fromArray([
                'card_number' => $request->card_number,
                'expiry_date' => $request->expiry_date,
                'cvv' => $request->cvv,
            ])
            : null;

        return new Cart($cartItems, $subtotal, $tax, $shipping, $total, $paymentDetails, $customer);
    }
}
