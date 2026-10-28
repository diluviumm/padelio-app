@extends('layouts.admin')
@section('title', 'Pendaftar')
@section('heading', 'Pendaftar — ' . $event->name)

@section('content')
<a class="btn btn-ghost btn-sm" href="{{ route('admin.events') }}">← Kembali ke event</a>

<div class="card" style="margin-top:12px;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table class="tbl">
            <thead><tr><th>Pemain</th><th>Rating</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @forelse($registrations as $registration)
                <tr>
                    <td>{{ $registration->player->name }}</td>
                    <td>{{ $registration->player->rating }}</td>
                    <td><span class="pill {{ $registration->status === 'accepted' ? 'pill-success' : ($registration->status === 'rejected' ? 'pill-danger' : 'pill-warning') }}">{{ ucfirst($registration->status) }}</span></td>
                    <td>
                        <div class="table-actions">
                            @foreach(['accepted' => 'Terima', 'rejected' => 'Tolak', 'pending' => 'Pending'] as $status => $label)
                                <form method="POST" action="{{ route('admin.events.registrations.set', [$event, $registration]) }}">@csrf<input type="hidden" name="status" value="{{ $status }}"><button class="btn btn-sm {{ $status === 'accepted' ? 'btn-success' : ($status === 'rejected' ? 'btn-danger-soft' : 'btn-ghost') }}" type="submit">{{ $label }}</button></form>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="faint small">Belum ada pendaftar.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
