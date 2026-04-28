<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Cart\Cart;
use App\DTO\Cart\CartItem;
use App\DTO\PaymentResponse;
use App\Enum\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class OrderService
{
    public function createOrder(Cart $cart, User $user, PaymentResponse $paymentResponse): Order
    {
        try {
            // Create payment record
            $payment = $this->createPaymentRecord($cart, $paymentResponse);

            $customer = $cart->getCustomer();
            if (! $customer) {
                throw new InvalidArgumentException('Cart must contain customer information');
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'shipping_name' => $customer->name,
                'shipping_email' => $customer->email,
                'shipping_phone' => $customer->phone,
                'shipping_address' => $customer->address,
                'shipping_address2' => $customer->address2,
                'shipping_city' => $customer->city,
                'shipping_postcode' => $customer->postcode,
                'shipping_country' => $customer->country,
                'subtotal' => $cart->getSubtotal(),
                'tax' => $cart->getTax(),
                'shipping' => $cart->getShipping(),
                'total' => $cart->getTotal(),
                'payment_method' => PaymentMethod::CARD,
                'status' => 'confirmed',
            ]);

            $this->createOrderItems($order, $cart->getItems()->getItems());

            return $order;
        } catch (\Exception $e) {
            // Log the exception properly to avoid malformed JSON
            Log::error('Order creation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function createPaymentRecord(Cart $cart, PaymentResponse $paymentResponse): Payment
    {
        $customer = $cart->getCustomer();
        $paymentDetails = $cart->getPaymentDetails();

        if (! $customer || ! $paymentDetails) {
            throw new InvalidArgumentException('Cart must contain customer and payment information');
        }

        // Parse expiry date (MM/YY format)
        $expiryParts = explode('/', $paymentDetails->expiryDate);
        $expiryMonth = $expiryParts[0];
        $expiryYear = '20'.$expiryParts[1];

        return Payment::create([
            'card_type' => Payment::getCardType($paymentDetails->cardNumber),
            'last_four_digits' => Payment::getLastFourDigits($paymentDetails->cardNumber),
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear,
            'transaction_id' => $paymentResponse->transactionId,
            'status' => $paymentResponse->isSuccessful() ? 'completed' : 'failed',
            'amount' => $cart->getTotal(),
        ]);
    }

    /** @param CartItem[] $cartItems */
    private function createOrderItems(Order $order, array $cartItems): void
    {
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->productId,
                'product_name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->getSubtotal(),
            ]);
        }
    }
}
