@extends('layouts.app')
@section('title', 'Pertandingan')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-match"/></svg> Pertandingan</h1>
    <p>Jadwal, skor, dan status konfirmasi — transparan untuk semua pemain.</p>
</div>

@auth
    @if($scheduled->isNotEmpty())
        <b class="sec-title">Jadwal kamu</b>
        @foreach($scheduled as $challenge)
            <div class="card match-card" style="margin-bottom:10px">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
                    <b class="small">{{ $challenge->fromPlayer->name }} vs {{ $challenge->toPlayer->name }}</b>
                    <span class="pill pill-info">Terjadwal</span>
                </div>
                <div class="faint small" style="margin-top:6px">{{ $challenge->scheduled_at?->translatedFormat('d M Y H:i') }} · {{ $challenge->court?->name }}</div>
                <a class="btn btn-sm btn-primary" style="margin-top:10px" href="{{ route('matches.score', $challenge) }}">Input skor</a>
            </div>
        @endforeach
    @endif

    @if($mine->isNotEmpty())
        <b class="sec-title">Menunggu konfirmasi</b>
        @foreach($mine as $match)
            @php($player = auth()->user()->player)
            @php($already = $match->confirmations->contains('player_id', $player?->id))
            <div class="card match-card" style="margin-bottom:10px">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
                    <b class="small">{{ $match->a->name }} vs {{ $match->b->name }}</b>
                    <span class="pill {{ $match->status->pill() }}">{{ $match->status->label() }}</span>
                </div>
                <div class="faint small" style="margin-top:6px">{{ $match->scoreLabel() }} · {{ $match->created_at->translatedFormat('d M Y') }}</div>
                @if($player && $match->involves($player) && ! $already && $match->status === \App\Enums\MatchStatus::PendingConfirm)
                    <form method="POST" action="{{ route('matches.confirm', $match) }}" style="margin-top:10px">@csrf<button class="btn btn-sm btn-success" type="submit">Konfirmasi skor</button></form>
                @elseif($already)
                    <p class="faint small" style="margin-top:10px">Kamu sudah konfirmasi — menunggu lawan.</p>
                @endif
            </div>
        @endforeach
    @endif
@endauth

<b class="sec-title">Hasil disetujui</b>
@forelse($matches as $match)
    <div class="card match-card" style="margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="teams">
                @include('partials.avatar', ['player' => $match->a, 'size' => 'sm'])
                <span class="small" style="font-weight:700">{{ $match->a->name }}</span>
                <span class="score">{{ $match->scoreLabel() }}</span>
                <span class="small" style="font-weight:700">{{ $match->b->name }}</span>
                @include('partials.avatar', ['player' => $match->b, 'size' => 'sm'])
            </div>
            <span class="pill pill-success">Selesai</span>
        </div>
        <div class="faint small" style="margin-top:8px">{{ $match->approved_at?->translatedFormat('d M Y') }} · Winner: {{ $match->winner?->name }}</div>
    </div>
@empty
    <p class="faint small">Belum ada hasil yang disetujui.</p>
@endforelse
@endsection
