<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BracketMatch extends Model
{
    protected $fillable = ['bracket_id', 'round', 'position', 'a_id', 'b_id', 'winner_id'];

    public function bracket()
    {
        return $this->belongsTo(Bracket::class);
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

    public function ready(): bool
    {
        return $this->a_id !== null && $this->b_id !== null;
    }
}
