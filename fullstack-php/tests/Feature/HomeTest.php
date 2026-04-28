<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_home_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
        );
    }

    #[Test]
    public function it_displays_home_page_with_products()
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(5)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 5)
            ->has('categories')
        );
    }

    #[Test]
    public function it_displays_home_page_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
        );
    }

    #[Test]
    public function it_displays_home_page_for_guest_user()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
        );
    }

    #[Test]
    public function it_loads_categories_on_home_page()
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('categories', 3)
        );
    }

    #[Test]
    public function it_handles_empty_product_catalog()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalogue')
            ->has('products', 0)
        );
    }
}
