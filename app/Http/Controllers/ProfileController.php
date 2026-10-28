<?php
namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $player = $request->user()->player;
        abort_unless($player, 403);

        $rank = \App\Models\Player::where('level', $player->level)
            ->whereNull('suspended_at')
            ->where('rating', '>', $player->rating)
            ->count() + 1;

        return view('profile', compact('player', 'rank'));
    }

    public function update(Request $request)
    {
        $player = $request->user()->player;
        abort_unless($player, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL\pN .\'-]+$/u', Rule::unique('players', 'name')->ignore($player->id)],
            'city' => ['required', 'string', 'max:80'],
            'age' => ['nullable', 'integer', 'min:10', 'max:90'],
            'hand' => ['required', Rule::in(['Kanan', 'Kiri'])],
            'style' => ['required', Rule::in(['All-round', 'Agresif', 'Defensif', 'Servis-voli'])],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $player->update([
            'name' => $data['name'],
            'city' => $data['city'],
            'age' => $data['age'] ?? null,
            'hand' => $data['hand'],
            'style' => $data['style'],
            'bio' => $data['bio'] ?? '',
        ]);

        $request->user()->update(['name' => $data['name']]);

        return back()->with('status', 'Profil diperbarui.');
    }
}
