<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Cart\CartItemCollection;
use App\Enum\EmailFailureType;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    private const CACHE_TTL_HOURS = 24;

    /**
     * Send order confirmation email to the customer.
     *
     * ==============================================================
     * DO NOT MODIFY THIS LOGIC - FIND A WAY TO WORK WITH IT AS IT IS
     * ==============================================================
     *
     * This method includes failure simulation for testing purposes.
     * When EMAIL_ALWAYS_SUCCEED=false, the first attempt will fail,
     * subsequent attempts will succeed.
     *
     * @param  Order  $order  The order to send confirmation for
     * @param  CartItemCollection  $cartItems  The items in the order
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendOrderConfirmationEmail(Order $order, CartItemCollection $cartItems): bool
    {
        try {
            // Check if we should simulate a failure for testing
            if ($this->shouldSimulateFailure($order->order_number)) {
                $this->simulateFailure($order->order_number);
            }

            $this->sendEmail($order, $cartItems);
            $this->logSuccess($order);

            return true;
        } catch (Exception $e) {
            $this->logError($order, $e->getMessage());

            return false;
        }
    }

    private function sendEmail(Order $order, CartItemCollection $cartItems): void
    {
        Mail::to($order->shipping_email)->send(new OrderConfirmation(
            $order->order_number,
            $order->shipping_name,
            (float) $order->total,
            $this->formatCartItems($cartItems),
            $order->shipping_name,
            $order->shipping_address,
            $order->shipping_address2,
            $order->shipping_city,
            $order->shipping_postcode,
            $order->shipping_country,
            $order->shipping_email,
            $order->shipping_phone
        ));
    }

    private function shouldSimulateFailure(string $orderNumber): bool
    {
        if (config('app.email_always_succeed', false)) {
            return false;
        }

        $attempts = $this->getAttempts($orderNumber);

        return $attempts === 0;
    }

    /**
     * @throws Exception
     */
    private function simulateFailure(string $orderNumber): void
    {
        $attempts = $this->incrementAttempts($orderNumber);
        $failureType = EmailFailureType::random();
        $errorMessage = $failureType->getMessage();

        $this->logFailure($orderNumber, $attempts, $errorMessage);

        throw new Exception($errorMessage);
    }

    private function getAttempts(string $orderNumber): int
    {
        $attemptKey = $this->getAttemptKey($orderNumber);

        return Cache::get($attemptKey, 0);
    }

    private function incrementAttempts(string $orderNumber): int
    {
        $attemptKey = $this->getAttemptKey($orderNumber);
        $attempts = $this->getAttempts($orderNumber) + 1;

        Cache::put($attemptKey, $attempts, now()->addHours(self::CACHE_TTL_HOURS));

        return $attempts;
    }

    private function getAttemptKey(string $orderNumber): string
    {
        return "email_attempts_{$orderNumber}";
    }

    private function formatCartItems(CartItemCollection $cartItems): array
    {
        return array_map(function ($item) {
            return [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ];
        }, $cartItems->toArray());
    }

    private function logSuccess(Order $order): void
    {
        Log::info('Order confirmation email sent successfully', [
            'order_number' => $order->order_number,
            'recipient' => $order->shipping_email,
        ]);
    }

    private function logError(Order $order, string $errorMessage): void
    {
        Log::error('Failed to send order confirmation email', [
            'order_number' => $order->order_number,
            'error' => $errorMessage,
        ]);
    }

    private function logFailure(string $orderNumber, int $attempts, string $errorMessage): void
    {
        Log::warning('Email service failed (simulated)', [
            'order_number' => $orderNumber,
            'attempt' => $attempts,
            'error' => $errorMessage,
        ]);
    }
}
