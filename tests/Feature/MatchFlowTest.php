<?php

namespace Tests\Feature;

use App\Enums\ChallengeStatus;
use App\Enums\MatchStatus;
use App\Models\Challenge;
use App\Models\MatchGame;
use App\Models\Player;
use App\Models\User;
use App\Services\MatchService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchFlowTest extends TestCase
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

    private function scheduledChallenge(): Challenge
    {
        return Challenge::where('status', ChallengeStatus::Scheduled)->firstOrFail();
    }

    public function test_player_can_submit_score(): void
    {
        $challenge = $this->scheduledChallenge();

        $this->actingAs($this->user('andi@padelio.id'))
            ->post(route('matches.score.store', $challenge), [
                'sets' => [['a' => 6, 'b' => 3], ['a' => 6, 'b' => 4]],
            ])->assertRedirect(route('matches.index'));

        $match = MatchGame::latest('id')->firstOrFail();
        $this->assertSame(MatchStatus::PendingConfirm, $match->status);
        $this->assertSame($challenge->from_player_id, $match->winner_id);
        $this->assertSame('6-3 6-4', $match->scoreLabel());
        $this->assertSame(ChallengeStatus::Done, $challenge->fresh()->status);
    }

    public function test_tied_set_is_rejected(): void
    {
        $challenge = $this->scheduledChallenge();

        $this->actingAs($this->user('andi@padelio.id'))
            ->post(route('matches.score.store', $challenge), [
                'sets' => [['a' => 6, 'b' => 6], ['a' => 6, 'b' => 4]],
            ])->assertSessionHasErrors('sets');
    }

    public function test_both_confirmations_move_match_to_pending(): void
    {
        $challenge = $this->scheduledChallenge();
        $this->actingAs($this->user('andi@padelio.id'))->post(route('matches.score.store', $challenge), [
            'sets' => [['a' => 6, 'b' => 3], ['a' => 6, 'b' => 4]],
        ]);
        $match = MatchGame::latest('id')->firstOrFail();

        $this->actingAs($this->user('user@padelio.id'))->post(route('matches.confirm', $match))->assertSessionHasNoErrors();
        $this->assertSame(MatchStatus::PendingConfirm, $match->fresh()->status);

        $this->actingAs($this->user('andi@padelio.id'))->post(route('matches.confirm', $match))->assertSessionHasNoErrors();
        $this->assertSame(MatchStatus::Pending, $match->fresh()->status);
    }

    public function test_admin_approves_and_elo_is_applied_once(): void
    {
        $challenge = $this->scheduledChallenge();
        $this->actingAs($this->user('andi@padelio.id'))->post(route('matches.score.store', $challenge), [
            'sets' => [['a' => 6, 'b' => 3], ['a' => 6, 'b' => 4]],
        ]);
        $match = MatchGame::latest('id')->firstOrFail();

        $this->actingAs($this->user('user@padelio.id'))->post(route('matches.confirm', $match));
        $this->actingAs($this->user('andi@padelio.id'))->post(route('matches.confirm', $match));

        $hadiBefore = Player::where('name', 'Hadi')->value('rating');

        $this->actingAs($this->user('admin@padelio.id'))
            ->post(route('admin.matches.approve', $match))
            ->assertSessionHasNoErrors();

        $match->refresh();
        $this->assertSame(MatchStatus::Approved, $match->status);
        $this->assertSame(40, $match->delta_a);
        $this->assertSame($hadiBefore + 40, Player::where('name', 'Hadi')->value('rating'));

        $afterFirst = Player::where('name', 'Hadi')->value('rating');
        app(MatchService::class)->approve($match->fresh(), $this->user('admin@padelio.id'));
        $this->assertSame($afterFirst, Player::where('name', 'Hadi')->value('rating'));
    }

    public function test_admin_cannot_approve_before_both_confirmations(): void
    {
        $match = MatchGame::where('status', MatchStatus::PendingConfirm)->firstOrFail();

        $this->actingAs($this->user('admin@padelio.id'))
            ->post(route('admin.matches.approve', $match))
            ->assertForbidden();
    }

    public function test_other_player_cannot_confirm_match(): void
    {
        $match = MatchGame::where('status', MatchStatus::PendingConfirm)->firstOrFail();

        $this->actingAs($this->user('andi@padelio.id'))
            ->post(route('matches.confirm', $match))
            ->assertForbidden();
    }
}
