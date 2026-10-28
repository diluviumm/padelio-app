<?php
namespace App\Http\Controllers\Admin;

use App\Enums\MatchStatus;
use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Services\MatchService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $pendingConfirm = MatchGame::with(['a', 'b', 'sets', 'confirmations'])
            ->where('status', MatchStatus::PendingConfirm)->latest()->get();

        $pending = MatchGame::with(['a', 'b', 'sets'])
            ->where('status', MatchStatus::Pending)->latest()->get();

        $approved = MatchGame::with(['a', 'b', 'sets', 'winner'])
            ->where('status', MatchStatus::Approved)->latest('approved_at')->limit(30)->get();

        return view('admin.results', compact('pendingConfirm', 'pending', 'approved'));
    }

    public function approve(MatchGame $match, MatchService $matches)
    {
        $this->authorize('approve', $match);

        if ($match->status === MatchStatus::PendingConfirm) {
            return back()->withErrors(['match' => 'Kedua pemain harus konfirmasi sebelum approve.']);
        }

        $matches->approve($match, request()->user());

        return back()->with('status', 'Hasil disetujui - ELO diperbarui.');
    }

    public function reject(MatchGame $match, MatchService $matches)
    {
        $this->authorize('reject', $match);

        $matches->reject($match, request()->user());

        return back()->with('status', 'Hasil ditolak.');
    }
}
