<?php
namespace App\Enums;
enum EventStatus: string {
    case Open = 'open';
    case Soon = 'soon';
    case Live = 'live';
    case Done = 'done';
    public function label(): string {
        return match($this) {
            self::Open => 'DAFTAR',
            self::Soon => 'SEGERA',
            self::Live => 'LIVE',
            self::Done => 'SELESAI',
        };
    }
    public function pill(): string {
        return match($this) {
            self::Open => 'pill-success',
            self::Soon => 'pill-muted',
            self::Live => 'pill-danger',
            self::Done => 'pill-info',
        };
    }
}
