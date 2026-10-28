<?php
namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Court;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function checkout(Request $request, BookingService $bookings)
    {
        $data = $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'date' => ['required', 'date'],
            'slot' => ['required', Rule::in(config('padelio.slots'))],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['integer', 'exists:addons,id'],
        ]);

        $court = Court::findOrFail($data['court_id']);

        if ($bookings->availability($court->id, $data['date'], $data['slot']) !== 'available') {
            return redirect()->route('rent', ['date' => $data['date'], 'court' => $court->id])
                ->withErrors(['slot' => 'Slot tidak tersedia.']);
        }

        $addonIds = $data['addons'] ?? [];
        $addons = Addon::whereIn('id', $addonIds)->active()->get();
        $base = $court->priceFor($data['slot']);
        $addonsTotal = $addons->sum('price');

        return view('checkout', [
            'court' => $court,
            'date' => $data['date'],
            'slot' => $data['slot'],
            'addons' => $addons,
            'availableAddons' => Addon::active()->orderBy('name')->get(),
            'base' => $base,
            'addonsTotal' => $addonsTotal,
            'total' => $base + $addonsTotal,
        ]);
    }

    public function store(Request $request, BookingService $bookings)
    {
        $data = $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'slot' => ['required', Rule::in(config('padelio.slots'))],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['integer', 'exists:addons,id'],
            'payment_method' => ['required', Rule::in(config('padelio.payment_methods'))],
        ]);

        $booking = $bookings->create(
            $request->user(),
            (int) $data['court_id'],
            $data['date'],
            $data['slot'],
            array_map('intval', $data['addons'] ?? []),
            $data['payment_method']
        );

        return redirect()->route('rent', ['date' => $data['date'], 'court' => $data['court_id']])
            ->with('status', 'Booking ' . $booking->code . ' berhasil. Total Rp' . number_format($booking->total, 0, ',', '.'));
    }

    public function cancel(Booking $booking, BookingService $bookings)
    {
        $this->authorize('cancel', $booking);

        $bookings->cancel($booking, request()->user());

        return back()->with('status', 'Booking dibatalkan.');
    }
}
