<?php
namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Court;
use App\Models\CourtMaintenance;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RentController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        try {
            $parsed = Carbon::parse($date);
        } catch (\Throwable) {
            $parsed = now();
        }

        if ($parsed->lt(today()) || $parsed->gt(today()->addDays(config('padelio.booking_days')))) {
            $parsed = today();
        }

        $date = $parsed->toDateString();

        $courts = Court::where('active', true)
            ->when($request->query('surface') && $request->query('surface') !== 'all',
                fn ($q) => $q->where('surface', $request->query('surface')))
            ->when($request->query('area') && $request->query('area') !== 'all',
                fn ($q) => $q->where('area', $request->query('area')))
            ->when($request->query('lights') && $request->query('lights') !== 'all',
                fn ($q) => $q->where('lights', $request->query('lights') === 'yes'))
            ->when($request->query('sort') === 'price',
                fn ($q) => $q->orderBy('rate_off'),
                fn ($q) => $q->orderByDesc('rating'))
            ->get();

        $selectedId = (int) $request->query('court', $courts->first()?->id);
        $selectedCourt = $courts->firstWhere('id', $selectedId) ?? $courts->first();

        $booked = Booking::active()
            ->where('date', $date)
            ->get()
            ->keyBy(fn ($booking) => $booking->court_id . '|' . $booking->slot);

        $maintenance = CourtMaintenance::where('date', $date)
            ->get()
            ->keyBy(fn ($row) => $row->court_id . '|' . $row->slot);

        $days = collect(range(0, 6))->map(fn ($offset) => today()->addDays($offset));
        $slots = config('padelio.slots');
        $addons = Addon::active()->orderBy('name')->get();

        return view('rent', compact(
            'courts', 'selectedCourt', 'date', 'days', 'slots',
            'booked', 'maintenance', 'addons'
        ));
    }
}
