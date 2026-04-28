<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cardTypes = ['Visa', 'Mastercard', 'American Express', 'Discover'];
        $cardType = $this->faker->randomElement($cardTypes);

        return [
            'card_type' => $cardType,
            'last_four_digits' => $this->faker->numerify('####'),
            'expiry_month' => $this->faker->numberBetween(1, 12),
            'expiry_year' => $this->faker->numberBetween(date('Y'), date('Y') + 10),
            'transaction_id' => 'TXN-'.$this->faker->unique()->numerify('########'),
            'status' => 'completed',
            'amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
