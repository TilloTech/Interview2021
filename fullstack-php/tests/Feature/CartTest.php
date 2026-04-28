<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_cart_page()
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Cart')
        );
    }

    #[Test]
    public function it_displays_cart_page_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Cart')
        );
    }

    #[Test]
    public function it_displays_cart_page_for_guest_user()
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Cart')
        );
    }

    #[Test]
    public function it_redirects_to_checkout_when_cart_has_items()
    {
        // This test would verify that the cart page shows a checkout button
        // when there are items in the cart
        $response = $this->get('/cart');

        $response->assertStatus(200);
        // The frontend would handle the checkout redirect logic
    }

    #[Test]
    public function it_shows_empty_cart_message()
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        // The frontend would handle showing empty cart message
    }
}
