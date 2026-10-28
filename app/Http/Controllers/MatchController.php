<?php
namespace App\Http\Controllers;

use App\Enums\ChallengeStatus;
use App\Enums\MatchStatus;
use App\Models\Challenge;
use App\Models\MatchGame;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $matches = MatchGame::with(['a', 'b', 'sets'])
            ->where('status', MatchStatus::Approved)
            ->latest('approved_at')
            ->limit(40)
            ->get();

        $mine = [];

        if ($player = $request->user()?->player) {
            $mine = MatchGame::with(['a', 'b', 'sets', 'confirmations'])
                ->where(fn ($q) => $q->where('a_id', $player->id)->orWhere('b_id', $player->id))
                ->whereIn('status', [MatchStatus::PendingConfirm, MatchStatus::Pending])
                ->latest()
                ->get();

            $scheduled = Challenge::with(['fromPlayer', 'toPlayer', 'court'])
                ->where(fn ($q) => $q->where('from_player_id', $player->id)->orWhere('to_player_id', $player->id))
                ->where('status', ChallengeStatus::Scheduled)
                ->orderBy('scheduled_at')
                ->get();
        } else {
            $scheduled = collect();
        }

        return view('matches', compact('matches', 'mine', 'scheduled'));
    }
}
