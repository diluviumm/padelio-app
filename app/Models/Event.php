<?php
namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['name', 'type', 'detail', 'status', 'starts_at', 'icon', 'color'];

    protected function casts(): array
    {
        return ['status' => EventStatus::class, 'starts_at' => 'datetime'];
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function bracket()
    {
        return $this->hasOne(Bracket::class);
    }
}
