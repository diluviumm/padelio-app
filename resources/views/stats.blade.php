@extends('layouts.app')
@section('title', 'Statistik')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-chart"/></svg> Statistik Komunitas</h1>
    <p>Data agregat seluruh pemain Padelio.</p>
</div>

<div class="grid grid-4" style="gap:10px">
    <div class="card pad kpi"><span class="ico" style="background:var(--primary-soft);color:var(--primary)"><svg style="width:21px;height:21px"><use href="#i-users"/></svg></span><span><span class="val">{{ $active }}</span><span class="lbl" style="display:block">Pemain aktif</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--success-soft);color:var(--success)"><svg style="width:21px;height:21px"><use href="#i-match"/></svg></span><span><span class="val">{{ $approved }}</span><span class="lbl" style="display:block">Match disetujui</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--warning-soft);color:var(--warning)"><svg style="width:21px;height:21px"><use href="#i-trend"/></svg></span><span><span class="val">{{ $avg }}</span><span class="lbl" style="display:block">Rata-rata rating</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--purple-soft);color:var(--purple)"><svg style="width:21px;height:21px"><use href="#i-medal"/></svg></span><span><span class="val">{{ $total }}</span><span class="lbl" style="display:block">Total pemain</span></span></div>
</div>

<div class="grid grid-3" style="margin-top:14px">
    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Distribusi level</b>
        @foreach($distribution as $row)
            @php($pct = $total ? round($row['count'] / $total * 100) : 0)
            <div style="margin-bottom:10px">
                <div style="display:flex;justify-content:space-between" class="small"><span class="muted">{{ $row['name'] }}</span><b>{{ $row['count'] }}</b></div>
                <div class="progress" style="margin-top:5px"><div class="bar" style="width:{{ $pct }}%"></div></div>
            </div>
        @endforeach
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Top win rate</b>
        @foreach($topWinRate as $player)
            <div class="kv"><a class="small" href="{{ route('players.show', $player) }}">{{ $player->name }}</a><b class="small">{{ $player->winRate() }}%</b></div>
        @endforeach
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Top streak</b>
        @foreach($topStreak as $player)
            <div class="kv"><a class="small" href="{{ route('players.show', $player) }}">{{ $player->name }}</a><b class="small">{{ $player->streak }} 🔥</b></div>
        @endforeach
    </div>
</div>
@endsection
