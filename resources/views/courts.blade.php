@extends('layouts.app')
@section('title', 'Lapangan')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-pin"/></svg> Lapangan &amp; Venue</h1>
    <p>Direktori venue padel dengan rating dari komunitas.</p>
</div>

<form method="GET" action="{{ route('courts.index') }}" class="card pad" style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select class="select" name="surface" style="width:auto" aria-label="Filter permukaan">
        <option value="all">Semua Permukaan</option>
        @foreach(config('padelio.surfaces') as $surface)
            <option value="{{ $surface }}" @selected(request('surface') === $surface)>{{ $surface }}</option>
        @endforeach
    </select>
    <select class="select" name="area" style="width:auto" aria-label="Filter area">
        <option value="all">Semua Area</option>
        @foreach(config('padelio.areas') as $area)
            <option value="{{ $area }}" @selected(request('area') === $area)>{{ $area }}</option>
        @endforeach
    </select>
    <select class="select" name="sort" style="width:auto" aria-label="Urutkan">
        <option value="rating" @selected(request('sort') !== 'price')>Urut: Rating</option>
        <option value="price" @selected(request('sort') === 'price')>Urut: Termurah</option>
    </select>
    <button class="btn btn-ghost btn-sm" type="submit">Terapkan</button>
</form>

<div class="grid grid-2">
    @foreach($courts as $court)
        <div class="card pad">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                <div>
                    <b style="font-size:.98rem">{{ $court->name }}</b>
                    <div class="faint small">{{ $court->venue }} · {{ $court->area }}</div>
                </div>
                <span style="color:var(--star);font-weight:800" class="small">★ {{ $court->rating }}</span>
            </div>
            <div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap">
                <span class="pill surface-{{ strtolower($court->surface) }}">{{ $court->surface }}</span>
                <span class="pill pill-muted">{{ $court->lights ? 'Lampu' : 'Tanpa lampu' }}</span>
                <span class="pill pill-muted">{{ $reviews[$court->id]->total ?? 0 }} review</span>
            </div>
            <div class="kv" style="margin-top:10px"><span class="muted small">Mulai</span><b class="small">Rp{{ number_format($court->rate_off, 0, ',', '.') }}/jam</b></div>
            <div style="display:flex;gap:8px;margin-top:10px">
                <a class="btn btn-sm btn-primary" href="{{ route('courts.show', $court) }}">Detail</a>
                <a class="btn btn-sm btn-ghost" href="{{ route('rent', ['court' => $court->id]) }}">Sewa</a>
            </div>
        </div>
    @endforeach
</div>
@endsection
