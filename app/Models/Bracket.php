<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bracket extends Model
{
    protected $fillable = ['event_id', 'size', 'generated_at'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime'];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function matches()
    {
        return $this->hasMany(BracketMatch::class)->orderBy('round')->orderBy('position');
    }

    public function rounds()
    {
        return $this->matches->groupBy('round');
    }

    public function champion(): ?Player
    {
        $final = $this->matches()->where('round', (int) log($this->size, 2))->first();
        return $final?->winner;
    }
}
