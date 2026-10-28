<?php

namespace Tests\Feature;

use App\Enums\MatchStatus;
use App\Models\MatchGame;
use App\Models\Player;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_profile_bio_is_escaped(): void
    {
        $player = Player::where('name', 'Hadi')->firstOrFail();
        $payload = '<img src=x onerror="alert(1)">';

        $this->actingAs($this->user('user@padelio.id'))
            ->put(route('profile.update'), [
                'name' => 'Hadi',
                'city' => 'Yogyakarta',
                'age' => 28,
                'hand' => 'Kanan',
                'style' => 'All-round',
                'bio' => $payload,
            ])->assertSessionHasNoErrors();

        $response = $this->get(route('players.show', $player->fresh()));
        $response->assertDontSee($payload, false);
        $response->assertSee(e($payload), false);
    }

    public function test_profile_update_cannot_change_rating(): void
    {
        $player = Player::where('name', 'Hadi')->firstOrFail();
        $before = $player->rating;

        $this->actingAs($this->user('user@padelio.id'))
            ->put(route('profile.update'), [
                'name' => 'Hadi',
                'city' => 'Yogyakarta',
                'hand' => 'Kanan',
                'style' => 'All-round',
                'rating' => 5000,
            ]);

        $this->assertSame($before, $player->fresh()->rating);
    }

    public function test_registration_rejects_markup_in_name(): void
    {
        $this->post('/register', [
            'name' => '<script>alert(1)</script>',
            'email' => 'xss@padelio.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('users', ['email' => 'xss@padelio.id']);
    }

    public function test_regular_user_cannot_open_admin(): void
    {
        $this->actingAs($this->user('user@padelio.id'))->get('/admin')->assertForbidden();
    }

    public function test_guest_is_redirected_from_rent(): void
    {
        $this->get('/rent')->assertRedirect('/login');
    }

    public function test_user_cannot_confirm_match_they_are_not_in(): void
    {
        $andi = Player::where('name', 'Andi')->firstOrFail();
        $rio = Player::where('name', 'Rio')->firstOrFail();

        $match = MatchGame::create([
            'a_id' => $andi->id,
            'b_id' => $rio->id,
            'winner_id' => $andi->id,
            'status' => MatchStatus::PendingConfirm,
            'played_at' => now(),
        ]);

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('matches.confirm', $match))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $this->actingAs($this->user('admin@padelio.id'))->get('/admin')->assertOk();
    }
}
