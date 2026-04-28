<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_item(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'price' => 29.99,
            'quantity' => 2,
            'total' => 59.98,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'quantity' => 2,
            'total' => 59.98,
        ]);
    }

    public function test_order_item_belongs_to_order(): void
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertEquals($order->id, $orderItem->order->id);
        $this->assertEquals($order->order_number, $orderItem->order->order_number);
    }

    public function test_order_item_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertEquals($product->id, $orderItem->product->id);
        $this->assertEquals($product->name, $orderItem->product->name);
    }

    public function test_order_item_total_calculation(): void
    {
        $orderItem = OrderItem::create([
            'order_id' => Order::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'product_name' => 'Test Product',
            'price' => 25.00,
            'quantity' => 3,
            'total' => 75.00,
        ]);

        $expectedTotal = 25.00 * 3;
        $this->assertEquals($expectedTotal, $orderItem->total);
    }

    public function test_order_item_casts(): void
    {
        $orderItem = OrderItem::factory()->create([
            'price' => '29.99',
            'quantity' => '2',
            'total' => '59.98',
        ]);

        $this->assertIsString($orderItem->price);
        $this->assertIsInt($orderItem->quantity);
        $this->assertIsString($orderItem->total);
    }

    public function test_order_item_quantity_validation(): void
    {
        $orderItem = OrderItem::factory()->create(['quantity' => 1]);

        $this->assertGreaterThan(0, $orderItem->quantity);
    }

    public function test_order_item_price_validation(): void
    {
        $orderItem = OrderItem::factory()->create(['price' => 10.00]);

        $this->assertGreaterThan(0, $orderItem->price);
    }
}
