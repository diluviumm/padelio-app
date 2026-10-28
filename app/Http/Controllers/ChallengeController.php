<?php
namespace App\Http\Controllers;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\Court;
use App\Models\Player;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $player = $request->user()->player;
        abort_unless($player, 403);

        $incoming = Challenge::with(['fromPlayer', 'court'])
            ->where('to_player_id', $player->id)
            ->whereIn('status', [ChallengeStatus::PendingAccept, ChallengeStatus::PendingSchedule])
            ->latest()
            ->get();

        $outgoing = Challenge::with(['toPlayer', 'court'])
            ->where('from_player_id', $player->id)
            ->whereNotIn('status', [ChallengeStatus::Done, ChallengeStatus::Declined, ChallengeStatus::Cancelled])
            ->latest()
            ->get();

        $history = Challenge::with(['fromPlayer', 'toPlayer'])
            ->where(fn ($q) => $q->where('from_player_id', $player->id)->orWhere('to_player_id', $player->id))
            ->whereIn('status', [ChallengeStatus::Done, ChallengeStatus::Declined, ChallengeStatus::Cancelled])
            ->latest()
            ->limit(20)
            ->get();

        $opponents = Player::active()->where('id', '!=', $player->id)->orderByDesc('rating')->get();
        $courts = Court::where('active', true)->orderBy('name')->get();

        return view('challenges', compact('player', 'incoming', 'outgoing', 'history', 'opponents', 'courts'));
    }

    public function store(Request $request, NotificationService $notifications)
    {
        $player = $request->user()->player;
        abort_unless($player, 403);

        $data = $request->validate([
            'opponent_id' => ['required', 'integer', 'exists:players,id'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $opponent = Player::findOrFail($data['opponent_id']);

        if ($opponent->id === $player->id) {
            return back()->withErrors(['opponent_id' => 'Tidak bisa menantang diri sendiri.'])->withInput();
        }

        if ($opponent->isSuspended()) {
            return back()->withErrors(['opponent_id' => 'Pemain sedang disuspensi.'])->withInput();
        }

        $activeCount = Challenge::where(fn ($q) => $q->where('from_player_id', $player->id)->orWhere('to_player_id', $player->id))
            ->whereIn('status', [ChallengeStatus::PendingAccept, ChallengeStatus::PendingSchedule, ChallengeStatus::Scheduled])
            ->count();

        if ($activeCount >= config('padelio.max_active_challenges')) {
            return back()->withErrors(['opponent_id' => 'Maksimal ' . config('padelio.max_active_challenges') . ' tantangan aktif.'])->withInput();
        }

        $duplicate = Challenge::whereIn('status', [ChallengeStatus::PendingAccept, ChallengeStatus::PendingSchedule, ChallengeStatus::Scheduled])
            ->where(fn ($q) => $q->where(fn ($x) => $x->where('from_player_id', $player->id)->where('to_player_id', $opponent->id))
                ->orWhere(fn ($x) => $x->where('from_player_id', $opponent->id)->where('to_player_id', $player->id)))
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['opponent_id' => 'Sudah ada tantangan aktif dengan pemain ini.'])->withInput();
        }

        $challenge = Challenge::create([
            'from_player_id' => $player->id,
            'to_player_id' => $opponent->id,
            'status' => ChallengeStatus::PendingAccept,
            'note' => $data['note'] ?? '',
        ]);

        $notifications->send($opponent->user, 'i-sword', $player->name . ' menantangmu - buka halaman Tantangan.');
        $notifications->send($player->user, 'i-send', 'Tantangan ke ' . $opponent->name . ' terkirim.');

        return back()->with('status', 'Tantangan dikirim ke ' . $opponent->name . '.');
    }

    public function accept(Challenge $challenge, NotificationService $notifications)
    {
        $this->authorize('respond', $challenge);

        $challenge->update(['status' => ChallengeStatus::PendingSchedule, 'responded_at' => now()]);
        $notifications->send($challenge->fromPlayer->user, 'i-check', $challenge->toPlayer->name . ' menerima tantanganmu - atur jadwal sekarang.');

        return back()->with('status', 'Tantangan diterima. Atur jadwal di panel bawah.');
    }

    public function decline(Challenge $challenge, NotificationService $notifications)
    {
        $this->authorize('respond', $challenge);

        $challenge->update(['status' => ChallengeStatus::Declined, 'responded_at' => now()]);
        $notifications->send($challenge->fromPlayer->user, 'i-x', $challenge->toPlayer->name . ' menolak tantangan.');

        return back()->with('status', 'Tantangan ditolak.');
    }

    public function cancel(Challenge $challenge)
    {
        $this->authorize('cancel', $challenge);

        $challenge->update(['status' => ChallengeStatus::Cancelled]);

        return back()->with('status', 'Tantangan dibatalkan.');
    }

    public function schedule(Challenge $challenge, Request $request, BookingService $availability, NotificationService $notifications)
    {
        $this->authorize('schedule', $challenge);

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', Rule::in(config('padelio.slots'))],
            'court_id' => ['required', 'integer', 'exists:courts,id'],
        ]);

        $scheduledAt = Carbon::parse($data['date'] . ' ' . $data['time']);

        if ($scheduledAt->isPast()) {
            return back()->withErrors(['date' => 'Jadwal harus di masa depan.'])->withInput();
        }

        if ($availability->availability((int) $data['court_id'], $data['date'], $data['time']) !== 'available') {
            return back()->withErrors(['time' => 'Slot lapangan tidak tersedia. Pilih waktu lain.'])->withInput();
        }

        $challenge->update([
            'status' => ChallengeStatus::Scheduled,
            'scheduled_at' => $scheduledAt,
            'court_id' => $data['court_id'],
        ]);

        $other = $challenge->from_player_id === $request->user()->player->id
            ? $challenge->toPlayer
            : $challenge->fromPlayer;

        $notifications->send($other->user, 'i-cal', 'Jadwal pertandingan ditetapkan: ' . $scheduledAt->format('d M Y H:i') . '.');

        return back()->with('status', 'Jadwal pertandingan disimpan.');
    }
}
