<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'city', 'age', 'hand', 'style', 'bio', 'photo_path',
        'rating', 'level', 'wins', 'losses', 'streak', 'suspended_at',
    ];

    protected function casts(): array
    {
        return ['suspended_at' => 'datetime', 'age' => 'integer', 'rating' => 'integer'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challengesFrom()
    {
        return $this->hasMany(Challenge::class, 'from_player_id');
    }

    public function challengesTo()
    {
        return $this->hasMany(Challenge::class, 'to_player_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('suspended_at');
    }

    public function levelInfo(): array
    {
        return config('padelio.levels')[$this->level] ?? ['name' => ucfirst($this->level), 'color' => '#38bdf8'];
    }

    public function levelName(): string
    {
        return $this->levelInfo()['name'];
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 2));
    }

    public function winRate(): int
    {
        $total = $this->wins + $this->losses;
        return $total ? (int) round($this->wins / $total * 100) : 0;
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }
}
