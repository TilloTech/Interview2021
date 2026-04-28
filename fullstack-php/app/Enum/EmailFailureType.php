<?php

declare(strict_types=1);

namespace App\Enum;

enum EmailFailureType: string
{
    case TIMEOUT = 'timeout';
    case RATE_LIMIT = 'rate_limit';
    case SERVICE_UNAVAILABLE = 'service_unavailable';
    case NETWORK_ERROR = 'network_error';

    public function getMessage(): string
    {
        return match ($this) {
            self::TIMEOUT => 'Request timeout',
            self::RATE_LIMIT => 'Rate limit exceeded',
            self::SERVICE_UNAVAILABLE => 'Email service temporarily unavailable',
            self::NETWORK_ERROR => 'Network connection error',
        };
    }

    public function getErrorCode(): string
    {
        return strtoupper($this->value);
    }

    public static function random(): self
    {
        $cases = self::cases();

        return $cases[array_rand($cases)];
    }
}
