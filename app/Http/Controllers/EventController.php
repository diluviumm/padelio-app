<?php
namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount(['registrations as accepted_count' => fn ($q) => $q->where('status', 'accepted')])
            ->orderByRaw("case status when 'live' then 0 when 'open' then 1 when 'soon' then 2 else 3 end")
            ->get();

        $myRegs = auth()->check()
            ? \App\Models\EventRegistration::where('player_id', auth()->user()->player?->id)->get()->keyBy('event_id')
            : collect();

        return view('events', compact('events', 'myRegs'));
    }
}
