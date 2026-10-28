<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchSet extends Model
{
    protected $fillable = ['match_id', 'set_no', 'a_games', 'b_games'];

    public function matchGame()
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }
}
