@extends('layouts.admin')
@section('title', 'Lapangan')
@section('heading', 'Kelola Lapangan')

@section('content')
<details class="card pad">
    <summary style="cursor:pointer;font-weight:700">+ Tambah lapangan</summary>
    <form method="POST" action="{{ route('admin.courts.store') }}" style="margin-top:12px">
        @csrf
        <div class="grid grid-2" style="gap:10px">
            <div class="field"><label for="c-name">Nama</label><input class="input" id="c-name" name="name" required maxlength="80"></div>
            <div class="field"><label for="c-venue">Venue</label><input class="input" id="c-venue" name="venue" required maxlength="120"></div>
            <div class="field"><label for="c-surface">Permukaan</label><select class="select" id="c-surface" name="surface">@foreach(config('padelio.surfaces') as $surface)<option>{{ $surface }}</option>@endforeach</select></div>
            <div class="field"><label for="c-area">Area</label><select class="select" id="c-area" name="area">@foreach(config('padelio.areas') as $area)<option>{{ $area }}</option>@endforeach</select></div>
            <div class="field"><label for="c-peak">Tarif peak</label><input class="input" id="c-peak" name="rate_peak" type="number" min="0" value="150000" required></div>
            <div class="field"><label for="c-off">Tarif off-peak</label><input class="input" id="c-off" name="rate_off" type="number" min="0" value="100000" required></div>
        </div>
        <label class="check" style="margin-top:8px"><input type="checkbox" name="lights" value="1" checked> Ada lampu</label>
        <button class="btn btn-primary" style="margin-top:10px" type="submit">Simpan lapangan</button>
    </form>
</details>

<div class="card" style="margin-top:14px;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table class="tbl">
            <thead><tr><th>Lapangan</th><th>Surface</th><th>Tarif</th><th>Rating</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @foreach($courts as $court)
                <tr>
                    <td><b class="small">{{ $court->name }}</b><div class="faint small">{{ $court->venue }} · {{ $court->area }}</div></td>
                    <td><span class="pill surface-{{ strtolower($court->surface) }}">{{ $court->surface }}</span></td>
                    <td class="small">Rp{{ number_format($court->rate_off, 0, ',', '.') }} / Rp{{ number_format($court->rate_peak, 0, ',', '.') }}</td>
                    <td>★ {{ $court->rating }} <span class="faint small">({{ $court->reviews_count }})</span></td>
                    <td>
                        <div class="table-actions">
                            <details class="inline-edit">
                                <summary>Edit</summary>
                                <form class="detail-form" method="POST" action="{{ route('admin.courts.update', $court) }}">
                                    @csrf @method('PUT')
                                    <input class="input" name="name" value="{{ $court->name }}" required aria-label="Nama">
                                    <input class="input" name="venue" value="{{ $court->venue }}" required style="margin-top:6px" aria-label="Venue">
                                    <select class="select" name="surface" style="margin-top:6px" aria-label="Permukaan">@foreach(config('padelio.surfaces') as $surface)<option @selected($court->surface === $surface)>{{ $surface }}</option>@endforeach</select>
                                    <select class="select" name="area" style="margin-top:6px" aria-label="Area">@foreach(config('padelio.areas') as $area)<option @selected($court->area === $area)>{{ $area }}</option>@endforeach</select>
                                    <input class="input" name="rate_peak" type="number" value="{{ $court->rate_peak }}" style="margin-top:6px" aria-label="Tarif peak">
                                    <input class="input" name="rate_off" type="number" value="{{ $court->rate_off }}" style="margin-top:6px" aria-label="Tarif off">
                                    <label class="check" style="margin-top:6px"><input type="checkbox" name="lights" value="1" @checked($court->lights)> Ada lampu</label>
                                    <button class="btn btn-sm btn-primary" style="margin-top:6px" type="submit">Simpan</button>
                                </form>
                            </details>
                            <details class="inline-edit">
                                <summary>Maintenance</summary>
                                <form class="detail-form" method="POST" action="{{ route('admin.courts.maintenance', $court) }}">
                                    @csrf
                                    <input class="input" type="date" name="date" value="{{ $date }}" required aria-label="Tanggal">
                                    <select class="select" name="slot" style="margin-top:6px" aria-label="Slot">@foreach(config('padelio.slots') as $slot)<option>{{ $slot }}</option>@endforeach</select>
                                    <button class="btn btn-sm btn-ghost" style="margin-top:6px" type="submit">Toggle slot</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.courts.destroy', $court) }}" data-confirm="Hapus lapangan {{ $court->name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger-soft" type="submit">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
