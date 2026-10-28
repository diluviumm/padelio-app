<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Player;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
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

    public function test_player_can_register_for_open_event(): void
    {
        $event = Event::where('status', 'open')->firstOrFail();

        $this->actingAs($this->user('andi@padelio.id'))
            ->post(route('events.register', $event))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'player_id' => Player::where('name', 'Andi')->value('id'),
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_registration_is_ignored(): void
    {
        $event = Event::where('status', 'open')->firstOrFail();
        $andi = $this->user('andi@padelio.id');

        $this->actingAs($andi)->post(route('events.register', $event));
        $this->actingAs($andi)->post(route('events.register', $event));

        $this->assertSame(1, EventRegistration::where('event_id', $event->id)
            ->where('player_id', Player::where('name', 'Andi')->value('id'))->count());
    }

    public function test_suspended_player_cannot_register(): void
    {
        $event = Event::where('status', 'open')->firstOrFail();
        $andi = Player::where('name', 'Andi')->firstOrFail();
        $andi->update(['suspended_at' => now()]);

        $this->actingAs($this->user('andi@padelio.id'))
            ->post(route('events.register', $event))
            ->assertSessionHasErrors('event');
    }

    public function test_admin_accepts_registration(): void
    {
        $event = Event::where('status', 'open')->firstOrFail();
        $this->actingAs($this->user('andi@padelio.id'))->post(route('events.register', $event));
        $registration = EventRegistration::where('event_id', $event->id)->latest('id')->firstOrFail();

        $this->actingAs($this->user('admin@padelio.id'))
            ->post(route('admin.events.registrations.set', [$event, $registration]), ['status' => 'accepted'])
            ->assertSessionHasNoErrors();

        $this->assertSame('accepted', $registration->fresh()->status);
    }
}
