<?php
namespace App\Policies;

use App\Enums\MatchStatus;
use App\Models\MatchGame;
use App\Models\User;

class MatchGamePolicy
{
    public function confirm(User $user, MatchGame $match): bool
    {
        $player = $user->player;
        return $player && $match->involves($player) && $match->status === MatchStatus::PendingConfirm;
    }

    public function approve(User $user, MatchGame $match): bool
    {
        return $user->isAdmin() && $match->status === MatchStatus::Pending;
    }

    public function reject(User $user, MatchGame $match): bool
    {
        return $user->isAdmin() && in_array($match->status, [MatchStatus::Pending, MatchStatus::PendingConfirm], true);
    }
}
