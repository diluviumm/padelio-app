@extends('layouts.app')
@section('title', $player->name)

@section('content')
@php($rank = \App\Models\Player::where('level', $player->level)->whereNull('suspended_at')->where('rating', '>', $player->rating)->count() + 1)
<div class="page-head">
    <h1>{{ $player->name }}</h1>
    <p>{{ $player->levelName() }} · Ranking #{{ $rank }} · Rating {{ $player->rating }}</p>
</div>

<a class="btn btn-ghost btn-sm" href="{{ route('leaderboard', ['level' => $player->level]) }}">← Kembali ke ranking</a>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));margin-top:14px">
    <div class="card pad" style="text-align:center">
        <div style="display:flex;justify-content:center">@include('partials.avatar', ['player' => $player, 'size' => 'xl'])</div>
        <h2 style="font-size:1.25rem;margin-top:12px">{{ $player->name }}</h2>
        <div style="display:flex;justify-content:center;gap:7px;margin-top:8px;flex-wrap:wrap">
            <span class="pill lvl-{{ $player->level }}">{{ $player->levelName() }}</span>
            <span class="pill pill-muted">{{ $player->city }}</span>
            @if($player->isSuspended())<span class="pill pill-danger">Suspended</span>@endif
        </div>
        <div class="kv" style="margin-top:14px"><span class="muted small">Menang / Kalah</span><b class="small">{{ $player->wins }} / {{ $player->losses }}</b></div>
        <div class="kv"><span class="muted small">Win rate</span><b class="small">{{ $player->winRate() }}%</b></div>
        <div class="kv"><span class="muted small">Streak</span><b class="small">{{ $player->streak }}</b></div>
        @if($player->bio)<div class="small muted" style="margin-top:12px;text-align:left;background:var(--bg-elev);border-radius:11px;padding:11px">{{ $player->bio }}</div>@endif

        @auth
            @php($me = auth()->user()->player)
            @if($me && $me->id !== $player->id && ! $player->isSuspended() && ! $me->isSuspended())
                <form method="POST" action="{{ route('challenges.store') }}" style="margin-top:14px">
                    @csrf
                    <input type="hidden" name="opponent_id" value="{{ $player->id }}">
                    <button class="btn btn-primary btn-block" type="submit">Tantang {{ $player->name }}</button>
                </form>
            @endif
        @endauth
    </div>

    <div class="stack">
        @if($h2h)
            <div class="card pad">
                <b class="sec-title" style="margin-top:0">Head-to-head vs kamu</b>
                <div class="kv"><span class="muted small">Kamu menang</span><b>{{ $h2h['wins'] }}</b></div>
                <div class="kv"><span class="muted small">Kamu kalah</span><b>{{ $h2h['losses'] }}</b></div>
            </div>
        @endif

        <div class="card pad">
            <b class="sec-title" style="margin-top:0">Hasil terakhir</b>
            @forelse($recent as $match)
                <div class="kv">
                    <span class="small">{{ $match->a->name }} vs {{ $match->b->name }}<span class="faint" style="display:block">{{ $match->scoreLabel() }} · {{ $match->approved_at?->translatedFormat('d M Y') }}</span></span>
                    <b class="small">{{ $match->winner?->name }}</b>
                </div>
            @empty
                <p class="faint small">Belum ada hasil.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
