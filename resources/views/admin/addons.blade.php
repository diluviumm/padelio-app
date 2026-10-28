@extends('layouts.admin')
@section('title', 'Peralatan')
@section('heading', 'Kelola Peralatan')

@section('content')
<details class="card pad">
    <summary style="cursor:pointer;font-weight:700">+ Tambah peralatan</summary>
    <form method="POST" action="{{ route('admin.addons.store') }}" style="margin-top:12px">
        @csrf
        <div class="grid grid-3" style="gap:10px">
            <div class="field"><label for="a-name">Nama</label><input class="input" id="a-name" name="name" required></div>
            <div class="field"><label for="a-price">Harga</label><input class="input" id="a-price" name="price" type="number" min="0" required></div>
            <div class="field"><label for="a-unit">Satuan</label><input class="input" id="a-unit" name="unit" value="/sesi" required></div>
            <div class="field"><label for="a-deposit">Deposit</label><input class="input" id="a-deposit" name="deposit" type="number" min="0" value="0"></div>
            <div class="field"><label for="a-stock">Stok</label><input class="input" id="a-stock" name="stock" type="number" min="0" value="10" required></div>
            <div class="field"><label for="a-icon">Ikon</label><input class="input" id="a-icon" name="icon" value="i-gear" required></div>
        </div>
        <button class="btn btn-primary" style="margin-top:10px" type="submit">Simpan</button>
    </form>
</details>

<div class="card" style="margin-top:14px;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table class="tbl">
            <thead><tr><th>Nama</th><th>Harga</th><th>Deposit</th><th>Stok</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @foreach($addons as $addon)
                <tr>
                    <td><b class="small">{{ $addon->name }}</b><div class="faint small">{{ $addon->unit }}</div></td>
                    <td>Rp{{ number_format($addon->price, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($addon->deposit, 0, ',', '.') }}</td>
                    <td><span class="pill {{ $addon->stock < 5 ? 'pill-danger' : 'pill-success' }}">{{ $addon->stock }}</span></td>
                    <td>
                        <div class="table-actions">
                            <form method="POST" action="{{ route('admin.addons.stock', $addon) }}">@csrf<input type="hidden" name="delta" value="1"><button class="btn btn-sm btn-ghost" type="submit">+1</button></form>
                            <form method="POST" action="{{ route('admin.addons.stock', $addon) }}">@csrf<input type="hidden" name="delta" value="-1"><button class="btn btn-sm btn-ghost" type="submit">−1</button></form>
                            <details class="inline-edit">
                                <summary>Edit</summary>
                                <form class="detail-form" method="POST" action="{{ route('admin.addons.update', $addon) }}">
                                    @csrf @method('PUT')
                                    <input class="input" name="name" value="{{ $addon->name }}" required>
                                    <input class="input" name="price" type="number" value="{{ $addon->price }}" required style="margin-top:6px">
                                    <input class="input" name="unit" value="{{ $addon->unit }}" required style="margin-top:6px">
                                    <input class="input" name="deposit" type="number" value="{{ $addon->deposit }}" style="margin-top:6px">
                                    <input class="input" name="stock" type="number" value="{{ $addon->stock }}" required style="margin-top:6px">
                                    <input class="input" name="icon" value="{{ $addon->icon }}" required style="margin-top:6px">
                                    <button class="btn btn-sm btn-primary" style="margin-top:6px" type="submit">Simpan</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.addons.destroy', $addon) }}" data-confirm="Hapus {{ $addon->name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger-soft" type="submit">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
