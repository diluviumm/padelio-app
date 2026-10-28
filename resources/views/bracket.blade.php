@extends('layouts.app')
@section('title', 'Bracket')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-trophy"/></svg> {{ $event->name }}</h1>
    <p>Single elimination{{ $bracket ? ' · ' . $bracket->size . ' peserta' : '' }}</p>
</div>

<a class="btn btn-ghost btn-sm" href="{{ route('events.index') }}">← Kembali ke event</a>

@if($bracket)
    @php($champion = $bracket->champion())
    @if($champion)
        <div class="card pad" style="margin-top:14px;border-color:var(--warning);background:var(--warning-soft)">
            <b>🏆 Champion: {{ $champion->name }}</b>
        </div>
    @endif

    @php($rounds = $bracket->rounds())
    @php($totalRounds = $rounds->keys()->max())
    <div class="bm" style="margin-top:14px">
        @foreach($rounds as $round => $matches)
            <div class="bm-col">
                <div class="bm-title">
                    @if($round === $totalRounds) Final
                    @elseif($round === $totalRounds - 1) Semifinal
                    @elseif($round === $totalRounds - 2) Perempat
                    @else R{{ 1 << ($totalRounds - $round) }}
                    @endif
                </div>
                @foreach($matches as $match)
                    <div class="bm-match {{ $match->winner_id ? 'bm-done' : '' }}">
                        <div class="bm-p {{ $match->winner_id && $match->winner_id === $match->a_id ? 'bm-win' : '' }}">
                            @if($match->a)
                                @include('partials.avatar', ['player' => $match->a, 'size' => 'sm'])
                                <span>{{ $match->a->name }}</span>
                            @else
                                <span class="faint">—</span>
                            @endif
                        </div>
                        <div class="bm-p {{ $match->winner_id && $match->winner_id === $match->b_id ? 'bm-win' : '' }}">
                            @if($match->b)
                                @include('partials.avatar', ['player' => $match->b, 'size' => 'sm'])
                                <span>{{ $match->b->name }}</span>
                            @else
                                <span class="faint">—</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@else
    <div class="card pad" style="margin-top:14px">
        <p class="faint small">Bracket belum dibuat. Admin akan generate saat turnamen dimulai.</p>
    </div>
@endif
@endsection
