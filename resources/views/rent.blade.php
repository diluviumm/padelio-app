@extends('layouts.app')
@section('title', 'Sewa Lapangan')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-cal"/></svg> Sewa Lapangan</h1>
    <p>Pilih tanggal, lapangan, dan slot jam. Harga dihitung otomatis di server.</p>
</div>

<form method="GET" action="{{ route('rent') }}" class="card pad" style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <input type="hidden" name="date" value="{{ $date }}">
    <input type="hidden" name="court" value="{{ $selectedCourt?->id }}">
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
    <select class="select" name="lights" style="width:auto" aria-label="Filter lampu">
        <option value="all">Lampu: Semua</option>
        <option value="yes" @selected(request('lights') === 'yes')>Ada Lampu</option>
        <option value="no" @selected(request('lights') === 'no')>Tanpa Lampu</option>
    </select>
    <select class="select" name="sort" style="width:auto" aria-label="Urutkan">
        <option value="rating" @selected(request('sort') !== 'price')>Urut: Rating</option>
        <option value="price" @selected(request('sort') === 'price')>Urut: Termurah</option>
    </select>
    <button class="btn btn-ghost btn-sm" type="submit">Terapkan</button>
</form>

<div class="chips" style="margin-bottom:12px">
    @foreach($days as $day)
        <a class="chip {{ $day->toDateString() === $date ? 'active' : '' }}" href="{{ route('rent', ['date' => $day->toDateString(), 'court' => $selectedCourt?->id, 'surface' => request('surface'), 'area' => request('area'), 'lights' => request('lights'), 'sort' => request('sort')]) }}">
            <span class="d">{{ $day->isToday() ? 'Hari ini' : $day->translatedFormat('D') }}</span>{{ $day->translatedFormat('d M') }}
        </a>
    @endforeach
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr))">
    <div class="stack">
        @foreach($courts as $court)
            <a class="card pad {{ $selectedCourt?->id === $court->id ? 'hover' : '' }}" style="display:block;{{ $selectedCourt?->id === $court->id ? 'border-color:var(--primary)' : '' }}" href="{{ route('rent', ['date' => $date, 'court' => $court->id, 'surface' => request('surface'), 'area' => request('area'), 'lights' => request('lights'), 'sort' => request('sort')]) }}">
                <div style="display:flex;justify-content:space-between;gap:8px">
                    <b style="font-size:.96rem">{{ $court->name }}</b>
                    <span class="pill surface-{{ strtolower($court->surface) }}">{{ $court->surface }}</span>
                </div>
                <div class="faint small" style="margin-top:4px">{{ $court->venue }} · {{ $court->area }}{{ $court->lights ? ' · Lampu' : '' }}</div>
                <div style="display:flex;justify-content:space-between;margin-top:8px" class="small">
                    <span class="muted">Rp{{ number_format($court->rate_off, 0, ',', '.') }} – Rp{{ number_format($court->rate_peak, 0, ',', '.') }}</span>
                    <span style="color:var(--star);font-weight:800">★ {{ $court->rating }}</span>
                </div>
            </a>
        @endforeach
    </div>

    <div class="card pad">
        @if($selectedCourt)
            <b class="sec-title" style="margin-top:0">{{ $selectedCourt->name }} — {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y') }}</b>
            <p class="faint small" style="margin-bottom:10px">Peak: 17:00–08:00. Slot terisi tidak bisa dipilih.</p>
            <div class="slot-grid">
                @foreach($slots as $slot)
                    @php($key = $selectedCourt->id . '|' . $slot)
                    @php($state = $maintenance->has($key) ? 'maintenance' : ($booked->has($key) ? 'booked' : 'available'))
                    @if($state === 'available')
                        <a class="slot available" href="{{ route('checkout', ['court_id' => $selectedCourt->id, 'date' => $date, 'slot' => $slot]) }}">
                            {{ $slot }}<small>Rp{{ number_format($selectedCourt->priceFor($slot) / 1000, 0, ',', '.') }}rb</small>
                        </a>
                    @else
                        <span class="slot {{ $state }}">{{ $slot }}<small>{{ $state === 'booked' ? 'terisi' : 'maintenance' }}</small></span>
                    @endif
                @endforeach
            </div>

            @auth
                <div class="divider" style="margin:16px 0"></div>
                <b class="sec-title">Booking saya</b>
                @php($mine = \App\Models\Booking::with('court')->where('user_id', auth()->id())->whereIn('status', ['confirmed', 'hold'])->latest()->limit(5)->get())
                @forelse($mine as $booking)
                    <div class="kv">
                        <span class="small">{{ $booking->code }} · {{ $booking->court->name }}<span class="faint" style="display:block">{{ $booking->date->translatedFormat('d M') }} {{ $booking->slot }}</span></span>
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}" data-confirm="Batalkan booking {{ $booking->code }}?">@csrf<button class="btn btn-sm btn-danger-soft" type="submit">Batal</button></form>
                    </div>
                @empty
                    <p class="faint small">Belum ada booking aktif.</p>
                @endforelse
            @endauth
        @else
            <p class="faint small">Tidak ada lapangan aktif.</p>
        @endif
    </div>
</div>
@endsection
