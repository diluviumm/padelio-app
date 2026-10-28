@extends('layouts.admin')
@section('title', 'Bracket')
@section('heading', 'Bracket — ' . $event->name)

@section('content')
<a class="btn btn-ghost btn-sm" href="{{ route('admin.events') }}">← Kembali ke event</a>

<div class="card pad" style="margin-top:12px">
    <form method="POST" action="{{ route('admin.events.bracket.generate', $event) }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
        @csrf
        <div class="field">
            <label for="size">Jumlah peserta</label>
            <select class="select" id="size" name="size">
                <option value="8">8 peserta</option>
                <option value="16">16 peserta</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">{{ $bracket ? 'Generate ulang' : 'Generate bracket' }}</button>
        @if($bracket)
            <button class="btn btn-danger-soft" type="submit" form="delete-bracket" data-confirm="Hapus bracket?">Hapus bracket</button>
        @endif
    </form>
    @if($bracket)
        <form id="delete-bracket" method="POST" action="{{ route('admin.events.bracket.destroy', $event) }}">@csrf @method('DELETE')</form>
    @endif
    <p class="faint small" style="margin-top:8px">Peserta diambil dari pendaftar berstatus accepted, diurutkan rating. Ukuran harus tepat 8 atau 16.</p>
</div>

@if($bracket)
    @php($rounds = $bracket->rounds())
    @php($totalRounds = $rounds->keys()->max())
    <div class="bm" style="margin-top:14px">
        @foreach($rounds as $round => $matches)
            <div class="bm-col">
                <div class="bm-title">{{ $round === $totalRounds ? 'Final' : ($round === $totalRounds - 1 ? 'Semifinal' : 'R' . (1 << ($totalRounds - $round))) }}</div>
                @foreach($matches as $match)
                    <div class="bm-match {{ $match->winner_id ? 'bm-done' : '' }}">
                        <div class="bm-p {{ $match->winner_id === $match->a_id ? 'bm-win' : '' }}">
                            @if($match->a) @include('partials.avatar', ['player' => $match->a, 'size' => 'sm']) <span>{{ $match->a->name }}</span> @else <span class="faint">—</span> @endif
                        </div>
                        <div class="bm-p {{ $match->winner_id === $match->b_id ? 'bm-win' : '' }}">
                            @if($match->b) @include('partials.avatar', ['player' => $match->b, 'size' => 'sm']) <span>{{ $match->b->name }}</span> @else <span class="faint">—</span> @endif
                        </div>
                        @if($match->ready())
                            <form method="POST" action="{{ route('admin.events.bracket.advance', [$event, $match]) }}" style="display:flex;gap:4px;padding:6px">
                                @csrf
                                <select class="select" name="winner_id" aria-label="Pemenang" style="font-size:.72rem;padding:4px">
                                    <option value="{{ $match->a_id }}">{{ $match->a->name }}</option>
                                    <option value="{{ $match->b_id }}">{{ $match->b->name }}</option>
                                </select>
                                <button class="btn btn-sm btn-soft" type="submit">Set</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@else
    <div class="card pad" style="margin-top:14px"><p class="faint small">Bracket belum dibuat.</p></div>
@endif
@endsection
