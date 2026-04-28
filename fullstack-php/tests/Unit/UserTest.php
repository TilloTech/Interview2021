<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_user()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function it_has_many_orders()
    {
        $user = User::factory()->create();
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Collection::class, $user->orders);
        $this->assertEquals(2, $user->orders->count());
        $this->assertTrue($user->orders->contains($order1));
        $this->assertTrue($user->orders->contains($order2));
    }

    #[Test]
    public function it_casts_email_verified_at_to_datetime()
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Set email_verified_at after creation since it's not in fillable
        $user->email_verified_at = '2023-01-01 12:00:00';
        $user->save();

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
        $this->assertEquals('2023-01-01 12:00:00', $user->email_verified_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_hashes_password()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintextpassword',
        ]);

        $this->assertNotEquals('plaintextpassword', $user->password);
        $this->assertTrue(Hash::check('plaintextpassword', $user->password));
    }

    #[Test]
    public function it_hides_sensitive_attributes()
    {
        $user = User::factory()->create();

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    #[Test]
    public function it_can_be_created_with_factory()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertTrue(Hash::check('password', $user->password)); // Default factory password
    }

    #[Test]
    public function it_can_be_updated()
    {
        $user = User::factory()->create();

        $user->update([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
    }

    #[Test]
    public function it_can_be_deleted()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    #[Test]
    public function it_has_fillable_fields()
    {
        $user = User::create([
            'name' => 'Fillable User',
            'email' => 'fillable@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->assertNotNull($user->id);
        $this->assertEquals('Fillable User', $user->name);
        $this->assertEquals('fillable@example.com', $user->email);
    }

    #[Test]
    public function it_can_have_null_email_verified_at()
    {
        $user = User::create([
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function it_extends_authenticatable()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Foundation\Auth\User::class, $user);
        $this->assertInstanceOf(Authenticatable::class, $user);
    }

    #[Test]
    public function it_uses_notifiable_trait()
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'notifications'));
        $this->assertTrue(method_exists($user, 'readNotifications'));
        $this->assertTrue(method_exists($user, 'unreadNotifications'));
    }

    #[Test]
    public function it_can_have_remember_token()
    {
        $user = User::factory()->create();
        $token = 'remember-token-123';

        $user->remember_token = $token;
        $user->save();

        $this->assertEquals($token, $user->remember_token);
    }
}
