<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    private function getTestOrderData(): array
    {
        return [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_phone' => '123-456-7890',
            'shipping_address' => '123 Main St',
            'shipping_address2' => 'Apt 4B',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'card_number' => '4111111111111111',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'cart_items' => [
                [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => 29.99,
                    'quantity' => 1,
                    'image' => 'product.jpg',
                ],
            ],
        ];
    }

    #[Test]
    public function it_displays_checkout_page()
    {
        $this->actingAs($this->user);

        $response = $this->get('/checkout');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Checkout')
        );
    }

    #[Test]
    public function it_redirects_guest_to_login()
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function it_creates_order_with_valid_data()
    {
        $this->actingAs($this->user);

        $orderData = $this->getTestOrderData();

        $response = $this->post('/checkout', $orderData);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_phone' => '123-456-7890',
            'shipping_address' => '123 Main St',
            'shipping_address2' => 'Apt 4B',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'subtotal' => 29.99,
            'tax' => 5.998,
            'shipping' => 5.99,
            'total' => 41.978,
        ]);

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'last_four_digits' => '1111',
            'card_type' => 'Visa',
            'amount' => 41.978,
        ]);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $this->actingAs($this->user);

        $response = $this->post('/checkout', []);

        $response->assertSessionHasErrors([
            'shipping_name',
            'shipping_email',
            'shipping_address',
            'shipping_city',
            'shipping_postcode',
            'shipping_country',
            'card_number',
            'expiry_date',
            'cvv',
            'cart_items',
        ]);
    }

    #[Test]
    public function it_validates_card_number_format()
    {
        $this->actingAs($this->user);

        $response = $this->post('/checkout', [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'card_number' => '1234', // Too short
            'expiry_date' => '12/25',
            'cvv' => '123',
            'cart_items' => [[
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 10,
                'quantity' => 1,
            ]],
        ]);

        $response->assertSessionHasErrors(['card_number']);
    }

    #[Test]
    public function it_validates_expiry_date_format()
    {
        $this->actingAs($this->user);

        $response = $this->post('/checkout', [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25/2025', // Wrong format
            'cvv' => '123',
            'cart_items' => [[
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 10,
                'quantity' => 1,
            ]],
        ]);

        $response->assertSessionHasErrors(['expiry_date']);
    }

    #[Test]
    public function it_validates_cvv_format()
    {
        $this->actingAs($this->user);

        $response = $this->post('/checkout', [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '12', // Too short
            'cart_items' => [[
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 10,
                'quantity' => 1,
            ]],
        ]);

        $response->assertSessionHasErrors(['cvv']);
    }

    #[Test]
    public function it_validates_cart_items_are_required()
    {
        $this->actingAs($this->user);

        $response = $this->post('/checkout', [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '123',
            'cart_items' => [], // Empty cart
        ]);

        $response->assertSessionHasErrors(['cart_items']);
    }

    #[Test]
    public function it_creates_multiple_order_items()
    {
        $this->actingAs($this->user);

        $orderData = $this->getTestOrderData();
        $orderData['cart_items'] = [
            [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 10.00,
                'quantity' => 2,
                'image' => 'product1.jpg',
            ],
            [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 15.00,
                'quantity' => 1,
                'image' => 'product2.jpg',
            ],
        ];

        $response = $this->post('/checkout', $orderData);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify order was created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);

        // Verify both order items were created
        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
        ]);
    }

    #[Test]
    public function it_generates_unique_order_number()
    {
        $this->actingAs($this->user);

        $orderData = $this->getTestOrderData();

        $response = $this->post('/checkout', $orderData);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $order = Order::where('user_id', $this->user->id)->first();

        // Verify order number format
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{6}$/', $order->order_number);

        // Verify order number is unique
        $this->assertDatabaseCount('orders', 1);
    }

    #[Test]
    public function it_displays_order_confirmation_page()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-20250101-ABC123',
        ]);

        $response = $this->get("/checkout/confirmation/{$order->order_number}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('OrderConfirmation')
            ->has('order')
        );
    }

    #[Test]
    public function it_loads_order_items_in_confirmation()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-20250101-ABC123',
        ]);

        $response = $this->get("/checkout/confirmation/{$order->order_number}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('OrderConfirmation')
            ->has('order')
            ->has('order.items')
        );
    }

    #[Test]
    public function it_returns_404_for_nonexistent_order()
    {
        $this->actingAs($this->user);

        $response = $this->get('/checkout/confirmation/ORD-20250101-ABC123');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_redirects_guest_from_confirmation_to_login()
    {
        $response = $this->get('/checkout/confirmation/ORD-20250101-ABC123');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function it_redirects_to_confirmation_after_successful_order()
    {
        $this->actingAs($this->user);

        $orderData = $this->getTestOrderData();

        $response = $this->post('/checkout', $orderData);

        $response->assertRedirect();

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertStringContainsString("/checkout/confirmation/{$order->order_number}", $response->getTargetUrl());
    }

    #[Test]
    public function it_allows_multiple_orders_to_be_processed_sequentially()
    {
        $this->actingAs($this->user);

        $orderData = $this->getTestOrderData();

        // Submit first order
        $response1 = $this->post('/checkout', $orderData);
        $response1->assertRedirect();
        $response1->assertSessionHasNoErrors();

        // Submit second order (should be allowed since first completed)
        $response2 = $this->post('/checkout', $orderData);
        $response2->assertRedirect();
        $response2->assertSessionHasNoErrors();

        // Verify both orders were created
        $this->assertDatabaseCount('orders', 2);
    }
}
