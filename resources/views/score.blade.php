@extends('layouts.app')
@section('title', 'Input Skor')

@section('content')
<div class="page-head">
    <h1>Input Skor</h1>
    <p>{{ $challenge->fromPlayer->name }} vs {{ $challenge->toPlayer->name }} · {{ $challenge->scheduled_at?->translatedFormat('d M Y H:i') }}</p>
</div>

<div class="card pad" style="max-width:560px">
    <form method="POST" action="{{ route('matches.score.store', $challenge) }}">
        @csrf
        @for($i = 0; $i < 3; $i++)
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span class="faint small" style="width:48px">Set {{ $i + 1 }}</span>
                <input class="input" type="number" name="sets[{{ $i }}][a]" min="0" max="7" value="{{ old("sets.$i.a") }}" placeholder="{{ $challenge->fromPlayer->name }}" style="text-align:center" @if($i < 2) required @endif>
                <span class="faint">—</span>
                <input class="input" type="number" name="sets[{{ $i }}][b]" min="0" max="7" value="{{ old("sets.$i.b") }}" placeholder="{{ $challenge->toPlayer->name }}" style="text-align:center" @if($i < 2) required @endif>
            </div>
        @endfor
        <p class="faint small" style="margin:10px 0 16px">Isi minimal set 1 &amp; 2. Set 3 opsional. Pemenang = pemain dengan set terbanyak.</p>
        <div style="display:flex;gap:9px">
            <button class="btn btn-primary" type="submit" style="flex:1">Simpan skor</button>
            <a class="btn btn-ghost" href="{{ route('matches.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
