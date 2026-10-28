<?php
namespace App\Http\Controllers;

use App\Enums\MatchStatus;
use App\Models\MatchGame;
use App\Models\Player;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $levels = config('padelio.levels');
        $level = $request->query('level', 'intermediate');
        abort_unless(isset($levels[$level]), 404);

        $period = in_array($request->query('period', 'all'), ['all', 'week', 'month'], true)
            ? $request->query('period', 'all')
            : 'all';

        $search = trim((string) $request->query('search', ''));

        if ($period === 'all') {
            $players = Player::where('level', $level)
                ->whereNull('suspended_at')
                ->when($search !== '', fn ($q) => $q->where('name', 'ilike', '%' . $search . '%'))
                ->orderByDesc('rating')
                ->get();

            $rows = $players->map(fn (Player $p) => [
                'player' => $p,
                'wins' => $p->wins,
                'losses' => $p->losses,
                'net' => null,
            ]);
        } else {
            $from = $period === 'week' ? now()->subDays(7) : now()->startOfMonth();

            $matches = MatchGame::with(['a', 'b'])
                ->where('status', MatchStatus::Approved)
                ->where('approved_at', '>=', $from)
                ->get();

            $agg = [];
            foreach ($matches as $match) {
                foreach ([$match->a, $match->b] as $p) {
                    $agg[$p->id] ??= ['player' => $p, 'wins' => 0, 'losses' => 0, 'net' => 0];
                }
                $winnerId = $match->winner_id;
                $loserId = $match->a_id === $winnerId ? $match->b_id : $match->a_id;
                $agg[$winnerId]['wins']++;
                $agg[$loserId]['losses']++;
                $agg[$winnerId]['net'] += (int) ($match->a_id === $winnerId ? $match->delta_a : $match->delta_b);
                $agg[$loserId]['net'] += (int) ($match->a_id === $loserId ? $match->delta_a : $match->delta_b);
            }

            $rows = collect($agg)
                ->filter(fn ($row) => ! $row['player']->isSuspended())
                ->when($search !== '', fn ($c) => $c->filter(fn ($row) => mb_stripos($row['player']->name, $search) !== false))
                ->sortBy([['wins', 'desc'], ['net', 'desc']])
                ->values();
        }

        return view('leaderboard', compact('levels', 'level', 'period', 'search', 'rows'));
    }
}
