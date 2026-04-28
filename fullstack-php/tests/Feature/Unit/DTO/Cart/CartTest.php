<?php

declare(strict_types=1);

namespace Tests\Feature\Unit\DTO\Cart;

use App\DTO\Cart\Cart;
use App\DTO\Cart\CartItemCollection;
use App\DTO\Customer;
use App\DTO\PaymentDetails;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartTest extends TestCase
{
    private Cart $cart;

    private CartItemCollection $items;

    private Customer $customer;

    private PaymentDetails $paymentDetails;

    protected function setUp(): void
    {
        parent::setUp();

        $this->items = new CartItemCollection;
        $this->items->addItem([
            'product_id' => 1,
            'name' => 'Test Product',
            'quantity' => 2,
            'price' => 10.00,
        ]);
        $this->items->addItem([
            'product_id' => 2,
            'name' => 'Another Product',
            'quantity' => 1,
            'price' => 15.00,
        ]);

        $this->customer = new Customer(
            'John Doe',
            'john@example.com',
            '123-456-7890',
            '123 Main St',
            'Apt 4B',
            'New York',
            '10001',
            'United States'
        );

        $this->paymentDetails = new PaymentDetails(
            '4111111111111111',
            '12/25',
            '123'
        );

        // Calculate values
        $subtotal = 35.00; // (2 * 10.00) + (1 * 15.00)
        $tax = $subtotal * 0.20; // 7.00
        $shipping = 5.99;
        $total = $subtotal + $tax + $shipping; // 47.99

        $this->cart = new Cart(
            $this->items,
            $subtotal,
            $tax,
            $shipping,
            $total,
            $this->paymentDetails,
            $this->customer
        );
    }

    #[Test]
    public function it_creates_cart_with_valid_data()
    {
        $this->assertInstanceOf(Cart::class, $this->cart);
        $this->assertSame($this->items, $this->cart->getItems());
        $this->assertSame($this->customer, $this->cart->getCustomer());
        $this->assertSame($this->paymentDetails, $this->cart->getPaymentDetails());
    }

    #[Test]
    public function it_returns_correct_subtotal()
    {
        $expectedSubtotal = 35.00;

        $this->assertEquals($expectedSubtotal, $this->cart->getSubtotal());
    }

    #[Test]
    public function it_returns_correct_tax()
    {
        $expectedTax = 7.00; // 20% of 35.00

        $this->assertEquals($expectedTax, $this->cart->getTax());
    }

    #[Test]
    public function it_returns_correct_shipping_cost()
    {
        $expectedShipping = 5.99;

        $this->assertEquals($expectedShipping, $this->cart->getShipping());
    }

    #[Test]
    public function it_returns_correct_total()
    {
        $expectedTotal = 47.99; // 35.00 + 7.00 + 5.99

        $this->assertEquals($expectedTotal, $this->cart->getTotal());
    }

    #[Test]
    public function it_handles_empty_cart()
    {
        $emptyItems = new CartItemCollection;
        $emptyCart = new Cart($emptyItems, 0.00, 0.00, 5.99, 5.99, $this->paymentDetails, $this->customer);

        $this->assertEquals(0.00, $emptyCart->getSubtotal());
        $this->assertEquals(0.00, $emptyCart->getTax());
        $this->assertEquals(5.99, $emptyCart->getShipping());
        $this->assertEquals(5.99, $emptyCart->getTotal());
        $this->assertTrue($emptyCart->isEmpty());
        $this->assertEquals(0, $emptyCart->count());
    }

    #[Test]
    public function it_handles_cart_with_zero_price_items()
    {
        $zeroPriceItems = new CartItemCollection;
        $zeroPriceItems->addItem([
            'product_id' => 1,
            'name' => 'Free Product',
            'quantity' => 1,
            'price' => 0.00,
        ]);

        $zeroPriceCart = new Cart($zeroPriceItems, 0.00, 0.00, 5.99, 5.99, $this->paymentDetails, $this->customer);

        $this->assertEquals(0.00, $zeroPriceCart->getSubtotal());
        $this->assertEquals(0.00, $zeroPriceCart->getTax());
        $this->assertEquals(5.99, $zeroPriceCart->getShipping());
        $this->assertEquals(5.99, $zeroPriceCart->getTotal());
    }

    #[Test]
    public function it_handles_large_quantities()
    {
        $largeQuantityItems = new CartItemCollection;
        $largeQuantityItems->addItem([
            'product_id' => 1,
            'name' => 'Bulk Product',
            'quantity' => 100,
            'price' => 1.50,
        ]);

        $expectedSubtotal = 100 * 1.50; // 150.00
        $expectedTax = $expectedSubtotal * 0.20; // 30.00
        $expectedTotal = $expectedSubtotal + $expectedTax + 5.99; // 185.99

        $largeQuantityCart = new Cart($largeQuantityItems, $expectedSubtotal, $expectedTax, 5.99, $expectedTotal, $this->paymentDetails, $this->customer);

        $this->assertEquals($expectedSubtotal, $largeQuantityCart->getSubtotal());
        $this->assertEquals($expectedTax, $largeQuantityCart->getTax());
        $this->assertEquals($expectedTotal, $largeQuantityCart->getTotal());
    }

    #[Test]
    public function it_handles_decimal_prices()
    {
        $decimalItems = new CartItemCollection;
        $decimalItems->addItem([
            'product_id' => 1,
            'name' => 'Decimal Product',
            'quantity' => 3,
            'price' => 3.33,
        ]);

        $expectedSubtotal = 3 * 3.33; // 9.99
        $expectedTax = $expectedSubtotal * 0.20; // 1.998
        $expectedTotal = $expectedSubtotal + $expectedTax + 5.99; // 17.978

        $decimalCart = new Cart($decimalItems, $expectedSubtotal, $expectedTax, 5.99, $expectedTotal, $this->paymentDetails, $this->customer);

        $this->assertEquals($expectedSubtotal, $decimalCart->getSubtotal());
        $this->assertEquals($expectedTax, $decimalCart->getTax(), '', 0.01);
        $this->assertEquals($expectedTotal, $decimalCart->getTotal(), '', 0.01);
    }

    #[Test]
    public function it_returns_correct_customer_information()
    {
        $customer = $this->cart->getCustomer();

        $this->assertEquals('John Doe', $customer->name);
        $this->assertEquals('john@example.com', $customer->email);
        $this->assertEquals('123-456-7890', $customer->phone);
        $this->assertEquals('123 Main St', $customer->address);
        $this->assertEquals('Apt 4B', $customer->address2);
        $this->assertEquals('New York', $customer->city);
        $this->assertEquals('10001', $customer->postcode);
        $this->assertEquals('United States', $customer->country);
    }

    #[Test]
    public function it_returns_correct_payment_information()
    {
        $paymentDetails = $this->cart->getPaymentDetails();

        $this->assertEquals('4111111111111111', $paymentDetails->cardNumber);
        $this->assertEquals('12/25', $paymentDetails->expiryDate);
        $this->assertEquals('123', $paymentDetails->cvv);
    }

    #[Test]
    public function it_handles_cart_with_null_customer()
    {
        $cartWithoutCustomer = new Cart($this->items, 35.00, 7.00, 5.99, 47.99, $this->paymentDetails, null);

        $this->assertNull($cartWithoutCustomer->getCustomer());
        $this->assertSame($this->items, $cartWithoutCustomer->getItems());
        $this->assertSame($this->paymentDetails, $cartWithoutCustomer->getPaymentDetails());
        $this->assertFalse($cartWithoutCustomer->hasCustomer());
    }

    #[Test]
    public function it_handles_cart_with_null_payment_details()
    {
        $cartWithoutPayment = new Cart($this->items, 35.00, 7.00, 5.99, 47.99, null, $this->customer);

        $this->assertNull($cartWithoutPayment->getPaymentDetails());
        $this->assertSame($this->items, $cartWithoutPayment->getItems());
        $this->assertSame($this->customer, $cartWithoutPayment->getCustomer());
        $this->assertFalse($cartWithoutPayment->hasPaymentDetails());
    }

    #[Test]
    public function it_returns_correct_item_count()
    {
        $this->assertEquals(2, $this->cart->count());
        $this->assertFalse($this->cart->isEmpty());
    }

    #[Test]
    public function it_handles_cart_with_customer_and_payment()
    {
        $this->assertTrue($this->cart->hasCustomer());
        $this->assertTrue($this->cart->hasPaymentDetails());
    }
}
