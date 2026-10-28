@extends('layouts.app')
@section('title', 'Ranking')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-trophy"/></svg> Ranking Padel</h1>
    <p>Posisi berdasarkan rating ELO per level. Poin berubah setelah hasil disetujui admin.</p>
</div>

<div class="card pad" style="margin-bottom:12px">
    <form method="GET" action="{{ route('leaderboard') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <input type="hidden" name="level" value="{{ $level }}">
        <input type="hidden" name="period" value="{{ $period }}">
        <input class="input" type="search" name="search" value="{{ $search }}" placeholder="Cari pemain..." aria-label="Cari pemain" style="max-width:320px">
        <button class="btn btn-primary btn-sm" type="submit">Cari</button>
        @if($search !== '')
            <a class="btn btn-ghost btn-sm" href="{{ route('leaderboard', ['level' => $level, 'period' => $period]) }}">Reset</a>
        @endif
    </form>
</div>

<div class="tabs">
    @foreach(['all' => 'Semua Waktu', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini'] as $value => $label)
        <a class="tab {{ $period === $value ? 'active' : '' }}" href="{{ route('leaderboard', ['level' => $level, 'period' => $value, 'search' => $search ?: null]) }}">{{ $label }}</a>
    @endforeach
</div>

@if($period === 'all')
    <div class="tabs">
        @foreach($levels as $key => $info)
            <a class="tab {{ $level === $key ? 'active' : '' }}" href="{{ route('leaderboard', ['level' => $key, 'period' => $period, 'search' => $search ?: null]) }}">{{ $info['name'] }}</a>
        @endforeach
    </div>
@else
    <p class="faint small" style="margin-bottom:10px">Peringkat periode dihitung dari pertandingan yang disetujui admin.</p>
@endif

<div class="card" style="overflow:hidden">
    @forelse($rows as $index => $row)
        @php($player = $row['player'])
        <div class="lb-row {{ auth()->id() && auth()->user()->player?->id === $player->id ? 'me' : '' }}">
            <span class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-n')) }}">{{ $index + 1 }}</span>
            @include('partials.avatar', ['player' => $player, 'size' => 'sm'])
            <span class="grow" style="min-width:0">
                <a href="{{ route('players.show', $player) }}" style="font-weight:700;font-size:.92rem;color:var(--text)">{{ $player->name }}</a>
                <span class="faint small" style="display:block">{{ $player->city }} · W {{ $row['wins'] }} L {{ $row['losses'] }}</span>
            </span>
            @if($period === 'all')
                <span class="tnum" style="font-weight:800;font-size:1rem;width:64px;text-align:right">{{ $player->rating }}</span>
            @else
                <span class="small" style="font-weight:800;color:{{ $row['net'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $row['net'] >= 0 ? '+' : '' }}{{ $row['net'] }}</span>
            @endif
        </div>
    @empty
        <div class="empty">
            <svg class="e-ico" style="width:2.5em;height:2.5em"><use href="#i-user"/></svg>
            <div style="font-weight:700;color:var(--text-muted)">{{ $search !== '' ? 'Pemain tidak ditemukan' : 'Belum ada pemain di level ini' }}</div>
        </div>
    @endforelse
</div>
@endsection
