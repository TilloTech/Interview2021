<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_category(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
        ]);
    }

    public function test_category_has_many_products(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->products->contains($product1));
        $this->assertTrue($category->products->contains($product2));
        $this->assertEquals(2, $category->products->count());
    }

    public function test_category_color_method(): void
    {
        $electronics = Category::create(['name' => 'Electronics']);
        $clothing = Category::create(['name' => 'Clothing']);
        $unknown = Category::create(['name' => 'Unknown']);

        $this->assertEquals('blue', $electronics->getColor());
        $this->assertEquals('green', $clothing->getColor());
        $this->assertEquals('gray', $unknown->getColor());
    }

    public function test_category_search_scope(): void
    {
        Category::factory()->create(['name' => 'Electronics']);
        Category::factory()->create(['name' => 'Clothing']);
        Category::factory()->create(['name' => 'Books']);

        $results = Category::where('name', 'like', '%Electronics%')->get();
        $this->assertEquals(1, $results->count());
        $this->assertEquals('Electronics', $results->first()->name);
    }

    public function test_category_casts(): void
    {
        $category = Category::factory()->create();

        $this->assertIsInt($category->id);
        $this->assertIsString($category->name);
    }
}
