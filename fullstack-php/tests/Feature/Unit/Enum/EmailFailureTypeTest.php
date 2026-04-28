<?php

declare(strict_types=1);

namespace Tests\Feature\Unit\Enum;

use App\Enum\EmailFailureType;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailFailureTypeTest extends TestCase
{
    #[Test]
    public function it_has_all_expected_failure_types()
    {
        $expectedTypes = [
            'timeout',
            'rate_limit',
            'service_unavailable',
            'network_error',
        ];

        $actualTypes = array_map(fn ($case) => $case->value, EmailFailureType::cases());

        $this->assertEquals($expectedTypes, $actualTypes);
    }

    #[Test]
    public function it_returns_correct_message_for_timeout()
    {
        $failureType = EmailFailureType::TIMEOUT;

        $this->assertEquals('Request timeout', $failureType->getMessage());
    }

    #[Test]
    public function it_returns_correct_message_for_rate_limit()
    {
        $failureType = EmailFailureType::RATE_LIMIT;

        $this->assertEquals('Rate limit exceeded', $failureType->getMessage());
    }

    #[Test]
    public function it_returns_correct_message_for_service_unavailable()
    {
        $failureType = EmailFailureType::SERVICE_UNAVAILABLE;

        $this->assertEquals('Email service temporarily unavailable', $failureType->getMessage());
    }

    #[Test]
    public function it_returns_correct_message_for_network_error()
    {
        $failureType = EmailFailureType::NETWORK_ERROR;

        $this->assertEquals('Network connection error', $failureType->getMessage());
    }

    #[Test]
    public function it_returns_correct_error_code_for_timeout()
    {
        $failureType = EmailFailureType::TIMEOUT;

        $this->assertEquals('TIMEOUT', $failureType->getErrorCode());
    }

    #[Test]
    public function it_returns_correct_error_code_for_rate_limit()
    {
        $failureType = EmailFailureType::RATE_LIMIT;

        $this->assertEquals('RATE_LIMIT', $failureType->getErrorCode());
    }

    #[Test]
    public function it_returns_correct_error_code_for_service_unavailable()
    {
        $failureType = EmailFailureType::SERVICE_UNAVAILABLE;

        $this->assertEquals('SERVICE_UNAVAILABLE', $failureType->getErrorCode());
    }

    #[Test]
    public function it_returns_correct_error_code_for_network_error()
    {
        $failureType = EmailFailureType::NETWORK_ERROR;

        $this->assertEquals('NETWORK_ERROR', $failureType->getErrorCode());
    }

    #[Test]
    public function it_returns_random_failure_type()
    {
        $randomType = EmailFailureType::random();

        $this->assertInstanceOf(EmailFailureType::class, $randomType);
        $this->assertContains($randomType, EmailFailureType::cases());
    }

    #[Test]
    public function it_returns_different_random_types_on_multiple_calls()
    {
        $types = [];

        // Call random() multiple times to ensure we get different types
        for ($i = 0; $i < 10; $i++) {
            $types[] = EmailFailureType::random()->value;
        }

        // Should have at least 2 different types (with 4 total types, this is very likely)
        $uniqueTypes = array_unique($types);
        $this->assertGreaterThan(1, count($uniqueTypes));
    }

    #[Test]
    public function it_has_four_total_failure_types()
    {
        $this->assertCount(4, EmailFailureType::cases());
    }

    #[Test]
    public function it_returns_uppercase_error_codes()
    {
        foreach (EmailFailureType::cases() as $failureType) {
            $errorCode = $failureType->getErrorCode();
            $this->assertEquals(strtoupper($errorCode), $errorCode);
        }
    }

    #[Test]
    public function it_returns_non_empty_messages()
    {
        foreach (EmailFailureType::cases() as $failureType) {
            $message = $failureType->getMessage();
            $this->assertNotEmpty($message);
            $this->assertIsString($message);
        }
    }

    #[Test]
    public function it_returns_non_empty_error_codes()
    {
        foreach (EmailFailureType::cases() as $failureType) {
            $errorCode = $failureType->getErrorCode();
            $this->assertNotEmpty($errorCode);
            $this->assertIsString($errorCode);
        }
    }

    #[Test]
    public function it_handles_all_failure_types_in_switch_statement()
    {
        foreach (EmailFailureType::cases() as $failureType) {
            $message = match ($failureType) {
                EmailFailureType::TIMEOUT => 'Request timeout',
                EmailFailureType::RATE_LIMIT => 'Rate limit exceeded',
                EmailFailureType::SERVICE_UNAVAILABLE => 'Email service temporarily unavailable',
                EmailFailureType::NETWORK_ERROR => 'Network connection error',
            };

            $this->assertEquals($failureType->getMessage(), $message);
        }
    }
}
