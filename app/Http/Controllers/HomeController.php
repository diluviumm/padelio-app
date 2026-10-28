<?php
namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Enums\MatchStatus;
use App\Models\Court;
use App\Models\Event;
use App\Models\MatchGame;
use App\Models\Player;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('dashboard');
        }

        $stats = [
            'players' => Player::active()->count(),
            'courts' => Court::where('active', true)->count(),
            'matches' => MatchGame::where('status', MatchStatus::Approved)->count(),
        ];

        $top = Player::active()->orderByDesc('rating')->limit(5)->get();
        $events = Event::whereIn('status', [EventStatus::Open, EventStatus::Live])->limit(3)->get();

        return view('home', compact('stats', 'top', 'events'));
    }
}
