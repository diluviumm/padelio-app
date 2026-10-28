<?php
namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\AuditService;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $bookings = Booking::with(['court', 'user', 'addonItems.addon'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->query('date'), fn ($q) => $q->where('date', $request->query('date')))
            ->latest()
            ->limit(100)
            ->get();

        return view('admin.bookings', compact('bookings', 'status'));
    }

    public function update(Booking $booking, Request $request, BookingService $bookings, AuditService $audit)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'cancelled'])],
        ]);

        if ($data['status'] === 'cancelled') {
            $bookings->cancel($booking, $request->user());
            $audit->log($request->user(), 'booking.admin_cancel', $booking);
            return back()->with('status', 'Booking dibatalkan dan stok dikembalikan.');
        }

        if ($booking->status === BookingStatus::Cancelled) {
            return back()->withErrors(['status' => 'Booking yang sudah dibatalkan tidak bisa dikonfirmasi ulang.']);
        }

        $booking->update(['status' => BookingStatus::Confirmed]);
        $audit->log($request->user(), 'booking.confirm', $booking);
        $booking->user->userNotifications()->create([
            'icon' => 'i-check',
            'message' => 'Booking ' . $booking->code . ' dikonfirmasi admin.',
        ]);

        return back()->with('status', 'Booking dikonfirmasi.');
    }
}
