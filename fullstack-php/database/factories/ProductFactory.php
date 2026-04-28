<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 500);
        $hasDiscount = $this->faker->boolean(30); // 30% chance of having a discount

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $hasDiscount ? $price * 0.8 : $price,
            'original_price' => $hasDiscount ? $price : null,
            'image' => 'https://images.unsplash.com/photo-'.$this->faker->regexify('[0-9]{10}').'?w=400&h=400&fit=crop',
            'discount' => $hasDiscount ? $this->faker->numberBetween(10, 50) : null,
            'category_id' => Category::factory(),
        ];
    }

    /**
     * Indicate that the product has a discount.
     */
    public function withDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_price' => $attributes['price'] * 1.2,
            'price' => $attributes['price'],
            'discount' => $this->faker->numberBetween(10, 50),
        ]);
    }

    /**
     * Indicate that the product belongs to a specific category.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
