<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\PaymentDetails;
use App\DTO\PaymentResponse;
use App\Http\Controllers\PaymentController;
use Exception;
use Illuminate\Http\Request;

class PaymentService
{
    public function processPayment(PaymentDetails $paymentDetails, float $amount): PaymentResponse
    {
        try {
            $paymentController = new PaymentController;
            $paymentRequest = new Request([
                'card_number' => $paymentDetails->cardNumber,
                'expiry_date' => $paymentDetails->expiryDate,
                'cvv' => $paymentDetails->cvv,
                'amount' => $amount,
            ]);

            $response = $paymentController->process($paymentRequest);
            $data = $response->getData(true);

            $isSuccessful = $response->getStatusCode() === 200 && $data['success'];

            return $isSuccessful
                ? PaymentResponse::success(
                    $data['message'] ?? 'Payment processed successfully',
                    $data['transaction_id'] ?? null
                )
                : PaymentResponse::failure(
                    $data['message'] ?? 'Payment processing failed'
                );
        } catch (Exception $e) {
            return PaymentResponse::failure('Payment service is currently unavailable. Please try again.');
        }
    }
}
