@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid grid-4" style="gap:10px">
    <div class="card pad kpi"><span class="ico" style="background:var(--primary-soft);color:var(--primary)"><svg style="width:21px;height:21px"><use href="#i-users"/></svg></span><span><span class="val">{{ $stats['players'] }}</span><span class="lbl" style="display:block">Pemain</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--warning-soft);color:var(--warning)"><svg style="width:21px;height:21px"><use href="#i-match"/></svg></span><span><span class="val">{{ $stats['pending'] }}</span><span class="lbl" style="display:block">Menunggu approve</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--info-soft);color:var(--info)"><svg style="width:21px;height:21px"><use href="#i-cal"/></svg></span><span><span class="val">{{ $stats['bookings_today'] }}</span><span class="lbl" style="display:block">Booking hari ini</span></span></div>
    <div class="card pad kpi"><span class="ico" style="background:var(--success-soft);color:var(--success)"><svg style="width:21px;height:21px"><use href="#i-credit"/></svg></span><span><span class="val">Rp{{ number_format($stats['revenue'], 0, ',', '.') }}</span><span class="lbl" style="display:block">Pendapatan bulan ini</span></span></div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));margin-top:14px">
    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Menunggu persetujuan ({{ $stats['pending'] }})</b>
        @forelse($pending as $match)
            <div class="list-row">
                <span class="grow small"><b>{{ $match->a->name }} vs {{ $match->b->name }}</b><span class="faint" style="display:block">{{ $match->scoreLabel() }} · {{ $match->created_at->translatedFormat('d M') }}</span></span>
                <form method="POST" action="{{ route('admin.matches.approve', $match) }}">@csrf<button class="btn btn-sm btn-success" type="submit">Approve</button></form>
                <form method="POST" action="{{ route('admin.matches.reject', $match) }}">@csrf<button class="btn btn-sm btn-ghost" type="submit">Tolak</button></form>
            </div>
        @empty
            <p class="faint small">Tidak ada hasil menunggu.</p>
        @endforelse
        <a class="btn btn-ghost btn-sm btn-block" style="margin-top:8px" href="{{ route('admin.results') }}">Semua hasil</a>
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Stok menipis</b>
        @forelse($lowStock as $addon)
            <div class="kv"><span class="small">{{ $addon->name }}</span><span class="pill {{ $addon->stock === 0 ? 'pill-danger' : 'pill-warning' }}">{{ $addon->stock }}</span></div>
        @empty
            <p class="faint small">Semua stok aman.</p>
        @endforelse
    </div>
</div>

<div class="card pad" style="margin-top:14px">
    <b class="sec-title" style="margin-top:0">Booking terbaru</b>
    <div class="table-wrap">
        <table class="tbl">
            <thead><tr><th>Kode</th><th>Pemain</th><th>Lapangan</th><th>Jadwal</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->code }}</td>
                    <td>{{ $booking->user->name }}</td>
                    <td>{{ $booking->court->name }}</td>
                    <td>{{ $booking->date->translatedFormat('d M') }} {{ $booking->slot }}</td>
                    <td>Rp{{ number_format($booking->total, 0, ',', '.') }}</td>
                    <td><span class="pill {{ $booking->status->pill() }}">{{ $booking->status->label() }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
