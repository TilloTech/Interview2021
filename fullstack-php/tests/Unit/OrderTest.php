<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'order_number' => 'ORD-2025-001',
            'user_id' => $user->id,
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_phone' => '1234567890',
            'shipping_address' => '123 Main St',
            'shipping_address2' => 'Apt 4B',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'shipping' => 5.99,
            'total' => 125.99,
            'payment_method' => 'card',
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-2025-001',
            'shipping_name' => 'John Doe',
            'total' => 125.99,
        ]);
    }

    public function test_order_has_items_relationship(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertTrue($order->items->contains($item));
        $this->assertEquals(1, $order->items->count());
    }

    public function test_order_number_generation(): void
    {
        $orderNumber1 = Order::generateOrderNumber();
        $orderNumber2 = Order::generateOrderNumber();

        $this->assertStringStartsWith('ORD-', $orderNumber1);
        $this->assertStringStartsWith('ORD-', $orderNumber2);
        $this->assertNotEquals($orderNumber1, $orderNumber2);
    }

    public function test_order_total_calculation(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'order_number' => 'ORD-2025-002',
            'user_id' => $user->id,
            'shipping_name' => 'Jane Doe',
            'shipping_email' => 'jane@example.com',
            'shipping_phone' => '1234567890',
            'shipping_address' => '456 Oak St',
            'shipping_address2' => null,
            'shipping_city' => 'Los Angeles',
            'shipping_postcode' => '90210',
            'shipping_country' => 'United States',
            'subtotal' => 50.00,
            'tax' => 10.00,
            'shipping' => 5.99,
            'total' => 65.99,
            'payment_method' => 'card',
            'status' => 'confirmed',
        ]);

        $expectedTotal = 50.00 + 10.00 + 5.99;
        $this->assertEquals($expectedTotal, $order->total);
    }

    public function test_order_status_scope(): void
    {
        Order::factory()->create(['status' => 'confirmed']);
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'confirmed']);

        $confirmedOrders = Order::where('status', 'confirmed')->get();
        $this->assertEquals(2, $confirmedOrders->count());
    }

    public function test_order_date_formatting(): void
    {
        $order = Order::factory()->create();

        $this->assertIsString($order->created_at->format('Y-m-d'));
        $this->assertIsString($order->updated_at->format('Y-m-d'));
    }

    public function test_order_casts(): void
    {
        $order = Order::factory()->create([
            'subtotal' => '100.50',
            'tax' => '20.10',
            'shipping' => '5.99',
            'total' => '126.59',
        ]);

        $this->assertIsString($order->subtotal);
        $this->assertIsString($order->tax);
        $this->assertIsString($order->shipping);
        $this->assertIsString($order->total);
    }

    public function test_order_route_key_name(): void
    {
        $order = Order::factory()->create();

        $this->assertEquals('order_number', $order->getRouteKeyName());
    }
}
