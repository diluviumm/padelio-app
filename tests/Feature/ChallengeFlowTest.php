<?php

namespace Tests\Feature;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\Player;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeFlowTest extends TestCase
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

    public function test_recipient_can_accept_challenge(): void
    {
        $challenge = Challenge::where('status', ChallengeStatus::PendingAccept)
            ->where('to_player_id', Player::where('name', 'Hadi')->value('id'))
            ->firstOrFail();

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('challenges.accept', $challenge))
            ->assertSessionHasNoErrors();

        $this->assertSame(ChallengeStatus::PendingSchedule, $challenge->fresh()->status);
    }

    public function test_sender_cannot_accept_own_challenge(): void
    {
        $challenge = Challenge::where('from_player_id', Player::where('name', 'Rio')->value('id'))->firstOrFail();

        $this->actingAs($this->user('rio@padelio.id'))
            ->post(route('challenges.accept', $challenge))
            ->assertForbidden();
    }

    public function test_challenge_can_be_scheduled(): void
    {
        $challenge = Challenge::where('status', ChallengeStatus::PendingAccept)->whereHas('toPlayer', fn ($q) => $q->where('name', 'Hadi'))->firstOrFail();

        $this->actingAs($this->user('user@padelio.id'))->post(route('challenges.accept', $challenge));

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('challenges.schedule', $challenge), [
                'date' => now()->addDays(3)->toDateString(),
                'time' => '09:00',
                'court_id' => \App\Models\Court::where('name', 'GBK Lap 4')->value('id'),
            ])->assertSessionHasNoErrors();

        $fresh = $challenge->fresh();
        $this->assertSame(ChallengeStatus::Scheduled, $fresh->status);
        $this->assertSame('09:00', $fresh->scheduled_at->format('H:i'));
    }

    public function test_scheduling_a_taken_slot_is_rejected(): void
    {
        $challenge = Challenge::where('status', ChallengeStatus::PendingAccept)->whereHas('toPlayer', fn ($q) => $q->where('name', 'Hadi'))->firstOrFail();
        $this->actingAs($this->user('user@padelio.id'))->post(route('challenges.accept', $challenge));

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('challenges.schedule', $challenge), [
                'date' => now()->addDay()->toDateString(),
                'time' => '10:00',
                'court_id' => \App\Models\Booking::where('code', 'BK-DEMO0001')->value('court_id'),
            ])->assertSessionHasErrors('time');
    }

    public function test_active_challenge_limit_is_enforced(): void
    {
        $opponent = Player::where('name', 'Budi')->firstOrFail();

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('challenges.store'), ['opponent_id' => $opponent->id, 'note' => 'tes'])
            ->assertSessionHasErrors('opponent_id');

        $active = [ChallengeStatus::PendingAccept, ChallengeStatus::PendingSchedule, ChallengeStatus::Scheduled];
        $this->assertSame(0, Challenge::where('from_player_id', $opponent->id)
            ->where('to_player_id', Player::where('name', 'Hadi')->value('id'))
            ->whereIn('status', $active)
            ->count());
    }

    public function test_duplicate_challenge_is_rejected(): void
    {
        $hadi = Player::where('name', 'Hadi')->firstOrFail();
        $rio = Player::where('name', 'Rio')->firstOrFail();

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('challenges.store'), ['opponent_id' => $rio->id, 'note' => 'lagi'])
            ->assertSessionHasErrors('opponent_id');
    }
}
