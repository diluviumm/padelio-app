<?php
namespace App\Http\Controllers;

use App\Enums\MatchStatus;
use App\Models\MatchGame;
use App\Models\Player;

class PlayerController extends Controller
{
    public function show(Player $player)
    {
        $recent = MatchGame::with(['a', 'b', 'sets'])
            ->where(fn ($q) => $q->where('a_id', $player->id)->orWhere('b_id', $player->id))
            ->where('status', MatchStatus::Approved)
            ->latest('approved_at')
            ->limit(5)
            ->get();

        $me = auth()->user()?->player;
        $h2h = null;

        if ($me && $me->id !== $player->id) {
            $head = MatchGame::where('status', MatchStatus::Approved)
                ->where(fn ($q) => $q->where(fn ($x) => $x->where('a_id', $me->id)->where('b_id', $player->id))
                    ->orWhere(fn ($x) => $x->where('a_id', $player->id)->where('b_id', $me->id)))
                ->get();

            $h2h = [
                'wins' => $head->filter(fn ($m) => $m->winner_id === $me->id)->count(),
                'losses' => $head->filter(fn ($m) => $m->winner_id === $player->id)->count(),
            ];
        }

        return view('player', compact('player', 'recent', 'h2h'));
    }
}
