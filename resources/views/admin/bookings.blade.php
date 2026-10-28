@extends('layouts.admin')
@section('title', 'Booking')
@section('heading', 'Kelola Booking')

@section('content')
<form method="GET" action="{{ route('admin.bookings') }}" class="card pad" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select class="select" name="status" style="width:auto" aria-label="Filter status">
        <option value="all">Semua Status</option>
        @foreach(['confirmed' => 'Confirmed', 'hold' => 'Hold', 'cancelled' => 'Dibatalkan'] as $value => $label)
            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <input class="input" type="date" name="date" value="{{ request('date') }}" aria-label="Tanggal" style="width:auto">
    <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
</form>

<div class="card" style="margin-top:14px;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table class="tbl">
            <thead><tr><th>Kode</th><th>Pemain</th><th>Lapangan</th><th>Jadwal</th><th>Total</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->code }}</td>
                    <td>{{ $booking->user->name }}</td>
                    <td>{{ $booking->court->name }}</td>
                    <td>{{ $booking->date->translatedFormat('d M Y') }} {{ $booking->slot }}</td>
                    <td>Rp{{ number_format($booking->total, 0, ',', '.') }}</td>
                    <td><span class="pill {{ $booking->status->pill() }}">{{ $booking->status->label() }}</span></td>
                    <td>
                        <div class="table-actions">
                            @if($booking->status->value === 'hold')
                                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">@csrf @method('PUT')<input type="hidden" name="status" value="confirmed"><button class="btn btn-sm btn-success" type="submit">Konfirmasi</button></form>
                            @endif
                            @if($booking->status->value !== 'cancelled')
                                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" data-confirm="Batalkan booking {{ $booking->code }}?">@csrf @method('PUT')<input type="hidden" name="status" value="cancelled"><button class="btn btn-sm btn-danger-soft" type="submit">Batalkan</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
