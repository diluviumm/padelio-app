<?php

namespace Tests\Feature;

use App\Models\Bracket;
use App\Models\BracketMatch;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BracketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@padelio.id')->firstOrFail();
    }

    public function test_bracket_generation_requires_exact_participants(): void
    {
        $openEvent = Event::where('status', 'open')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.events.bracket.generate', $openEvent), ['size' => 8])
            ->assertSessionHasErrors('size');

        $this->assertDatabaseCount('brackets', 0);
    }

    public function test_bracket_is_generated_with_correct_rounds(): void
    {
        $event = Event::where('status', 'live')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.events.bracket.generate', $event), ['size' => 8])
            ->assertSessionHasNoErrors();

        $bracket = Bracket::where('event_id', $event->id)->firstOrFail();
        $this->assertSame(7, $bracket->matches()->count());
        $this->assertSame(4, $bracket->matches()->where('round', 1)->count());
        $this->assertSame(2, $bracket->matches()->where('round', 2)->count());
        $this->assertSame(1, $bracket->matches()->where('round', 3)->count());
    }

    public function test_winner_advances_to_next_round(): void
    {
        $event = Event::where('status', 'live')->firstOrFail();
        $this->actingAs($this->admin())->post(route('admin.events.bracket.generate', $event), ['size' => 8]);

        $bracket = Bracket::where('event_id', $event->id)->firstOrFail();
        $match = BracketMatch::where('bracket_id', $bracket->id)->where('round', 1)->where('position', 0)->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.events.bracket.advance', [$event, $match]), ['winner_id' => $match->a_id])
            ->assertSessionHasNoErrors();

        $next = BracketMatch::where('bracket_id', $bracket->id)->where('round', 2)->where('position', 0)->firstOrFail();
        $this->assertSame($match->a_id, $next->a_id);
        $this->assertSame($match->a_id, $match->fresh()->winner_id);
    }

    public function test_repick_clears_downstream_winner(): void
    {
        $event = Event::where('status', 'live')->firstOrFail();
        $this->actingAs($this->admin())->post(route('admin.events.bracket.generate', $event), ['size' => 8]);
        $bracket = Bracket::where('event_id', $event->id)->firstOrFail();

        $qf0 = BracketMatch::where('bracket_id', $bracket->id)->where('round', 1)->where('position', 0)->firstOrFail();
        $qf1 = BracketMatch::where('bracket_id', $bracket->id)->where('round', 1)->where('position', 1)->firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.events.bracket.advance', [$event, $qf0]), ['winner_id' => $qf0->a_id]);
        $this->actingAs($this->admin())->post(route('admin.events.bracket.advance', [$event, $qf1]), ['winner_id' => $qf1->a_id]);

        $sf = BracketMatch::where('bracket_id', $bracket->id)->where('round', 2)->where('position', 0)->firstOrFail();
        $this->assertNotNull($sf->a_id);
        $this->assertNotNull($sf->b_id);

        $this->actingAs($this->admin())->post(route('admin.events.bracket.advance', [$event, $sf]), ['winner_id' => $sf->a_id]);
        $this->assertNotNull($sf->fresh()->winner_id);

        $this->actingAs($this->admin())->post(route('admin.events.bracket.advance', [$event, $qf0]), ['winner_id' => $qf0->b_id]);

        $this->assertSame($qf0->b_id, $sf->fresh()->a_id);
        $this->assertNull($sf->fresh()->winner_id);
    }
}
