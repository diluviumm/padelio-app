<?php
namespace App\Http\Controllers\Admin;

use App\Enums\MatchStatus;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\MatchGame;
use App\Models\Player;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'players' => Player::count(),
            'pending' => MatchGame::where('status', MatchStatus::Pending)->count(),
            'pending_confirm' => MatchGame::where('status', MatchStatus::PendingConfirm)->count(),
            'bookings_today' => Booking::active()->where('date', today())->count(),
            'revenue' => Booking::where('status', 'confirmed')->where('created_at', '>=', now()->startOfMonth())->sum('total'),
        ];

        $pending = MatchGame::with(['a', 'b', 'sets'])
            ->where('status', MatchStatus::Pending)
            ->latest()
            ->limit(8)
            ->get();

        $bookings = Booking::with(['court', 'user'])->latest()->limit(6)->get();
        $lowStock = Addon::where('stock', '<', 5)->orderBy('stock')->get();

        return view('admin.dashboard', compact('stats', 'pending', 'bookings', 'lowStock'));
    }
}
