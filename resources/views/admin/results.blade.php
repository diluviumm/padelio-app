@extends('layouts.admin')
@section('title', 'Hasil')
@section('heading', 'Hasil Pertandingan')

@section('content')
<div class="card pad">
    <b class="sec-title" style="margin-top:0">Menunggu konfirmasi pemain ({{ $pendingConfirm->count() }})</b>
    @forelse($pendingConfirm as $match)
        <div class="list-row">
            <span class="grow small"><b>{{ $match->a->name }} vs {{ $match->b->name }}</b>
                <span class="faint" style="display:block">{{ $match->scoreLabel() }} · {{ $match->confirmations->count() }}/2 konfirmasi</span>
            </span>
            <span class="pill pill-warning">Menunggu pemain</span>
        </div>
    @empty
        <p class="faint small">Tidak ada.</p>
    @endforelse
    <p class="faint small" style="margin-top:8px">Approve hanya bisa setelah kedua pemain konfirmasi.</p>
</div>

<div class="card pad" style="margin-top:14px">
    <b class="sec-title" style="margin-top:0">Menunggu persetujuan ({{ $pending->count() }})</b>
    @forelse($pending as $match)
        <div class="list-row">
            <span class="grow small"><b>{{ $match->a->name }} {{ $match->scoreLabel() }} {{ $match->b->name }}</b>
                <span class="faint" style="display:block">Winner: {{ $match->winner?->name }} · {{ $match->created_at->translatedFormat('d M Y') }}</span>
            </span>
            <form method="POST" action="{{ route('admin.matches.approve', $match) }}">@csrf<button class="btn btn-sm btn-success" type="submit">Approve</button></form>
            <form method="POST" action="{{ route('admin.matches.reject', $match) }}">@csrf<button class="btn btn-sm btn-danger-soft" type="submit">Tolak</button></form>
        </div>
    @empty
        <p class="faint small">Tidak ada.</p>
    @endforelse
</div>

<div class="card pad" style="margin-top:14px">
    <b class="sec-title" style="margin-top:0">Riwayat disetujui</b>
    <div class="table-wrap">
        <table class="tbl">
            <thead><tr><th>Tanggal</th><th>Pemain</th><th>Skor</th><th>Winner</th><th>Delta</th></tr></thead>
            <tbody>
            @foreach($approved as $match)
                <tr>
                    <td>{{ $match->approved_at?->translatedFormat('d M Y') }}</td>
                    <td>{{ $match->a->name }} vs {{ $match->b->name }}</td>
                    <td>{{ $match->scoreLabel() }}</td>
                    <td>{{ $match->winner?->name }}</td>
                    <td>{{ $match->delta_a }} / {{ $match->delta_b }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
