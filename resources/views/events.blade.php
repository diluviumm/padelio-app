@extends('layouts.app')
@section('title', 'Event')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-event"/></svg> Event</h1>
    <p>Turnamen dan liga komunitas. Daftar, tunggu persetujuan admin, lalu lihat bracket.</p>
</div>

<div class="grid grid-2">
    @foreach($events as $event)
        @php($myReg = $myRegs->get($event->id))
        <div class="card pad {{ $event->status->value === 'live' ? '' : 'hover' }}" style="{{ $event->status->value === 'live' ? 'border-color:var(--primary)' : '' }}">
            <div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start">
                <span class="kpi"><span class="ico" style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:{{ $event->color }}1f;color:{{ $event->color }}"><svg style="width:22px;height:22px"><use href="#{{ $event->icon }}"/></svg></span></span>
                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end">
                    <span class="pill {{ $event->status->pill() }}">{{ $event->status->label() }}</span>
                    @if($myReg)
                        <span class="pill {{ $myReg->status === 'accepted' ? 'pill-success' : ($myReg->status === 'rejected' ? 'pill-danger' : 'pill-warning') }}">{{ ucfirst($myReg->status) }}</span>
                    @endif
                </div>
            </div>
            <h3 style="font-size:1rem;margin-top:12px">{{ $event->name }}</h3>
            <p class="muted small" style="margin-top:4px">{{ $event->detail }}</p>
            <p class="faint small" style="margin-top:6px">{{ $event->accepted_count }} peserta · {{ $event->type }}</p>

            <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
                @if($event->status->value === 'open' && ! $myReg)
                    @auth
                        <form method="POST" action="{{ route('events.register', $event) }}">@csrf<button class="btn btn-sm btn-primary" type="submit">Daftar sekarang</button></form>
                    @else
                        <a class="btn btn-sm btn-primary" href="{{ route('login') }}">Masuk untuk daftar</a>
                    @endauth
                @endif
                @if($event->bracket)
                    <a class="btn btn-sm btn-ghost" href="{{ route('bracket.show', $event) }}">Lihat bracket</a>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
