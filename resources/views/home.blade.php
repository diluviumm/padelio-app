@extends('layouts.app')
@section('title', 'Padel Ladder Indonesia')

@section('content')
<div class="card hero" style="margin-top:20px">
    <div class="glow" style="width:220px;height:220px;background:var(--primary);opacity:.22;left:-70px;top:-70px"></div>
    <div class="glow" style="width:180px;height:180px;background:var(--info);opacity:.18;right:-60px;bottom:-60px"></div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;align-items:center">
        <div>
            <span class="pill pill-soft">🎾 Padel Ladder Indonesia</span>
            <h1 style="font-size:2rem;margin-top:14px;line-height:1.15">Ranking transparan,<br>tantangan nyata.</h1>
            <p class="muted" style="margin-top:10px;max-width:46ch">Padelio menghubungkan pemain padel lewat ladder berbasis ELO: tantang pemain, jadwalkan pertandingan, input skor, dan lihat peringkatmu naik.</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px">
                <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Mulai gratis</a>
                <a class="btn btn-ghost btn-lg" href="{{ route('leaderboard') }}">Lihat ranking</a>
            </div>
        </div>
        <div class="grid grid-3" style="gap:10px">
            <div class="card pad" style="text-align:center"><div class="kpi"><span><span class="val">{{ $stats['players'] }}</span><span class="lbl" style="display:block">Pemain</span></span></div></div>
            <div class="card pad" style="text-align:center"><div class="kpi"><span><span class="val">{{ $stats['courts'] }}</span><span class="lbl" style="display:block">Lapangan</span></span></div></div>
            <div class="card pad" style="text-align:center"><div class="kpi"><span><span class="val">{{ $stats['matches'] }}</span><span class="lbl" style="display:block">Match</span></span></div></div>
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));margin-top:16px">
    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Top Rating</b>
        @foreach($top as $index => $player)
            <a class="lb-row" href="{{ route('players.show', $player) }}" style="padding-left:0;padding-right:0">
                <span class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-n')) }}">{{ $index + 1 }}</span>
                @include('partials.avatar', ['player' => $player, 'size' => 'sm'])
                <span class="grow">
                    <b class="small">{{ $player->name }}</b>
                    <span class="faint small" style="display:block">{{ $player->city }} · {{ $player->levelName() }}</span>
                </span>
                <b class="tnum">{{ $player->rating }}</b>
            </a>
        @endforeach
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Event</b>
        @forelse($events as $event)
            <div class="kv">
                <span><b class="small">{{ $event->name }}</b><span class="faint small" style="display:block">{{ $event->type }}</span></span>
                <span class="pill {{ $event->status->pill() }}">{{ $event->status->label() }}</span>
            </div>
        @empty
            <p class="faint small">Belum ada event.</p>
        @endforelse
        <a class="btn btn-ghost btn-block btn-sm" style="margin-top:10px" href="{{ route('events.index') }}">Semua event</a>
    </div>
</div>
@endsection
