<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some categories for testing with unique names
        $this->electronics = Category::factory()->create(['name' => 'Electronics']);
        $this->clothing = Category::factory()->create(['name' => 'Clothing']);
        $this->homeGarden = Category::factory()->create(['name' => 'Home & Garden']);
        $this->sports = Category::factory()->create(['name' => 'Sports']);
    }

    #[Test]
    public function it_displays_products_with_default_sorting()
    {
        // Create products with different creation dates using existing categories
        $product1 = Product::factory()->forCategory($this->electronics)->create(['created_at' => now()->subDays(2)]);
        $product2 = Product::factory()->forCategory($this->clothing)->create(['created_at' => now()->subDays(1)]);
        $product3 = Product::factory()->forCategory($this->homeGarden)->create(['created_at' => now()]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 3)
            ->has('pagination')
            ->has('filters')
            ->has('categories')
            ->where('filters.sort', 'featured')
        );
    }

    #[Test]
    public function it_applies_category_filter()
    {
        $electronicsProduct = Product::factory()->forCategory($this->electronics)->create();
        $clothingProduct = Product::factory()->forCategory($this->clothing)->create();

        $response = $this->get('/?category='.$this->electronics->id);

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 1)
            ->where('filters.category', (string) $this->electronics->id)
        );

        // Verify only electronics product is returned
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.id', $electronicsProduct->id)
        );
    }

    #[Test]
    public function it_applies_search_filter()
    {
        $laptopProduct = Product::factory()->forCategory($this->electronics)->create(['name' => 'Gaming Laptop']);
        $phoneProduct = Product::factory()->forCategory($this->electronics)->create(['name' => 'Smartphone']);
        $shirtProduct = Product::factory()->forCategory($this->clothing)->create(['name' => 'Cotton Shirt']);

        $response = $this->get('/?search=laptop');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 1)
            ->where('filters.search', 'laptop')
        );

        // Verify only laptop product is returned
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.id', $laptopProduct->id)
        );
    }

    #[Test]
    public function it_applies_search_filter_to_description()
    {
        $product1 = Product::factory()->forCategory($this->electronics)->create(['description' => 'High quality gaming laptop']);
        $product2 = Product::factory()->forCategory($this->clothing)->create(['description' => 'Regular office computer']);

        $response = $this->get('/?search=gaming');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 1)
        );

        // Verify only product with "gaming" in description is returned
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.id', $product1->id)
        );
    }

    #[Test]
    public function it_applies_multi_word_search_filter()
    {
        $waterBottle = Product::factory()->forCategory($this->homeGarden)->create(['name' => 'Stainless Steel Water Bottle']);
        $coffeeMug = Product::factory()->forCategory($this->homeGarden)->create(['name' => 'Ceramic Coffee Mug']);
        $laptop = Product::factory()->forCategory($this->electronics)->create(['name' => 'Gaming Laptop']);

        $response = $this->get('/?search=water bottle');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 1)
            ->where('filters.search', 'water bottle')
        );

        // Verify only water bottle product is returned
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.id', $waterBottle->id)
        );
    }

    #[Test]
    public function it_sorts_by_price_low_to_high()
    {
        $expensiveProduct = Product::factory()->forCategory($this->electronics)->create(['price' => 500.00]);
        $cheapProduct = Product::factory()->forCategory($this->clothing)->create(['price' => 50.00]);
        $mediumProduct = Product::factory()->forCategory($this->homeGarden)->create(['price' => 200.00]);

        $response = $this->get('/?sort=price-low');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->where('filters.sort', 'price-low')
        );

        // Verify products are sorted by price ascending
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.price', '50.00')
            ->where('products.1.price', '200.00')
            ->where('products.2.price', '500.00')
        );
    }

    #[Test]
    public function it_sorts_by_price_high_to_low()
    {
        $expensiveProduct = Product::factory()->forCategory($this->electronics)->create(['price' => 500.00]);
        $cheapProduct = Product::factory()->forCategory($this->clothing)->create(['price' => 50.00]);
        $mediumProduct = Product::factory()->forCategory($this->homeGarden)->create(['price' => 200.00]);

        $response = $this->get('/?sort=price-high');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->where('filters.sort', 'price-high')
        );

        // Verify products are sorted by price descending
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.price', '500.00')
            ->where('products.1.price', '200.00')
            ->where('products.2.price', '50.00')
        );
    }

    #[Test]
    public function it_sorts_by_newest()
    {
        $oldProduct = Product::factory()->forCategory($this->electronics)->create(['created_at' => now()->subDays(3)]);
        $newProduct = Product::factory()->forCategory($this->clothing)->create(['created_at' => now()->subDays(1)]);
        $newestProduct = Product::factory()->forCategory($this->homeGarden)->create(['created_at' => now()]);

        $response = $this->get('/?sort=newest');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->where('filters.sort', 'newest')
        );

        // Verify products are sorted by creation date descending
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.id', $newestProduct->id)
            ->where('products.1.id', $newProduct->id)
            ->where('products.2.id', $oldProduct->id)
        );
    }

    #[Test]
    public function it_sorts_by_rating()
    {
        // Create products and add reviews to simulate reviews_count
        $lowRatingProduct = Product::factory()->forCategory($this->electronics)->create();
        $highRatingProduct = Product::factory()->forCategory($this->clothing)->create();
        $mediumRatingProduct = Product::factory()->forCategory($this->homeGarden)->create();

        // Add different numbers of reviews to simulate rating sorting
        Review::factory()->count(5)->for($lowRatingProduct)->create();
        Review::factory()->count(50)->for($highRatingProduct)->create();
        Review::factory()->count(20)->for($mediumRatingProduct)->create();

        $response = $this->get('/?sort=rating');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->where('filters.sort', 'rating')
        );

        // Verify products are sorted by reviews count descending
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.id', $highRatingProduct->id)
            ->where('products.1.id', $mediumRatingProduct->id)
            ->where('products.2.id', $lowRatingProduct->id)
        );
    }

    #[Test]
    public function it_paginates_products()
    {
        // Create 30 products (more than the default 25 per page) using existing categories
        Product::factory()->count(10)->forCategory($this->electronics)->create();
        Product::factory()->count(10)->forCategory($this->clothing)->create();
        Product::factory()->count(10)->forCategory($this->homeGarden)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 25) // First page should have 25 products
            ->where('pagination.current_page', 1)
            ->where('pagination.per_page', 25)
            ->where('pagination.total', 30)
            ->where('pagination.last_page', 2)
        );
    }

    #[Test]
    public function it_loads_second_page()
    {
        // Create 30 products using existing categories
        Product::factory()->count(10)->forCategory($this->electronics)->create();
        Product::factory()->count(10)->forCategory($this->clothing)->create();
        Product::factory()->count(10)->forCategory($this->homeGarden)->create();

        $response = $this->get('/?page=2');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 5) // Second page should have 5 products
            ->where('pagination.current_page', 2)
        );
    }

    #[Test]
    public function it_includes_categories_in_response()
    {
        Product::factory()->count(3)->forCategory($this->electronics)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('categories', 4) // 4 categories created in setUp
        );

        // Verify categories are sorted by name
        $response->assertInertia(fn (Assert $page) => $page
            ->where('categories.0.name', 'Clothing')
            ->where('categories.1.name', 'Electronics')
            ->where('categories.2.name', 'Home & Garden')
            ->where('categories.3.name', 'Sports')
        );
    }

    #[Test]
    public function it_loads_products_with_relationships()
    {
        $product = Product::factory()->forCategory($this->electronics)->create();
        Review::factory()->count(3)->for($product)->create();

        $response = $this->get('/');

        $response->assertStatus(200);

        // Verify the product has the category_color computed field
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products.0.category_color')
        );
    }

    #[Test]
    public function it_displays_single_product()
    {
        $product = Product::factory()->forCategory($this->electronics)->create();
        Review::factory()->count(3)->for($product)->create();

        $response = $this->get("/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Product')
            ->has('product')
            ->where('product.id', $product->id)
            ->where('product.name', $product->name)
        );
    }

    #[Test]
    public function it_returns_404_for_nonexistent_product()
    {
        $response = $this->get('/products/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_combines_filters_and_sorting()
    {
        $electronicsProduct1 = Product::factory()->forCategory($this->electronics)->create([
            'name' => 'Gaming Laptop',
            'price' => 100.00,
        ]);
        $electronicsProduct2 = Product::factory()->forCategory($this->electronics)->create([
            'name' => 'Office Laptop',
            'price' => 50.00,
        ]);
        $clothingProduct = Product::factory()->forCategory($this->clothing)->create([
            'name' => 'Gaming Shirt',
            'price' => 25.00,
        ]);

        $response = $this->get("/?category={$this->electronics->id}&search=laptop&sort=price-low");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 2)
            ->where('filters.category', (string) $this->electronics->id)
            ->where('filters.search', 'laptop')
            ->where('filters.sort', 'price-low')
        );

        // Verify only electronics products with "laptop" in name are returned, sorted by price
        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.0.name', 'Office Laptop')
            ->where('products.1.name', 'Gaming Laptop')
        );
    }

    #[Test]
    public function it_handles_empty_search_results()
    {
        Product::factory()->count(3)->forCategory($this->electronics)->create();

        $response = $this->get('/?search=nonexistentproduct');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 0)
            ->where('filters.search', 'nonexistentproduct')
        );
    }

    #[Test]
    public function it_handles_empty_category_results()
    {
        Product::factory()->count(3)->forCategory($this->electronics)->create();

        $response = $this->get('/?category=999');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 0)
            ->where('filters.category', '999')
        );
    }

    #[Test]
    public function it_preserves_filters_in_pagination()
    {
        // Create products in electronics category
        Product::factory()->count(30)->forCategory($this->electronics)->create();

        $response = $this->get('/?category='.$this->electronics->id.'&page=2');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->where('filters.category', (string) $this->electronics->id)
            ->where('pagination.current_page', 2)
        );
    }
}
