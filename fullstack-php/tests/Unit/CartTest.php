<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\Cart\Cart;
use App\DTO\Cart\CartItemCollection;
use App\DTO\Customer;
use App\DTO\PaymentDetails;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartTest extends TestCase
{
    #[Test]
    public function it_creates_cart_with_items()
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => 1,
            'name' => 'Product 1',
            'price' => 10.00,
            'quantity' => 2,
        ]);
        $cartItems->addItem([
            'id' => 2,
            'name' => 'Product 2',
            'price' => 15.00,
            'quantity' => 1,
        ]);

        $cart = new Cart(
            $cartItems,
            35.00, // subtotal
            7.00,  // tax
            5.99,  // shipping
            47.99, // total
            null,  // payment details
            null   // customer
        );

        $this->assertInstanceOf(CartItemCollection::class, $cart->getItems());
        $this->assertEquals(35.00, $cart->getSubtotal());
        $this->assertEquals(7.00, $cart->getTax());
        $this->assertEquals(5.99, $cart->getShipping());
        $this->assertEquals(47.99, $cart->getTotal());
        $this->assertFalse($cart->hasPaymentDetails());
        $this->assertFalse($cart->hasCustomer());
        $this->assertFalse($cart->isEmpty());
        $this->assertEquals(2, $cart->count());
    }

    #[Test]
    public function it_creates_cart_with_payment_details()
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => 1,
            'name' => 'Product 1',
            'price' => 10.00,
            'quantity' => 1,
        ]);

        $paymentDetails = new PaymentDetails(
            '1234567890123456',
            '12/25',
            '123'
        );

        $cart = new Cart(
            $cartItems,
            10.00, // subtotal
            2.00,  // tax
            5.99,  // shipping
            17.99, // total
            $paymentDetails,
            null   // customer
        );

        $this->assertTrue($cart->hasPaymentDetails());
        $this->assertInstanceOf(PaymentDetails::class, $cart->getPaymentDetails());
        $this->assertEquals('1234567890123456', $cart->getPaymentDetails()->cardNumber);
    }

    #[Test]
    public function it_creates_cart_with_customer()
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => 1,
            'name' => 'Product 1',
            'price' => 10.00,
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

        $cart = new Cart(
            $cartItems,
            10.00, // subtotal
            2.00,  // tax
            5.99,  // shipping
            17.99, // total
            null,  // payment details
            $customer
        );

        $this->assertTrue($cart->hasCustomer());
        $this->assertInstanceOf(Customer::class, $cart->getCustomer());
        $this->assertEquals('John Doe', $cart->getCustomer()->name);
        $this->assertEquals('john@example.com', $cart->getCustomer()->email);
    }

    #[Test]
    public function it_creates_empty_cart()
    {
        $cartItems = new CartItemCollection;

        $cart = new Cart(
            $cartItems,
            0.00, // subtotal
            0.00, // tax
            5.99, // shipping
            5.99, // total
            null, // payment details
            null  // customer
        );

        $this->assertTrue($cart->isEmpty());
        $this->assertEquals(0, $cart->count());
        $this->assertEquals(0.00, $cart->getSubtotal());
        $this->assertEquals(0.00, $cart->getTax());
        $this->assertEquals(5.99, $cart->getShipping());
        $this->assertEquals(5.99, $cart->getTotal());
    }

    #[Test]
    public function it_creates_complete_cart()
    {
        $cartItems = new CartItemCollection;
        $cartItems->addItem([
            'id' => 1,
            'name' => 'Product 1',
            'price' => 10.00,
            'quantity' => 2,
        ]);

        $paymentDetails = new PaymentDetails(
            '1234567890123456',
            '12/25',
            '123'
        );

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

        $cart = new Cart(
            $cartItems,
            20.00, // subtotal
            4.00,  // tax
            5.99,  // shipping
            29.99, // total
            $paymentDetails,
            $customer
        );

        $this->assertTrue($cart->hasPaymentDetails());
        $this->assertTrue($cart->hasCustomer());
        $this->assertFalse($cart->isEmpty());
        $this->assertEquals(1, $cart->count());
        $this->assertEquals(20.00, $cart->getSubtotal());
        $this->assertEquals(4.00, $cart->getTax());
        $this->assertEquals(5.99, $cart->getShipping());
        $this->assertEquals(29.99, $cart->getTotal());
    }
}
