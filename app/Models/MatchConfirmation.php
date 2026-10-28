<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchConfirmation extends Model
{
    protected $fillable = ['match_id', 'player_id', 'confirmed_at'];

    protected function casts(): array
    {
        return ['confirmed_at' => 'datetime'];
    }
}
