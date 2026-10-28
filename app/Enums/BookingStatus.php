<?php
namespace App\Enums;
enum BookingStatus: string {
    case Hold = 'hold';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    public function label(): string {
        return match($this) {
            self::Hold => 'Hold',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Dibatalkan',
            self::Expired => 'Kedaluwarsa',
        };
    }
    public function pill(): string {
        return match($this) {
            self::Hold => 'pill-warning',
            self::Confirmed => 'pill-success',
            self::Cancelled, self::Expired => 'pill-muted',
        };
    }
}
