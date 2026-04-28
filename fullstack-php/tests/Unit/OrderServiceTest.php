<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\Cart\Cart;
use App\DTO\Cart\CartItemCollection;
use App\DTO\Customer;
use App\DTO\PaymentDetails;
use App\DTO\PaymentResponse;
use App\Enum\PaymentMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Service\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService;
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    #[Test]
    public function it_creates_order_with_valid_cart()
    {
        $cart = $this->createValidCart();
        $paymentResponse = new PaymentResponse(true, 'Payment successful', 'TXN-123456');

        $order = $this->orderService->createOrder($cart, $this->user, $paymentResponse);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($this->user->id, $order->user_id);
        $this->assertEquals('John Doe', $order->shipping_name);
        $this->assertEquals('john@example.com', $order->shipping_email);
        $this->assertEquals(29.99, $order->subtotal);
        $this->assertEquals(6.00, $order->tax);
        $this->assertEquals(5.99, $order->shipping);
        $this->assertEquals(41.98, $order->total);
        $this->assertEquals(PaymentMethod::CARD, $order->payment_method);
        $this->assertEquals('confirmed', $order->status);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{6}$/', $order->order_number);
    }

    #[Test]
    public function it_creates_payment_record()
    {
        $cart = $this->createValidCart();
        $paymentResponse = new PaymentResponse(true, 'Payment successful', 'TXN-123456');

        $order = $this->orderService->createOrder($cart, $this->user, $paymentResponse);

        $this->assertDatabaseHas('payments', [
            'card_type' => 'Visa',
            'last_four_digits' => '1111',
            'expiry_month' => '12',
            'expiry_year' => '2025',
            'transaction_id' => 'TXN-123456',
            'status' => 'completed',
            'amount' => 41.98,
        ]);

        $this->assertNotNull($order->payment_id);
    }

    #[Test]
    public function it_creates_order_items()
    {
        $cart = $this->createValidCart();
        $paymentResponse = new PaymentResponse(true, 'Payment successful', 'TXN-123456');

        $order = $this->orderService->createOrder($cart, $this->user, $paymentResponse);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => 'Test Product',
            'price' => 29.99,
            'quantity' => 1,
            'total' => 29.99,
        ]);

        $this->assertEquals(1, $order->items->count());
    }

    #[Test]
    public function it_throws_exception_when_cart_has_no_customer()
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => $this->product->id,
            'name' => 'Test Product',
            'price' => 29.99,
            'quantity' => 1,
        ]);

        $cart = new Cart(
            $cartItems,
            29.99,
            5.998,
            5.99,
            41.978,
            null,
            null // No customer
        );

        $paymentResponse = new PaymentResponse(true, 'Payment successful', 'TXN-123456');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart must contain customer and payment information');

        $this->orderService->createOrder($cart, $this->user, $paymentResponse);
    }

    #[Test]
    public function it_handles_failed_payment_response()
    {
        $cart = $this->createValidCart();
        $paymentResponse = new PaymentResponse(false, 'Payment failed', 'TXN-123456');

        $order = $this->orderService->createOrder($cart, $this->user, $paymentResponse);

        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'TXN-123456',
            'status' => 'failed',
        ]);
    }

    #[Test]
    public function it_creates_order_with_multiple_items()
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => $this->product->id,
            'name' => 'Product 1',
            'price' => 10.00,
            'quantity' => 2,
        ]);
        $cartItems->addItem([
            'id' => $this->product->id,
            'name' => 'Product 2',
            'price' => 15.00,
            'quantity' => 1,
        ]);

        $customer = new Customer(
            'John Doe',
            'john@example.com',
            '1234567890',
            '123 Main St',
            'Apt 4B',
            'New York',
            '10001',
            'United States'
        );

        $paymentDetails = new PaymentDetails(
            '4111111111111111',
            '12/25',
            '123'
        );

        $cart = new Cart(
            $cartItems,
            35.00,
            7.00,
            5.99,
            47.99,
            $paymentDetails,
            $customer
        );

        $paymentResponse = new PaymentResponse(true, 'Payment successful', 'TXN-123456');

        $order = $this->orderService->createOrder($cart, $this->user, $paymentResponse);

        $this->assertEquals(2, $order->items->count());
        $this->assertDatabaseCount('order_items', 2);
    }

    #[Test]
    public function it_generates_unique_order_numbers()
    {
        $cart = $this->createValidCart();
        $paymentResponse = new PaymentResponse(true, 'Payment successful', 'TXN-123456');

        $order1 = $this->orderService->createOrder($cart, $this->user, $paymentResponse);
        $order2 = $this->orderService->createOrder($cart, $this->user, $paymentResponse);

        $this->assertNotEquals($order1->order_number, $order2->order_number);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{6}$/', $order1->order_number);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{6}$/', $order2->order_number);
    }

    private function createValidCart(): Cart
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => $this->product->id,
            'name' => 'Test Product',
            'price' => 29.99,
            'quantity' => 1,
        ]);

        $customer = new Customer(
            'John Doe',
            'john@example.com',
            '1234567890',
            '123 Main St',
            'Apt 4B',
            'New York',
            '10001',
            'United States'
        );

        $paymentDetails = new PaymentDetails(
            '4111111111111111',
            '12/25',
            '123'
        );

        return new Cart(
            $cartItems,
            29.99,
            6.00,
            5.99,
            41.98,
            $paymentDetails,
            $customer
        );
    }
}
