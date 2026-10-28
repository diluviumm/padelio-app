<?php
namespace App\Http\Controllers;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\MatchGame;
use App\Services\MatchService;
use Illuminate\Http\Request;

class MatchFlowController extends Controller
{
    public function create(Challenge $challenge, Request $request)
    {
        $player = $request->user()->player;
        abort_unless($player && $challenge->involves($player), 403);

        if ($challenge->status !== ChallengeStatus::Scheduled || $challenge->matchGame()->exists()) {
            abort(403, 'Skor tidak bisa diinput untuk pertandingan ini.');
        }

        return view('score', compact('challenge'));
    }

    public function store(Challenge $challenge, Request $request, MatchService $matches)
    {
        $player = $request->user()->player;
        abort_unless($player && $challenge->involves($player), 403);

        $data = $request->validate([
            'sets' => ['required', 'array', 'min:2', 'max:3'],
            'sets.*.a' => ['required', 'integer', 'min:0', 'max:7'],
            'sets.*.b' => ['required', 'integer', 'min:0', 'max:7'],
        ]);

        $sets = collect($data['sets'])->map(fn ($set) => [(int) $set['a'], (int) $set['b']])->values()->all();

        foreach ($sets as $set) {
            if ($set[0] === $set[1]) {
                return back()->withErrors(['sets' => 'Skor set tidak boleh imbang.'])->withInput();
            }
        }

        $winsA = collect($sets)->filter(fn ($set) => $set[0] > $set[1])->count();
        $winsB = count($sets) - $winsA;

        if ($winsA < 2 && $winsB < 2) {
            return back()->withErrors(['sets' => 'Pemenang harus unggul minimal 2 set.'])->withInput();
        }

        $match = $matches->submitScore($challenge, $player, $sets);

        return redirect()->route('matches.index')
            ->with('status', 'Skor disimpan. Konfirmasi dari kedua pemain diperlukan sebelum admin menyetujui.');
    }

    public function confirm(MatchGame $match, Request $request, MatchService $matches)
    {
        $this->authorize('confirm', $match);

        $matches->confirm($match, $request->user()->player);

        return back()->with('status', 'Konfirmasi tercatat.');
    }
}
