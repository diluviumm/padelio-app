<?php
namespace App\Policies;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\User;

class ChallengePolicy
{
    private function player(User $user)
    {
        return $user->player;
    }

    public function respond(User $user, Challenge $challenge): bool
    {
        $player = $this->player($user);
        return $player
            && $challenge->to_player_id === $player->id
            && $challenge->status === ChallengeStatus::PendingAccept;
    }

    public function schedule(User $user, Challenge $challenge): bool
    {
        $player = $this->player($user);
        return $player
            && $challenge->involves($player)
            && $challenge->status === ChallengeStatus::PendingSchedule;
    }

    public function cancel(User $user, Challenge $challenge): bool
    {
        $player = $this->player($user);
        return $player
            && $challenge->involves($player)
            && in_array($challenge->status, [ChallengeStatus::PendingAccept, ChallengeStatus::PendingSchedule, ChallengeStatus::Scheduled], true);
    }
}
