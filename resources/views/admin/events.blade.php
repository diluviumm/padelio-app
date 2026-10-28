@extends('layouts.admin')
@section('title', 'Event')
@section('heading', 'Kelola Event')

@section('content')
<details class="card pad">
    <summary style="cursor:pointer;font-weight:700">+ Tambah event</summary>
    <form method="POST" action="{{ route('admin.events.store') }}" style="margin-top:12px">
        @csrf
        <div class="grid grid-2" style="gap:10px">
            <div class="field"><label for="e-name">Nama</label><input class="input" id="e-name" name="name" required maxlength="100"></div>
            <div class="field"><label for="e-type">Tipe</label><input class="input" id="e-type" name="type" value="Turnamen" required></div>
            <div class="field"><label for="e-status">Status</label><select class="select" id="e-status" name="status">@foreach(['open','soon','live','done'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></div>
            <div class="field"><label for="e-start">Mulai</label><input class="input" id="e-start" name="starts_at" type="date"></div>
            <div class="field"><label for="e-icon">Ikon</label><input class="input" id="e-icon" name="icon" value="i-trophy" required></div>
            <div class="field"><label for="e-color">Warna</label><input class="input" id="e-color" name="color" value="#2563eb" required></div>
        </div>
        <div class="field" style="margin-top:10px"><label for="e-detail">Detail</label><textarea class="textarea" id="e-detail" name="detail" rows="2" maxlength="300"></textarea></div>
        <button class="btn btn-primary" style="margin-top:10px" type="submit">Simpan event</button>
    </form>
</details>

<div class="card" style="margin-top:14px;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table class="tbl">
            <thead><tr><th>Event</th><th>Status</th><th>Pendaftar</th><th>Bracket</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @foreach($events as $event)
                <tr>
                    <td><b class="small">{{ $event->name }}</b><div class="faint small">{{ $event->type }}</div></td>
                    <td><span class="pill {{ $event->status->pill() }}">{{ $event->status->label() }}</span></td>
                    <td>{{ $event->pending_count }} pending</td>
                    <td>{!! $event->bracket ? '<span class="pill pill-success">Ada</span>' : '<span class="pill pill-muted">Belum</span>' !!}</td>
                    <td>
                        <div class="table-actions">
                            <a class="btn btn-sm btn-ghost" href="{{ route('admin.events.registrations', $event) }}">Pendaftar</a>
                            <a class="btn btn-sm btn-ghost" href="{{ route('admin.events.bracket.show', $event) }}">Bracket</a>
                            <details class="inline-edit">
                                <summary>Edit</summary>
                                <form class="detail-form" method="POST" action="{{ route('admin.events.update', $event) }}">
                                    @csrf @method('PUT')
                                    <input class="input" name="name" value="{{ $event->name }}" required>
                                    <input class="input" name="type" value="{{ $event->type }}" required style="margin-top:6px">
                                    <select class="select" name="status" style="margin-top:6px">@foreach(['open','soon','live','done'] as $status)<option value="{{ $status }}" @selected($event->status->value === $status)>{{ $status }}</option>@endforeach</select>
                                    <input class="input" name="starts_at" type="date" value="{{ $event->starts_at?->toDateString() }}" style="margin-top:6px">
                                    <input class="input" name="icon" value="{{ $event->icon }}" required style="margin-top:6px">
                                    <input class="input" name="color" value="{{ $event->color }}" required style="margin-top:6px">
                                    <textarea class="textarea" name="detail" rows="2" style="margin-top:6px">{{ $event->detail }}</textarea>
                                    <button class="btn btn-sm btn-primary" style="margin-top:6px" type="submit">Simpan</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" data-confirm="Hapus event {{ $event->name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger-soft" type="submit">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
