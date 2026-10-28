<?php
namespace App\Policies;

use App\Models\User;

class BracketPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
