<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        RateLimiter::clear('login:user@padelio.id|127.0.0.1');
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::where('email', 'user@padelio.id')->firstOrFail();

        $this->post('/login', ['email' => 'user@padelio.id', 'password' => 'user12345'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->post('/login', ['email' => 'user@padelio.id', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_register_creates_user_and_player_and_ignores_role(): void
    {
        $this->post('/register', [
            'name' => 'Pemain Baru',
            'email' => 'baru@padelio.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'city' => 'Bandung',
            'role' => 'admin',
        ])->assertRedirect('/dashboard');

        $user = User::where('email', 'baru@padelio.id')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertDatabaseHas('players', ['user_id' => $user->id, 'rating' => 1200, 'level' => 'upper-beginner']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'user@padelio.id', 'password' => 'salah']);
        }

        $this->post('/login', ['email' => 'user@padelio.id', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Terlalu banyak',
            session('errors')->first('email')
        );
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::where('email', 'user@padelio.id')->firstOrFail();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
