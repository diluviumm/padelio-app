<?php
namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\Court;
use App\Models\CourtMaintenance;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private NotificationService $notifications,
        private AuditService $audit,
    ) {}

    public function create(User $user, int $courtId, string $date, string $slot, array $addonIds, string $method): Booking
    {
        try {
            return DB::transaction(function () use ($user, $courtId, $date, $slot, $addonIds, $method) {
                $court = Court::whereKey($courtId)->lockForUpdate()->firstOrFail();

                if (! $court->active) {
                    throw ValidationException::withMessages(['court' => 'Lapangan sedang tidak aktif.']);
                }

                $hasMaintenance = $court->maintenance()->where('date', $date)->where('slot', $slot)->exists();
                if ($hasMaintenance) {
                    throw ValidationException::withMessages(['slot' => 'Slot sedang dalam maintenance.']);
                }

                $basePrice = $court->priceFor($slot);
                $addons = Addon::whereIn('id', $addonIds)->lockForUpdate()->get();
                $addonsTotal = 0;

                foreach ($addons as $addon) {
                    if (! $addon->active || $addon->stock < 1) {
                        throw ValidationException::withMessages(['addons' => 'Stok ' . $addon->name . ' habis.']);
                    }
                    $addonsTotal += $addon->price;
                }

                $booking = Booking::create([
                    'code' => 'BK-' . strtoupper(bin2hex(random_bytes(4))),
                    'user_id' => $user->id,
                    'court_id' => $court->id,
                    'date' => $date,
                    'slot' => $slot,
                    'price' => $basePrice,
                    'addons_total' => $addonsTotal,
                    'total' => $basePrice + $addonsTotal,
                    'status' => BookingStatus::Confirmed,
                    'payment_method' => $method,
                ]);

                foreach ($addons as $addon) {
                    BookingAddon::create([
                        'booking_id' => $booking->id,
                        'addon_id' => $addon->id,
                        'qty' => 1,
                        'price' => $addon->price,
                    ]);
                    $addon->decrement('stock');
                }

                $this->notifications->send($user, 'i-check', 'Booking ' . $booking->code . ' dikonfirmasi - ' . $court->name . ' ' . $date . ' ' . $slot);
                $this->audit->log($user, 'booking.create', $booking, ['total' => $booking->total]);

                return $booking->load('court', 'addonItems.addon');
            });
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'bookings_active_slot_unique')) {
                throw ValidationException::withMessages(['slot' => 'Slot sudah terisi. Pilih slot lain.']);
            }
            throw $e;
        }
    }

    public function availability(int $courtId, string $date, string $slot): string
    {
        if (CourtMaintenance::where('court_id', $courtId)->where('date', $date)->where('slot', $slot)->exists()) {
            return 'maintenance';
        }

        $booked = Booking::active()
            ->where('court_id', $courtId)
            ->where('date', $date)
            ->where('slot', $slot)
            ->exists();

        return $booked ? 'booked' : 'available';
    }

    public function cancel(Booking $booking, User $actor): void
    {
        if (! $actor->isAdmin() && $booking->user_id !== $actor->id) {
            abort(403);
        }

        if ($booking->status === BookingStatus::Cancelled) {
            return;
        }

        DB::transaction(function () use ($booking, $actor) {
            foreach ($booking->addonItems as $item) {
                Addon::whereKey($item->addon_id)->increment('stock', $item->qty);
            }

            $booking->update([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $this->notifications->send($booking->user, 'i-ban', 'Booking ' . $booking->code . ' dibatalkan.');
            $this->audit->log($actor, 'booking.cancel', $booking);
        });
    }
}
