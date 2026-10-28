<?php
namespace App\Models;

use App\Enums\ChallengeStatus;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'from_player_id', 'to_player_id', 'status', 'note', 'scheduled_at',
        'court_id', 'responded_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChallengeStatus::class,
            'scheduled_at' => 'datetime',
            'responded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function fromPlayer()
    {
        return $this->belongsTo(Player::class, 'from_player_id');
    }

    public function toPlayer()
    {
        return $this->belongsTo(Player::class, 'to_player_id');
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function matchGame()
    {
        return $this->hasOne(MatchGame::class, 'challenge_id');
    }

    public function involves(Player $player): bool
    {
        return $this->from_player_id === $player->id || $this->to_player_id === $player->id;
    }
}
