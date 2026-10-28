@extends('layouts.app')
@section('title', 'Notifikasi')

@section('content')
<div class="page-head">
    <h1>Notifikasi</h1>
    <p>{{ $notifications->whereNull('read_at')->count() }} belum dibaca dari {{ $notifications->count() }} terakhir.</p>
</div>

<form method="POST" action="{{ route('notifications.read') }}" style="margin-bottom:12px">
    @csrf
    <button class="btn btn-ghost btn-sm" type="submit">Tandai semua dibaca</button>
</form>

<div class="card" style="overflow:hidden">
    @forelse($notifications as $notification)
        <div class="dd-item {{ $notification->read_at ? '' : 'unread' }}" style="border-bottom:1px solid var(--border);border-radius:0">
            <svg style="width:20px;height:20px;flex:none;margin-top:2px"><use href="#{{ $notification->icon }}"/></svg>
            <span class="grow">
                <span class="t" style="display:block">{{ $notification->message }}</span>
                <span class="s">{{ $notification->created_at->diffForHumans() }}</span>
            </span>
        </div>
    @empty
        <p class="faint small" style="padding:16px">Belum ada notifikasi.</p>
    @endforelse
</div>
@endsection
