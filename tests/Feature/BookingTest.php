<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
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

    private function court(): Court
    {
        return Court::where('name', 'GBK Lap 4')->firstOrFail();
    }

    private function addon(): Addon
    {
        return Addon::where('name', 'Bola Padel (3 pcs)')->firstOrFail();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'court_id' => $this->court()->id,
            'date' => now()->addDays(3)->toDateString(),
            'slot' => '09:00',
            'addons' => [$this->addon()->id],
            'payment_method' => 'QRIS',
        ], $overrides);
    }

    public function test_booking_price_is_computed_server_side(): void
    {
        $stockBefore = $this->addon()->stock;
        $court = $this->court();

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('bookings.store'), $this->payload(['total' => 1]))
            ->assertSessionHasNoErrors();

        $booking = Booking::latest('id')->firstOrFail();
        $this->assertSame($court->rate_off + $this->addon()->price, $booking->total);
        $this->assertSame($stockBefore - 1, $this->addon()->fresh()->stock);
    }

    public function test_duplicate_slot_is_rejected(): void
    {
        $this->actingAs($this->user('user@padelio.id'))->post(route('bookings.store'), $this->payload());
        $this->actingAs($this->user('andi@padelio.id'))->post(route('bookings.store'), $this->payload())
            ->assertSessionHasErrors('slot');

        $this->assertSame(1, Booking::where('court_id', $this->court()->id)->where('slot', '09:00')->count());
    }

    public function test_cancel_restores_stock(): void
    {
        $stockBefore = $this->addon()->stock;
        $this->actingAs($this->user('user@padelio.id'))->post(route('bookings.store'), $this->payload());
        $booking = Booking::latest('id')->firstOrFail();

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('bookings.cancel', $booking))
            ->assertSessionHasNoErrors();

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertSame($stockBefore, $this->addon()->fresh()->stock);
    }

    public function test_user_cannot_cancel_other_users_booking(): void
    {
        $admin = $this->user('admin@padelio.id');
        $booking = Booking::create([
            'code' => 'BK-OTHER1', 'user_id' => $admin->id, 'court_id' => Court::where('name', 'Padel Kemang 2')->value('id'),
            'date' => now()->addDays(4)->toDateString(), 'slot' => '11:00',
            'price' => 90000, 'addons_total' => 0, 'total' => 90000,
            'status' => 'confirmed', 'payment_method' => 'QRIS',
        ]);

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_date_is_required(): void
    {
        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('bookings.store'), $this->payload(['date' => '']))
            ->assertSessionHasErrors('date');
    }

    public function test_maintenance_slot_is_rejected(): void
    {
        \App\Models\CourtMaintenance::create([
            'court_id' => $this->court()->id, 'date' => now()->addDays(3)->toDateString(), 'slot' => '09:00', 'reason' => 'tes',
        ]);

        $this->actingAs($this->user('user@padelio.id'))
            ->post(route('bookings.store'), $this->payload())
            ->assertSessionHasErrors('slot');
    }
}
