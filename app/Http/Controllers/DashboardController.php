<?php
namespace App\Http\Controllers;

use App\Enums\ChallengeStatus;
use App\Enums\MatchStatus;
use App\Models\Booking;
use App\Models\Challenge;
use App\Models\MatchGame;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $player = $request->user()->player;

        if (! $player) {
            abort(403, 'Akun ini tidak terhubung ke pemain ladder.');
        }

        $incoming = Challenge::with('fromPlayer')
            ->where('to_player_id', $player->id)
            ->whereIn('status', [ChallengeStatus::PendingAccept, ChallengeStatus::PendingSchedule])
            ->latest()
            ->get();

        $upcoming = Challenge::with(['fromPlayer', 'toPlayer', 'court'])
            ->where(fn ($q) => $q->where('from_player_id', $player->id)->orWhere('to_player_id', $player->id))
            ->where('status', ChallengeStatus::Scheduled)
            ->orderBy('scheduled_at')
            ->get();

        $recent = MatchGame::with(['a', 'b', 'sets'])
            ->where(fn ($q) => $q->where('a_id', $player->id)->orWhere('b_id', $player->id))
            ->where('status', MatchStatus::Approved)
            ->latest('approved_at')
            ->limit(5)
            ->get();

        $monthly = MatchGame::with(['a', 'b'])
            ->where(fn ($q) => $q->where('a_id', $player->id)->orWhere('b_id', $player->id))
            ->where('status', MatchStatus::Approved)
            ->where('approved_at', '>=', now()->startOfMonth())
            ->get();

        $monthlyWins = $monthly->filter(fn ($m) => $m->winner_id === $player->id)->count();
        $monthlyLosses = $monthly->count() - $monthlyWins;
        $monthlyNet = $monthly->sum(fn ($m) => $m->a_id === $player->id ? (int) $m->delta_a : (int) $m->delta_b);

        $rank = \App\Models\Player::where('level', $player->level)
            ->whereNull('suspended_at')
            ->where('rating', '>', $player->rating)
            ->count() + 1;

        $bookings = Booking::with('court')
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['confirmed', 'hold'])
            ->orderBy('date')
            ->limit(3)
            ->get();

        return view('dashboard', compact(
            'player', 'rank', 'incoming', 'upcoming', 'recent', 'bookings',
            'monthlyWins', 'monthlyLosses', 'monthlyNet'
        ));
    }
}
