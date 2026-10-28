<?php
namespace App\Services;

use App\Enums\ChallengeStatus;
use App\Enums\MatchStatus;
use App\Models\Challenge;
use App\Models\MatchConfirmation;
use App\Models\MatchGame;
use App\Models\MatchSet;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatchService
{
    public function __construct(
        private EloService $elo,
        private NotificationService $notifications,
        private AuditService $audit,
    ) {}

    public function submitScore(Challenge $challenge, Player $actor, array $sets): MatchGame
    {
        if (! $challenge->involves($actor) || $challenge->status !== ChallengeStatus::Scheduled) {
            throw ValidationException::withMessages(['score' => 'Pertandingan ini tidak bisa diinput.']);
        }

        $winsA = collect($sets)->filter(fn ($set) => $set[0] > $set[1])->count();
        $winnerId = $winsA >= 2 ? $challenge->from_player_id : $challenge->to_player_id;

        return DB::transaction(function () use ($challenge, $actor, $sets, $winnerId) {
            $match = MatchGame::create([
                'challenge_id' => $challenge->id,
                'a_id' => $challenge->from_player_id,
                'b_id' => $challenge->to_player_id,
                'winner_id' => $winnerId,
                'status' => MatchStatus::PendingConfirm,
                'played_at' => now(),
            ]);

            foreach ($sets as $index => $set) {
                MatchSet::create([
                    'match_id' => $match->id,
                    'set_no' => $index + 1,
                    'a_games' => $set[0],
                    'b_games' => $set[1],
                ]);
            }

            $challenge->update(['status' => ChallengeStatus::Done, 'completed_at' => now()]);

            $opponent = $challenge->from_player_id === $actor->id
                ? $challenge->toPlayer
                : $challenge->fromPlayer;

            $this->notifications->send(
                $opponent->user,
                'i-match',
                $actor->name . ' menginput skor (' . $challenge->fromPlayer->name . ' '
                    . collect($sets)->map(fn ($s) => $s[0] . '-' . $s[1])->implode(' ') . ' '
                    . $challenge->toPlayer->name . ') - konfirmasi skor menunggumu'
            );

            $this->audit->log($actor->user, 'match.submit', $match, ['sets' => $sets]);

            return $match->fresh(['sets', 'a', 'b', 'winner']);
        });
    }

    public function confirm(MatchGame $match, Player $player): void
    {
        if (! $match->involves($player) || $match->status !== MatchStatus::PendingConfirm) {
            throw ValidationException::withMessages(['match' => 'Konfirmasi tidak valid.']);
        }

        DB::transaction(function () use ($match, $player) {
            MatchConfirmation::firstOrCreate(
                ['match_id' => $match->id, 'player_id' => $player->id],
                ['confirmed_at' => now()]
            );

            $confirmed = MatchConfirmation::where('match_id', $match->id)->count();

            if ($confirmed >= 2) {
                $match->update(['status' => MatchStatus::Pending]);
                $this->notifications->toAdmins('i-match', 'Hasil ' . $match->a->name . ' vs ' . $match->b->name . ' menunggu persetujuan admin');
            } else {
                $opponent = $match->a_id === $player->id ? $match->b : $match->a;
                $this->notifications->send($opponent->user, 'i-check', $player->name . ' mengonfirmasi skor - giliranmu konfirmasi');
            }

            $this->audit->log($player->user, 'match.confirm', $match);
        });
    }

    public function approve(MatchGame $match, User $admin): void
    {
        DB::transaction(function () use ($match, $admin) {
            $locked = MatchGame::whereKey($match->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== MatchStatus::Pending || $locked->delta_a !== null) {
                return;
            }

            $a = Player::whereKey($locked->a_id)->lockForUpdate()->firstOrFail();
            $b = Player::whereKey($locked->b_id)->lockForUpdate()->firstOrFail();
            $winner = $locked->winner_id === $a->id ? $a : $b;
            $loser = $winner->is($a) ? $b : $a;

            $delta = $this->elo->delta($winner, $loser);
            $penalty = (int) round($delta * 0.25);

            $winner->update([
                'rating' => $winner->rating + $delta,
                'wins' => $winner->wins + 1,
                'streak' => $winner->streak + 1,
                'level' => $this->elo->levelFor($winner->rating + $delta),
            ]);

            $loser->update([
                'rating' => max(0, $loser->rating - $penalty),
                'losses' => $loser->losses + 1,
                'streak' => 0,
                'level' => $this->elo->levelFor(max(0, $loser->rating - $penalty)),
            ]);

            $locked->update([
                'status' => MatchStatus::Approved,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'delta_a' => $locked->a_id === $winner->id ? $delta : -$penalty,
                'delta_b' => $locked->b_id === $winner->id ? $delta : -$penalty,
            ]);

            $this->notifications->send($winner->user, 'i-star', 'Hasil disetujui! Kamu menang vs ' . $loser->name . ' (+' . $delta . ' poin)');
            $this->notifications->send($loser->user, 'i-trend', 'Hasil disetujui: kamu kalah dari ' . $winner->name . ' (-' . $penalty . ' poin)');
            $this->audit->log($admin, 'match.approve', $locked, ['delta' => $delta, 'winner' => $winner->name]);
        });
    }

    public function reject(MatchGame $match, User $admin): void
    {
        $match->update(['status' => MatchStatus::Rejected]);
        $this->audit->log($admin, 'match.reject', $match);
    }
}
