@extends('layouts.app')
@section('title', 'Tantangan')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-sword"/></svg> Tantangan</h1>
    <p>Terima tantangan, atur jadwal, lalu input skor setelah bertanding.</p>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Masuk ({{ $incoming->count() }})</b>
        @forelse($incoming as $challenge)
            <div class="card pad" style="margin-bottom:10px;background:var(--bg-elev)" id="jadwal-{{ $challenge->id }}">
                <div style="display:flex;justify-content:space-between;gap:8px;align-items:center">
                    <b class="small">{{ $challenge->fromPlayer->name }}</b>
                    <span class="pill {{ $challenge->status->pill() }}">{{ $challenge->status->label() }}</span>
                </div>
                <p class="faint small" style="margin-top:4px">{{ $challenge->note ?: 'menantangmu' }}</p>

                @if($challenge->status === \App\Enums\ChallengeStatus::PendingAccept)
                    <div style="display:flex;gap:8px;margin-top:8px">
                        <form method="POST" action="{{ route('challenges.accept', $challenge) }}">@csrf<button class="btn btn-sm btn-success" type="submit">Terima</button></form>
                        <form method="POST" action="{{ route('challenges.decline', $challenge) }}">@csrf<button class="btn btn-sm btn-ghost" type="submit">Tolak</button></form>
                    </div>
                @else
                    <form method="POST" action="{{ route('challenges.schedule', $challenge) }}" style="margin-top:10px">
                        @csrf
                        <div class="grid grid-3" style="gap:8px">
                            <input class="input" type="date" name="date" min="{{ today()->toDateString() }}" value="{{ old('date', today()->addDay()->toDateString()) }}" required aria-label="Tanggal">
                            <select class="select" name="court_id" required aria-label="Lapangan">
                                @foreach($courts as $court)
                                    <option value="{{ $court->id }}">{{ $court->name }} — {{ $court->venue }}</option>
                                @endforeach
                            </select>
                            <select class="select" name="time" required aria-label="Jam">
                                @foreach(config('padelio.slots') as $slot)
                                    <option value="{{ $slot }}">{{ $slot }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-sm btn-primary" style="margin-top:8px" type="submit">Simpan jadwal</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="faint small">Tidak ada tantangan masuk.</p>
        @endforelse
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Kirim tantangan</b>
        <form method="POST" action="{{ route('challenges.store') }}">
            @csrf
            <div class="field">
                <label for="opponent_id">Lawan</label>
                <select class="select" id="opponent_id" name="opponent_id" required>
                    @foreach($opponents as $opponent)
                        <option value="{{ $opponent->id }}">{{ $opponent->name }} · {{ $opponent->levelName() }} · {{ $opponent->rating }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="note">Catatan (opsional)</label>
                <input class="input" id="note" name="note" maxlength="200" placeholder="cth: main jam 7 pagi?">
            </div>
            <button class="btn btn-primary btn-block" style="margin-top:12px" type="submit">Kirim tantangan</button>
        </form>
    </div>
</div>

<div class="card pad" style="margin-top:14px">
    <b class="sec-title" style="margin-top:0">Terkirim</b>
    @forelse($outgoing as $challenge)
        <div class="list-row">
            <span class="grow">
                <b class="small">Ke {{ $challenge->toPlayer->name }}</b>
                <span class="faint small" style="display:block">{{ $challenge->status->label() }}{{ $challenge->scheduled_at ? ' · ' . $challenge->scheduled_at->translatedFormat('d M H:i') : '' }}</span>
            </span>
            <form method="POST" action="{{ route('challenges.cancel', $challenge) }}" data-confirm="Batalkan tantangan ini?">@csrf<button class="btn btn-sm btn-ghost" type="submit">Batalkan</button></form>
        </div>
    @empty
        <p class="faint small">Belum ada tantangan terkirim.</p>
    @endforelse
</div>

<div class="card pad" style="margin-top:14px">
    <b class="sec-title" style="margin-top:0">Riwayat</b>
    @forelse($history as $challenge)
        <div class="kv">
            <span class="small">{{ $challenge->fromPlayer->name }} vs {{ $challenge->toPlayer->name }}</span>
            <span class="pill {{ $challenge->status->pill() }}">{{ $challenge->status->label() }}</span>
        </div>
    @empty
        <p class="faint small">Belum ada riwayat.</p>
    @endforelse
</div>
@endsection
