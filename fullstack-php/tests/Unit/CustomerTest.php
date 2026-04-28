<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\Customer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    #[Test]
    public function it_creates_customer_with_all_properties()
    {
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
    public function it_creates_customer_with_null_phone()
    {
        $customer = new Customer(
            'Jane Smith',
            'jane@example.com',
            null,
            '456 Oak Ave',
            null,
            'Los Angeles',
            '90210',
            'United States'
        );

        $this->assertEquals('Jane Smith', $customer->name);
        $this->assertEquals('jane@example.com', $customer->email);
        $this->assertNull($customer->phone);
        $this->assertEquals('456 Oak Ave', $customer->address);
        $this->assertNull($customer->address2);
        $this->assertEquals('Los Angeles', $customer->city);
        $this->assertEquals('90210', $customer->postcode);
        $this->assertEquals('United States', $customer->country);
    }

    #[Test]
    public function it_converts_to_array()
    {
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

        $array = $customer->toArray();

        $this->assertEquals([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'address2' => 'Apt 4B',
            'city' => 'New York',
            'postcode' => '10001',
            'country' => 'United States',
        ], $array);
    }

    #[Test]
    public function it_converts_to_array_with_null_phone()
    {
        $customer = new Customer(
            'Jane Smith',
            'jane@example.com',
            null,
            '456 Oak Ave',
            null,
            'Los Angeles',
            '90210',
            'United States'
        );

        $array = $customer->toArray();

        $this->assertEquals([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => null,
            'address' => '456 Oak Ave',
            'address2' => null,
            'city' => 'Los Angeles',
            'postcode' => '90210',
            'country' => 'United States',
        ], $array);
    }

    #[Test]
    public function it_has_readonly_properties()
    {
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

        // Properties should be readonly and not modifiable
        $this->expectException(\Error::class);
        $customer->name = 'Jane Smith';
    }
}
