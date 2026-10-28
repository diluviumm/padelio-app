<?php
namespace App\Http\Controllers;

use App\Enums\MatchStatus;
use App\Models\MatchGame;
use App\Models\Player;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $total = Player::count();
        $active = Player::active()->count();
        $approved = MatchGame::where('status', MatchStatus::Approved)->count();
        $avg = (int) round(Player::avg('rating') ?? 0);

        $topWinRate = Player::active()
            ->whereRaw('(wins + losses) >= 5')
            ->get()
            ->sortByDesc(fn ($p) => $p->winRate())
            ->take(5);

        $topStreak = Player::active()->orderByDesc('streak')->take(5)->get();

        $distribution = collect(config('padelio.levels'))->map(fn ($level, $key) => [
            'name' => $level['name'],
            'count' => Player::where('level', $key)->count(),
        ]);

        return view('stats', compact('total', 'active', 'approved', 'avg', 'topWinRate', 'topStreak', 'distribution'));
    }
}
