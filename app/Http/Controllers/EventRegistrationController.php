<?php
namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function store(Event $event, Request $request, NotificationService $notifications)
    {
        $player = $request->user()->player;
        abort_unless($player, 403);

        if ($player->isSuspended()) {
            return back()->withErrors(['event' => 'Akun disuspensi - tidak bisa mendaftar.']);
        }

        if ($event->status !== EventStatus::Open) {
            return back()->withErrors(['event' => 'Event ini tidak membuka pendaftaran.']);
        }

        $registration = EventRegistration::firstOrCreate(
            ['event_id' => $event->id, 'player_id' => $player->id],
            ['status' => 'pending']
        );

        if (! $registration->wasRecentlyCreated) {
            return back()->with('status', 'Kamu sudah terdaftar di event ini.');
        }

        $notifications->toAdmins('i-event', 'Pendaftaran baru: ' . $player->name . ' - ' . $event->name);
        $notifications->send($player->user, 'i-event', 'Pendaftaranmu ke ' . $event->name . ' terkirim - menunggu persetujuan admin.');

        return back()->with('status', 'Pendaftaran terkirim.');
    }
}
