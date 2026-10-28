<?php
namespace App\Enums;
enum MatchStatus: string {
    case PendingConfirm = 'pending_confirm';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    public function label(): string {
        return match($this) {
            self::PendingConfirm => 'Menunggu konfirmasi',
            self::Pending => 'Menunggu admin',
            self::Approved => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }
    public function pill(): string {
        return match($this) {
            self::PendingConfirm, self::Pending => 'pill-warning',
            self::Approved => 'pill-success',
            self::Rejected => 'pill-danger',
        };
    }
}
