<?php
namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Player;
use App\Services\BracketService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount(['registrations as pending_count' => fn ($q) => $q->where('status', 'pending')])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.events', compact('events'));
    }

    public function store(Request $request)
    {
        Event::create($this->validated($request));

        return back()->with('status', 'Event dibuat.');
    }

    public function update(Event $event, Request $request)
    {
        $event->update($this->validated($request));

        return back()->with('status', 'Event diperbarui.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return back()->with('status', 'Event dihapus.');
    }

    public function registrations(Event $event)
    {
        $registrations = $event->registrations()->with('player')->latest()->get();
        $players = Player::active()->orderBy('name')->get();

        return view('admin.event-registrations', compact('event', 'registrations', 'players'));
    }

    public function setRegistration(Event $event, EventRegistration $registration, Request $request, NotificationService $notifications)
    {
        abort_unless($registration->event_id === $event->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'accepted', 'rejected'])],
        ]);

        $registration->update(['status' => $data['status']]);

        if ($data['status'] === 'accepted') {
            $notifications->send($registration->player->user, 'i-event', 'Pendaftaranmu ke ' . $event->name . ' DITERIMA - sampai jumpa di lapangan!');
        } elseif ($data['status'] === 'rejected') {
            $notifications->send($registration->player->user, 'i-x', 'Pendaftaranmu ke ' . $event->name . ' ditolak admin.');
        }

        return back()->with('status', 'Status pendaftar diperbarui.');
    }

    public function bracket(Event $event)
    {
        $bracket = $event->bracket()->with(['matches.a', 'matches.b', 'matches.winner'])->first();

        return view('admin.bracket', compact('event', 'bracket'));
    }

    public function generateBracket(Event $event, Request $request, BracketService $brackets)
    {
        $data = $request->validate(['size' => ['required', 'integer', Rule::in([8, 16])]]);

        $brackets->generate($event, (int) $data['size']);

        return back()->with('status', 'Bracket dibuat.');
    }

    public function advance(Event $event, \App\Models\BracketMatch $bracketMatch, Request $request, BracketService $brackets)
    {
        abort_unless($bracketMatch->bracket->event_id === $event->id, 404);

        $data = $request->validate(['winner_id' => ['required', 'integer', 'exists:players,id']]);

        $brackets->advance($bracketMatch, Player::findOrFail($data['winner_id']));

        return back()->with('status', 'Pemenang disimpan.');
    }

    public function destroyBracket(Event $event)
    {
        $event->bracket()->delete();

        return back()->with('status', 'Bracket dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:30'],
            'detail' => ['nullable', 'string', 'max:300'],
            'status' => ['required', Rule::in(array_column(EventStatus::cases(), 'value'))],
            'starts_at' => ['nullable', 'date'],
            'icon' => ['required', 'string', 'max:30'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
    }
}
