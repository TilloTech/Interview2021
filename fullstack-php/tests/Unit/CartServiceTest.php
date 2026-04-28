<?php

namespace Tests\Unit;

use App\DTO\Cart\Cart;
use App\DTO\Cart\CartItem;
use App\DTO\Cart\CartItemCollection;
use App\DTO\Customer;
use App\DTO\PaymentDetails;
use App\Http\Requests\StoreOrderRequest;
use App\Service\CartService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    private function makeRequest(array $cartItemsData = [], array $paymentData = []): StoreOrderRequest
    {
        $data = [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_phone' => '1234567890',
            'shipping_address' => '123 Main St',
            'shipping_address2' => 'Apt 4B',
            'shipping_city' => 'New York',
            'shipping_postcode' => '10001',
            'shipping_country' => 'United States',
            'cart_items' => $cartItemsData,
        ];
        if ($paymentData) {
            $data = array_merge($data, $paymentData);
        }
        // Use Request::create to simulate a POST request
        $request = StoreOrderRequest::create('/checkout', 'POST', $data);
        $request->setContainer(app());

        return $request;
    }

    #[Test]
    public function it_creates_cart_with_items()
    {
        $cartService = new CartService;
        $cartItemsData = [
            [
                'id' => 1,
                'name' => 'Test Product',
                'price' => 10.00,
                'quantity' => 2,
            ],
            [
                'id' => 2,
                'name' => 'Another Product',
                'price' => 15.00,
                'quantity' => 1,
            ],
        ];
        $request = $this->makeRequest($cartItemsData);
        $cart = $cartService->createCartFromRequest($request);
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertInstanceOf(CartItemCollection::class, $cart->getItems());
        $this->assertEquals(2, $cart->count());
        $this->assertEquals(35.00, $cart->getSubtotal());
        $this->assertEquals(7.00, $cart->getTax());
        $this->assertEquals(5.99, $cart->getShipping());
        $this->assertEquals(47.99, $cart->getTotal());
        $this->assertFalse($cart->hasPaymentDetails());
    }

    #[Test]
    public function it_creates_cart_with_payment_details()
    {
        $cartService = new CartService;
        $cartItemsData = [
            [
                'id' => 1,
                'name' => 'Test Product',
                'price' => 10.00,
                'quantity' => 1,
            ],
        ];
        $paymentData = [
            'card_number' => '1234567890123456',
            'expiry_date' => '12/25',
            'cvv' => '123',
        ];
        $request = $this->makeRequest($cartItemsData, $paymentData);
        $cart = $cartService->createCartFromRequest($request);
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertTrue($cart->hasPaymentDetails());
        $paymentDetails = $cart->getPaymentDetails();
        $this->assertInstanceOf(PaymentDetails::class, $paymentDetails);
        $this->assertEquals('1234567890123456', $paymentDetails->cardNumber);
        $this->assertEquals('12/25', $paymentDetails->expiryDate);
        $this->assertEquals('123', $paymentDetails->cvv);
    }

    #[Test]
    public function it_creates_empty_cart()
    {
        $cartService = new CartService;
        $request = $this->makeRequest();
        $cart = $cartService->createCartFromRequest($request);
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertTrue($cart->isEmpty());
        $this->assertEquals(0, $cart->count());
        $this->assertEquals(0.00, $cart->getSubtotal());
        $this->assertEquals(0.00, $cart->getTax());
        $this->assertEquals(5.99, $cart->getShipping());
        $this->assertEquals(5.99, $cart->getTotal());
        $this->assertFalse($cart->hasPaymentDetails());
    }

    #[Test]
    public function it_creates_cart_with_customer_details()
    {
        $cartService = new CartService;
        $cartItemsData = [
            [
                'id' => 1,
                'name' => 'Test Product',
                'price' => 10.00,
                'quantity' => 1,
            ],
        ];
        $request = $this->makeRequest($cartItemsData);
        $cart = $cartService->createCartFromRequest($request);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertTrue($cart->hasCustomer());

        $customer = $cart->getCustomer();
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertEquals('john@example.com', $customer->email);
        $this->assertEquals('1234567890', $customer->phone);
        $this->assertEquals('123 Main St', $customer->address);
        $this->assertEquals('Apt 4B', $customer->address2);
        $this->assertEquals('New York', $customer->city);
        $this->assertEquals('10001', $customer->postcode);
        $this->assertEquals('United States', $customer->country);
    }

    #[Test]
    public function cart_items_add_method_works()
    {
        $cartItems = new CartItemCollection;
        $this->assertTrue($cartItems->isEmpty());
        $this->assertEquals(0, $cartItems->count());
        $cartItems->addItem([
            'id' => 1,
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 2,
        ]);
        $this->assertFalse($cartItems->isEmpty());
        $this->assertEquals(1, $cartItems->count());
        $this->assertEquals(20.00, $cartItems->getSubtotal());
        $cartItems->addItem([
            'id' => 2,
            'name' => 'Another Product',
            'price' => 15.00,
            'quantity' => 1,
        ]);
        $this->assertEquals(2, $cartItems->count());
        $this->assertEquals(35.00, $cartItems->getSubtotal());
    }

    #[Test]
    public function cart_items_get_items_returns_array()
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => 1,
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 2,
        ]);
        $cartItems->addItem([
            'id' => 2,
            'name' => 'Another Product',
            'price' => 15.00,
            'quantity' => 1,
        ]);

        $items = $cartItems->getItems();
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertInstanceOf(CartItem::class, $items[0]);
        $this->assertInstanceOf(CartItem::class, $items[1]);
        $this->assertEquals('Test Product', $items[0]->name);
        $this->assertEquals('Another Product', $items[1]->name);
    }
}
