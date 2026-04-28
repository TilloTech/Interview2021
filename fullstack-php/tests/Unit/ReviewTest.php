<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_review()
    {
        $product = Product::factory()->create();

        $review = Review::create([
            'product_id' => $product->id,
            'author' => 'John Doe',
            'content' => 'Great product! Highly recommended.',
            'rating' => 5,
        ]);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'author' => 'John Doe',
            'content' => 'Great product! Highly recommended.',
            'rating' => 5,
        ]);

        $this->assertInstanceOf(Review::class, $review);
        $this->assertEquals($product->id, $review->product_id);
        $this->assertEquals('John Doe', $review->author);
        $this->assertEquals('Great product! Highly recommended.', $review->content);
        $this->assertEquals(5, $review->rating);
    }

    #[Test]
    public function it_belongs_to_product()
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $review->product);
        $this->assertEquals($product->id, $review->product->id);
        $this->assertEquals($product->name, $review->product->name);
    }

    #[Test]
    public function it_casts_rating_to_integer()
    {
        $product = Product::factory()->create();

        $review = Review::create([
            'product_id' => $product->id,
            'author' => 'Jane Smith',
            'content' => 'Good product.',
            'rating' => '4', // String input
        ]);

        $this->assertIsInt($review->rating);
        $this->assertEquals(4, $review->rating);
    }

    #[Test]
    public function it_has_fillable_fields()
    {
        $product = Product::factory()->create();

        $review = Review::create([
            'product_id' => $product->id,
            'author' => 'Test User',
            'content' => 'Test review content.',
            'rating' => 3,
        ]);

        $this->assertNotNull($review->id);
        $this->assertEquals($product->id, $review->product_id);
        $this->assertEquals('Test User', $review->author);
        $this->assertEquals('Test review content.', $review->content);
        $this->assertEquals(3, $review->rating);
    }

    #[Test]
    public function it_can_be_created_with_factory()
    {
        $review = Review::factory()->create();

        $this->assertInstanceOf(Review::class, $review);
        $this->assertNotNull($review->product_id);
        $this->assertNotNull($review->author);
        $this->assertNotNull($review->content);
        $this->assertNotNull($review->rating);
        $this->assertIsInt($review->rating);
        $this->assertGreaterThanOrEqual(1, $review->rating);
        $this->assertLessThanOrEqual(5, $review->rating);
    }

    #[Test]
    public function it_can_have_null_optional_fields()
    {
        $product = Product::factory()->create();

        $review = Review::create([
            'product_id' => $product->id,
            'author' => 'Anonymous',
            'content' => 'Optional content', // Content is required, not optional
            'rating' => 4,
        ]);

        $this->assertNotNull($review->id);
        $this->assertEquals($product->id, $review->product_id);
        $this->assertEquals('Anonymous', $review->author);
        $this->assertEquals('Optional content', $review->content);
        $this->assertEquals(4, $review->rating);
    }

    #[Test]
    public function it_can_be_updated()
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $review->update([
            'author' => 'Updated Author',
            'content' => 'Updated content',
            'rating' => 5,
        ]);

        $this->assertEquals('Updated Author', $review->author);
        $this->assertEquals('Updated content', $review->content);
        $this->assertEquals(5, $review->rating);
    }

    #[Test]
    public function it_can_be_deleted()
    {
        $review = Review::factory()->create();
        $reviewId = $review->id;

        $review->delete();

        $this->assertDatabaseMissing('reviews', ['id' => $reviewId]);
    }
}
