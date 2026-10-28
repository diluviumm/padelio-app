<?php
namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Model;

class MatchGame extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'challenge_id', 'a_id', 'b_id', 'winner_id', 'status',
        'approved_by', 'approved_at', 'delta_a', 'delta_b', 'played_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MatchStatus::class,
            'approved_at' => 'datetime',
            'played_at' => 'datetime',
        ];
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }

    public function a()
    {
        return $this->belongsTo(Player::class, 'a_id');
    }

    public function b()
    {
        return $this->belongsTo(Player::class, 'b_id');
    }

    public function winner()
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function sets()
    {
        return $this->hasMany(MatchSet::class, 'match_id')->orderBy('set_no');
    }

    public function confirmations()
    {
        return $this->hasMany(MatchConfirmation::class, 'match_id');
    }

    public function scoreLabel(): string
    {
        return $this->sets->map(fn ($s) => $s->a_games . '-' . $s->b_games)->implode(' ');
    }

    public function involves(Player $player): bool
    {
        return $this->a_id === $player->id || $this->b_id === $player->id;
    }
}
