<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\PaymentDetails;
use App\DTO\PaymentResponse;
use App\Service\PaymentService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;

    private PaymentDetails $paymentDetails;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService;
        $this->paymentDetails = new PaymentDetails(
            '1234567890123456',
            '12/25',
            '123'
        );
    }

    #[Test]
    public function it_processes_successful_payment()
    {
        $result = $this->paymentService->processPayment($this->paymentDetails, 100.00);

        $this->assertInstanceOf(PaymentResponse::class, $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Payment processed successfully', $result->message);
        $this->assertStringStartsWith('TXN-', $result->transactionId);
    }

    #[Test]
    public function it_handles_invalid_card_number()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card number must be exactly 16 digits');
        new PaymentDetails(
            '123456789012345', // Too short (15 digits)
            '12/25',
            '123'
        );
    }

    #[Test]
    public function it_handles_invalid_expiry_date()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid expiry date format (MM/YY)');
        new PaymentDetails(
            '1234567890123456',
            '12-25', // Invalid format (should be 12/25)
            '123'
        );
    }

    #[Test]
    public function it_handles_invalid_cvv()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid CVV format');
        new PaymentDetails(
            '1234567890123456',
            '12/25',
            '12' // Too short
        );
    }

    #[Test]
    public function it_handles_zero_amount()
    {
        $result = $this->paymentService->processPayment($this->paymentDetails, 0.00);

        $this->assertInstanceOf(PaymentResponse::class, $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Payment service is currently unavailable. Please try again.', $result->message);
        $this->assertNull($result->transactionId);
    }

    #[Test]
    public function it_handles_negative_amount()
    {
        $result = $this->paymentService->processPayment($this->paymentDetails, -10.00);

        $this->assertInstanceOf(PaymentResponse::class, $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('Payment service is currently unavailable. Please try again.', $result->message);
        $this->assertNull($result->transactionId);
    }

    #[Test]
    public function it_generates_unique_transaction_ids()
    {
        $result1 = $this->paymentService->processPayment($this->paymentDetails, 100.00);
        $result2 = $this->paymentService->processPayment($this->paymentDetails, 100.00);

        $this->assertTrue($result1->isSuccessful());
        $this->assertTrue($result2->isSuccessful());
        $this->assertNotEquals($result1->transactionId, $result2->transactionId);
        $this->assertStringStartsWith('TXN-', $result1->transactionId);
        $this->assertStringStartsWith('TXN-', $result2->transactionId);
    }
}
