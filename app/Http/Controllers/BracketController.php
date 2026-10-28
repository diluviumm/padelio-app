<?php
namespace App\Http\Controllers;

use App\Models\Event;

class BracketController extends Controller
{
    public function show(Event $event)
    {
        $bracket = $event->bracket()->with(['matches.a', 'matches.b', 'matches.winner'])->first();

        return view('bracket', compact('event', 'bracket'));
    }
}
