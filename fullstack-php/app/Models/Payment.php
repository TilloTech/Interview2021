<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_type',
        'last_four_digits',
        'expiry_month',
        'expiry_year',
        'transaction_id',
        'status',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the card type from the card number.
     */
    public static function getCardType(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        if (str_starts_with($cardNumber, '4')) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]|^2[2-7]|^222[1-9]|^22[3-9]|^2[3-6]|^27[0-1]|^2720/', $cardNumber)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'Discover';
        }

        return 'Unknown';
    }

    /**
     * Get the last 4 digits from a card number.
     */
    public static function getLastFourDigits(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        return substr($cardNumber, -4);
    }
}
