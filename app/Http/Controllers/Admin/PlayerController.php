<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $players = Player::query()
            ->when($filter === 'suspended', fn ($q) => $q->whereNotNull('suspended_at'))
            ->when($filter !== 'all' && $filter !== 'suspended' && isset(config('padelio.levels')[$filter]),
                fn ($q) => $q->where('level', $filter))
            ->when($request->query('search'), fn ($q) => $q->where('name', 'ilike', '%' . $request->query('search') . '%'))
            ->orderByDesc('rating')
            ->get();

        return view('admin.players', compact('players', 'filter'));
    }

    public function update(Player $player, Request $request, AuditService $audit)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL\pN .\'-]+$/u', Rule::unique('players', 'name')->ignore($player->id)],
            'city' => ['required', 'string', 'max:80'],
            'rating' => ['required', 'integer', 'min:0', 'max:5000'],
        ]);

        $level = app(\App\Services\EloService::class)->levelFor((int) $data['rating']);

        $before = $player->rating;

        $player->update([
            'name' => $data['name'],
            'city' => $data['city'],
            'rating' => $data['rating'],
            'level' => $level,
        ]);

        $audit->log($request->user(), 'player.update', $player, ['rating_before' => $before, 'rating_after' => $player->rating]);

        return back()->with('status', 'Pemain ' . $player->name . ' diperbarui.');
    }

    public function suspend(Player $player, AuditService $audit)
    {
        $player->update([
            'suspended_at' => $player->isSuspended() ? null : now(),
        ]);

        $audit->log(request()->user(), 'player.suspend', $player, ['suspended' => $player->isSuspended()]);

        return back()->with('status', $player->name . ($player->isSuspended() ? ' disuspensi.' : ' dibuka kembali.'));
    }
}
