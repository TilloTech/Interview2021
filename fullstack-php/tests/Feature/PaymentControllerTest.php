<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_processes_valid_payment_successfully()
    {
        $paymentData = [
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'amount' => 100.00,
        ];

        $response = $this->postJson('/api/payment/process', $paymentData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment processed successfully',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'transaction_id',
            'amount',
            'processed_at',
        ]);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/payment/process', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'card_number',
            'expiry_date',
            'cvv',
            'amount',
        ]);
    }

    #[Test]
    public function it_validates_card_number_format()
    {
        $paymentData = [
            'card_number' => '123456789012345', // Too short (15 digits)
            'expiry_date' => '12/25',
            'cvv' => '123',
            'amount' => 100.00,
        ];

        $response = $this->postJson('/api/payment/process', $paymentData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['card_number']);
    }

    #[Test]
    public function it_validates_expiry_date_format()
    {
        $paymentData = [
            'card_number' => '1234567890123456',
            'expiry_date' => '12-25', // Wrong format
            'cvv' => '123',
            'amount' => 100.00,
        ];

        $response = $this->postJson('/api/payment/process', $paymentData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['expiry_date']);
    }

    #[Test]
    public function it_validates_cvv_format()
    {
        $paymentData = [
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '12', // Too short
            'amount' => 100.00,
        ];

        $response = $this->postJson('/api/payment/process', $paymentData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cvv']);
    }

    #[Test]
    public function it_validates_amount_is_positive()
    {
        $paymentData = [
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'amount' => 0, // Invalid amount
        ];

        $response = $this->postJson('/api/payment/process', $paymentData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_generates_unique_transaction_id()
    {
        $paymentData = [
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'amount' => 100.00,
        ];

        $response1 = $this->postJson('/api/payment/process', $paymentData);
        $response2 = $this->postJson('/api/payment/process', $paymentData);

        $transactionId1 = $response1->json('transaction_id');
        $transactionId2 = $response2->json('transaction_id');

        $this->assertNotEquals($transactionId1, $transactionId2);
        $this->assertStringStartsWith('TXN-', $transactionId1);
        $this->assertStringStartsWith('TXN-', $transactionId2);
    }

    #[Test]
    public function it_simulates_payment_processing_delay()
    {
        $paymentData = [
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'amount' => 100.00,
        ];

        $startTime = microtime(true);
        $response = $this->postJson('/api/payment/process', $paymentData);
        $endTime = microtime(true);

        $response->assertStatus(200);

        // Should take at least 0.5 seconds due to usleep(500000)
        $this->assertGreaterThanOrEqual(0.5, $endTime - $startTime);
    }
}
