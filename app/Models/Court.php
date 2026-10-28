<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $fillable = [
        'name', 'venue', 'surface', 'area', 'lights', 'rate_peak', 'rate_off',
        'rating', 'facilities', 'active',
    ];

    protected function casts(): array
    {
        return ['lights' => 'boolean', 'active' => 'boolean', 'facilities' => 'array', 'rating' => 'float'];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function maintenance()
    {
        return $this->hasMany(CourtMaintenance::class);
    }

    public function isPeak(string $slot): bool
    {
        $hour = (int) substr($slot, 0, 2);
        return $hour >= 17 || $hour <= 8;
    }

    public function priceFor(string $slot): int
    {
        return $this->isPeak($slot) ? $this->rate_peak : $this->rate_off;
    }
}
