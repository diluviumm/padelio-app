<?php
namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'code', 'user_id', 'court_id', 'date', 'slot', 'price', 'addons_total',
        'total', 'status', 'payment_method', 'expires_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => BookingStatus::class,
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function addonItems()
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Hold->value]);
    }
}
