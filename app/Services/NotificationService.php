<?php
namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function send(?User $user, string $icon, string $message): void
    {
        if (! $user) {
            return;
        }

        UserNotification::create([
            'user_id' => $user->id,
            'icon' => $icon,
            'message' => mb_substr($message, 0, 300),
        ]);

        $ids = UserNotification::where('user_id', $user->id)
            ->orderByDesc('id')
            ->skip(100)
            ->take(1000)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            UserNotification::whereIn('id', $ids)->delete();
        }
    }

    public function toAdmins(string $icon, string $message): void
    {
        User::where('role', 'admin')->get()->each(fn (User $admin) => $this->send($admin, $icon, $message));
    }
}
