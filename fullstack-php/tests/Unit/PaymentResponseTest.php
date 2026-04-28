<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\PaymentResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentResponseTest extends TestCase
{
    #[Test]
    public function it_creates_successful_payment_response()
    {
        $response = PaymentResponse::success('Payment processed successfully', 'TXN-123');

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('Payment processed successfully', $response->message);
        $this->assertEquals('TXN-123', $response->transactionId);
    }

    #[Test]
    public function it_creates_successful_payment_response_without_transaction_id()
    {
        $response = PaymentResponse::success('Payment processed successfully');

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('Payment processed successfully', $response->message);
        $this->assertNull($response->transactionId);
    }

    #[Test]
    public function it_creates_failed_payment_response()
    {
        $response = PaymentResponse::failure('Payment failed');

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('Payment failed', $response->message);
        $this->assertNull($response->transactionId);
    }

    #[Test]
    public function it_creates_payment_response_with_constructor()
    {
        $response = new PaymentResponse(true, 'Custom message', 'TXN-456');

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('Custom message', $response->message);
        $this->assertEquals('TXN-456', $response->transactionId);
    }

    #[Test]
    public function it_handles_null_transaction_id()
    {
        $response = new PaymentResponse(false, 'Error message', null);

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('Error message', $response->message);
        $this->assertNull($response->transactionId);
    }
}
