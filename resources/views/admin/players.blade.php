@extends('layouts.admin')
@section('title', 'Pemain')
@section('heading', 'Kelola Pemain')

@section('content')
<form method="GET" action="{{ route('admin.players') }}" class="card pad" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select class="select" name="filter" style="width:auto" aria-label="Filter level">
        <option value="all">Semua Level</option>
        @foreach(config('padelio.levels') as $key => $level)
            <option value="{{ $key }}" @selected($filter === $key)>{{ $level['name'] }}</option>
        @endforeach
        <option value="suspended" @selected($filter === 'suspended')>Disuspensi</option>
    </select>
    <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama..." aria-label="Cari pemain" style="max-width:240px">
    <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
</form>

<div class="card" style="margin-top:14px;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table class="tbl">
            <thead><tr><th>Pemain</th><th>Level</th><th>Rating</th><th>W/L</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @foreach($players as $player)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            @include('partials.avatar', ['player' => $player, 'size' => 'sm'])
                            <div><b class="small">{{ $player->name }}</b><div class="faint small">{{ $player->city }}</div></div>
                        </div>
                    </td>
                    <td><span class="pill lvl-{{ $player->level }}">{{ $player->levelName() }}</span></td>
                    <td>{{ $player->rating }}</td>
                    <td>{{ $player->wins }}/{{ $player->losses }}</td>
                    <td>{!! $player->isSuspended() ? '<span class="pill pill-danger">Suspended</span>' : '<span class="pill pill-success">Aktif</span>' !!}</td>
                    <td>
                        <div class="table-actions">
                            <details class="inline-edit">
                                <summary>Edit</summary>
                                <form class="detail-form" method="POST" action="{{ route('admin.players.update', $player) }}">
                                    @csrf @method('PUT')
                                    <input class="input" name="name" value="{{ $player->name }}" required maxlength="60" aria-label="Nama">
                                    <input class="input" name="city" value="{{ $player->city }}" required maxlength="80" style="margin-top:6px" aria-label="Kota">
                                    <input class="input" name="rating" type="number" min="0" max="5000" value="{{ $player->rating }}" required style="margin-top:6px" aria-label="Rating">
                                    <button class="btn btn-sm btn-primary" style="margin-top:6px" type="submit">Simpan</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.players.suspend', $player) }}" data-confirm="{{ $player->isSuspended() ? 'Buka suspensi' : 'Suspend' }} {{ $player->name }}?">
                                @csrf
                                <button class="btn btn-sm {{ $player->isSuspended() ? 'btn-success' : 'btn-danger-soft' }}" type="submit">{{ $player->isSuspended() ? 'Buka' : 'Suspend' }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
