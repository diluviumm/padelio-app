@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<div class="page-head">
    <h1>Halo, {{ $player->name }} 👋</h1>
    <p>{{ $player->levelName() }} · Ranking #{{ $rank }} · Rating {{ $player->rating }}</p>
</div>

<div class="grid grid-4" style="gap:10px">
    <div class="card pad kpi"><span class="ico" style="background:var(--success-soft);color:var(--success)"><svg style="width:21px;height:21px"><use href="#i-trophy"/></svg></span><span><span class="val">{{ $player->wins }}</span><span class="lbl" style="display:block">Menang</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--danger-soft);color:var(--danger)"><svg style="width:21px;height:21px"><use href="#i-x"/></svg></span><span><span class="val">{{ $player->losses }}</span><span class="lbl" style="display:block">Kalah</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--primary-soft);color:var(--primary)"><svg style="width:21px;height:21px"><use href="#i-chart"/></svg></span><span><span class="val">{{ $player->winRate() }}%</span><span class="lbl" style="display:block">Win rate</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--warning-soft);color:var(--warning)"><svg style="width:21px;height:21px"><use href="#i-fire"/></svg></span><span><span class="val">{{ $player->streak }}</span><span class="lbl" style="display:block">Streak</span></span></div>
</div>

@if($player->isSuspended())
    <div class="card pad" style="border-color:var(--danger);background:var(--danger-soft);margin-top:14px">
        <b class="small" style="color:var(--danger)">Akun disuspensi admin</b>
        <p class="small muted" style="margin-top:4px">Kamu tidak tampil di ranking dan tidak bisa menerima tantangan sampai admin membuka suspensi.</p>
    </div>
@endif

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));margin-top:14px">
    <div class="card pad">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <b class="sec-title" style="margin-top:0">Tantangan masuk</b>
            <a class="small" style="color:var(--primary);font-weight:700" href="{{ route('challenges.index') }}">Semua</a>
        </div>
        @forelse($incoming as $challenge)
            <div class="list-row">
                <span class="grow">
                    <b class="small">{{ $challenge->fromPlayer->name }}</b>
                    <span class="faint small" style="display:block">{{ $challenge->note ?: 'menantangmu' }}</span>
                </span>
                @if($challenge->status === \App\Enums\ChallengeStatus::PendingAccept)
                    <form method="POST" action="{{ route('challenges.accept', $challenge) }}">@csrf<button class="btn btn-sm btn-success" type="submit">Terima</button></form>
                    <form method="POST" action="{{ route('challenges.decline', $challenge) }}">@csrf<button class="btn btn-sm btn-ghost" type="submit">Tolak</button></form>
                @else
                    <a class="btn btn-sm btn-primary" href="{{ route('challenges.index') }}#jadwal-{{ $challenge->id }}">Atur jadwal</a>
                @endif
            </div>
        @empty
            <p class="faint small">Tidak ada tantangan masuk.</p>
        @endforelse
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Jadwal terdekat</b>
        @forelse($upcoming as $item)
            <div class="list-row">
                <span class="grow">
                    <b class="small">{{ $item->fromPlayer->name }} vs {{ $item->toPlayer->name }}</b>
                    <span class="faint small" style="display:block">{{ $item->scheduled_at?->translatedFormat('d M Y H:i') }} · {{ $item->court?->name ?? 'Lapangan belum dipilih' }}</span>
                </span>
                @if($item->involves($player))
                    <a class="btn btn-sm btn-primary" href="{{ route('matches.score', $item) }}">Input skor</a>
                @endif
            </div>
        @empty
            <p class="faint small">Belum ada jadwal. Kirim tantangan baru.</p>
        @endforelse
    </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));margin-top:14px">
    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Hasil terakhir</b>
        @forelse($recent as $match)
            @php($won = $match->winner_id === $player->id)
            <div class="list-row">
                <span class="grow small"><b>{{ $won ? 'Menang' : 'Kalah' }}</b> vs {{ $match->a_id === $player->id ? $match->b->name : $match->a->name }}
                    <span class="faint" style="display:block">{{ $match->scoreLabel() }} · {{ $match->approved_at?->translatedFormat('d M') }}</span>
                </span>
                <span class="small" style="color:{{ $won ? 'var(--success)' : 'var(--danger)' }};font-weight:800">{{ $won ? '+' : '' }}{{ $match->a_id === $player->id ? $match->delta_a : $match->delta_b }}</span>
            </div>
        @empty
            <p class="faint small">Belum ada hasil.</p>
        @endforelse
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Bulan ini</b>
        <div class="kv"><span class="muted small">Menang</span><b>{{ $monthlyWins }}</b></div>
        <div class="kv"><span class="muted small">Kalah</span><b>{{ $monthlyLosses }}</b></div>
        <div class="kv"><span class="muted small">Net poin</span><b style="color:{{ $monthlyNet >= 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $monthlyNet >= 0 ? '+' : '' }}{{ $monthlyNet }}</b></div>
        <div class="divider"></div>
        <b class="sec-title">Booking aktif</b>
        @forelse($bookings as $booking)
            <div class="kv"><span class="small">{{ $booking->court->name }}<span class="faint" style="display:block">{{ $booking->date->translatedFormat('d M') }} {{ $booking->slot }}</span></span><span class="pill {{ $booking->status->pill() }}">{{ $booking->status->label() }}</span></div>
        @empty
            <p class="faint small">Belum ada booking.</p>
        @endforelse
    </div>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px">
    <a class="btn btn-primary" href="{{ route('challenges.index') }}">Kirim tantangan</a>
    <a class="btn btn-ghost" href="{{ route('rent') }}">Sewa lapangan</a>
    <a class="btn btn-ghost" href="{{ route('leaderboard') }}">Lihat ranking</a>
</div>
@endsection
