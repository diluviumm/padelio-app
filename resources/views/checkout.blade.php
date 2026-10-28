@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="page-head">
    <h1>Checkout</h1>
    <p>{{ $court->name }} · {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y') }} · {{ $slot }}</p>
</div>

<form method="POST" action="{{ route('bookings.store') }}" id="booking-form" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
    @csrf
    <input type="hidden" name="court_id" value="{{ $court->id }}">
    <input type="hidden" name="date" value="{{ $date }}">
    <input type="hidden" name="slot" value="{{ $slot }}">

    <div class="card pad stack">
        <b class="sec-title" style="margin-top:0">Ringkasan</b>
        <div class="kv"><span class="muted">Lapangan</span><b>{{ $court->name }}</b></div>
        <div class="kv"><span class="muted">Venue</span><b>{{ $court->venue }}</b></div>
        <div class="kv"><span class="muted">Tanggal</span><b>{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y') }}</b></div>
        <div class="kv"><span class="muted">Jam</span><b>{{ $slot }}</b></div>
        <div class="kv"><span class="muted">Biaya lapangan</span><b>Rp{{ number_format($base, 0, ',', '.') }}</b></div>
        <div class="divider"></div>
        <div class="kv"><b>Total</b><b id="booking-total" data-base="{{ $base }}" style="color:var(--primary);font-size:1.05rem">Rp{{ number_format($total, 0, ',', '.') }}</b></div>

        <div class="field" style="margin-top:6px">
            <label for="payment_method">Metode pembayaran</label>
            <select class="select" id="payment_method" name="payment_method" required>
                @foreach(config('padelio.payment_methods') as $method)
                    <option value="{{ $method }}">{{ $method }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Bayar &amp; konfirmasi</button>
        <a class="btn btn-ghost btn-block" href="{{ route('rent', ['date' => $date, 'court' => $court->id]) }}">Kembali</a>
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Peralatan tambahan</b>
        <div class="stack" style="gap:8px">
            @foreach($availableAddons as $addon)
                <label class="card pad" style="display:flex;align-items:center;gap:11px;cursor:pointer;padding:12px 14px">
                    <input type="checkbox" name="addons[]" value="{{ $addon->id }}" data-price="{{ $addon->price }}" @checked($addons->contains('id', $addon->id)) {{ $addon->stock <= 0 ? 'disabled' : '' }} style="width:18px;height:18px;accent-color:var(--primary)">
                    <span class="grow">
                        <span style="font-weight:700;font-size:.86rem;display:block">{{ $addon->name }}
                            @if($addon->stock < 5)<span class="pill pill-danger">Sisa {{ $addon->stock }}</span>@endif
                        </span>
                        <span class="faint small">Rp{{ number_format($addon->price, 0, ',', '.') }}{{ $addon->unit }}{{ $addon->deposit ? ' · deposit Rp' . number_format($addon->deposit, 0, ',', '.') : '' }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        <p class="faint small" style="margin-top:10px">Stok berkurang otomatis saat booking dikonfirmasi. Total dihitung ulang di server.</p>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/booking.js') }}" defer></script>
@endpush
