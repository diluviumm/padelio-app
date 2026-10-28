<?php
namespace App\Services;

use App\Models\Bracket;
use App\Models\BracketMatch;
use App\Models\Event;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BracketService
{
    public function __construct(private AuditService $audit) {}

    public function generate(Event $event, int $size, ?array $playerIds = null): Bracket
    {
        if (! in_array($size, [8, 16], true)) {
            throw ValidationException::withMessages(['size' => 'Ukuran bracket harus 8 atau 16.']);
        }

        $players = $playerIds
            ? Player::whereIn('id', $playerIds)->get()
            : $event->registrations()->where('status', 'accepted')->with('player')->get()->pluck('player');

        $players = $players->filter()->sortByDesc('rating')->values();

        if ($players->count() < $size) {
            throw ValidationException::withMessages(['size' => 'Peserta tidak cukup: butuh ' . $size . ', tersedia ' . $players->count() . '.']);
        }

        $players = $players->take($size);

        return DB::transaction(function () use ($event, $size, $players) {
            Bracket::where('event_id', $event->id)->delete();

            $bracket = Bracket::create([
                'event_id' => $event->id,
                'size' => $size,
                'generated_at' => now(),
            ]);

            $rounds = (int) log($size, 2);
            $firstRound = (int) ($size / 2);

            for ($position = 0; $position < $firstRound; $position++) {
                $a = $players[$position];
                $b = $players[$size - 1 - $position];
                BracketMatch::create([
                    'bracket_id' => $bracket->id,
                    'round' => 1,
                    'position' => $position,
                    'a_id' => $a->id,
                    'b_id' => $b->id,
                ]);
            }

            for ($round = 2; $round <= $rounds; $round++) {
                $count = (int) ($size / (2 ** $round));
                for ($position = 0; $position < $count; $position++) {
                    BracketMatch::create([
                        'bracket_id' => $bracket->id,
                        'round' => $round,
                        'position' => $position,
                    ]);
                }
            }

            $this->audit->log(auth()->user(), 'bracket.generate', $bracket, ['size' => $size]);

            return $bracket->load('matches');
        });
    }

    public function advance(BracketMatch $match, Player $winner): void
    {
        if (! $match->ready()) {
            throw ValidationException::withMessages(['bracket' => 'Pertandingan belum lengkap.']);
        }

        if (! in_array($winner->id, [$match->a_id, $match->b_id], true)) {
            throw ValidationException::withMessages(['bracket' => 'Pemenang bukan peserta pertandingan ini.']);
        }

        DB::transaction(function () use ($match, $winner) {
            if ($match->winner_id && $match->winner_id !== $winner->id) {
                $this->clearPath($match);
            }

            $match->update(['winner_id' => $winner->id]);

            $next = BracketMatch::where('bracket_id', $match->bracket_id)
                ->where('round', $match->round + 1)
                ->where('position', intdiv($match->position, 2))
                ->first();

            if ($next) {
                $slot = $match->position % 2 === 0 ? 'a_id' : 'b_id';
                $next->update([$slot => $winner->id]);
            }

            $this->audit->log(auth()->user(), 'bracket.advance', $match, ['winner' => $winner->name]);
        });
    }

    private function clearPath(BracketMatch $match): void
    {
        $position = intdiv($match->position, 2);
        $side = $match->position % 2;
        $round = $match->round + 1;

        while (true) {
            $next = BracketMatch::where('bracket_id', $match->bracket_id)
                ->where('round', $round)
                ->where('position', $position)
                ->first();

            if (! $next) {
                break;
            }

            $hadWinner = $next->winner_id !== null;

            $next->update([
                $side === 0 ? 'a_id' : 'b_id' => null,
                'winner_id' => null,
            ]);

            if (! $hadWinner) {
                break;
            }

            $side = $position % 2;
            $position = intdiv($position, 2);
            $round++;
        }
    }
}
