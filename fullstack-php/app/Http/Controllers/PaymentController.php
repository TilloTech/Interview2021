<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Process a payment with the provided card details.
     * This is a fake payment API for demonstration purposes.
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'card_number' => 'required|string|regex:/^\d{16}$/',
            'expiry_date' => 'required|string|regex:/^\d{2}\/\d{2}$/',
            'cvv' => 'required|string|regex:/^\d{3,4}$/',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Simulate payment processing delay
        usleep(500000); // 0.5 seconds

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'transaction_id' => 'TXN-'.strtoupper(uniqid()),
            'amount' => $request->amount,
            'processed_at' => now()->toISOString(),
        ]);
    }
}
