<?php
namespace App\Enums;
enum ChallengeStatus: string {
    case PendingAccept = 'pending_accept';
    case PendingSchedule = 'pending_schedule';
    case Scheduled = 'scheduled';
    case Done = 'done';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    public function label(): string {
        return match($this) {
            self::PendingAccept => 'Menunggu',
            self::PendingSchedule => 'Perlu jadwal',
            self::Scheduled => 'Terjadwal',
            self::Done => 'Selesai',
            self::Declined => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }
    public function pill(): string {
        return match($this) {
            self::PendingAccept, self::PendingSchedule => 'pill-warning',
            self::Scheduled => 'pill-info',
            self::Done => 'pill-success',
            self::Declined, self::Cancelled => 'pill-muted',
        };
    }
}
