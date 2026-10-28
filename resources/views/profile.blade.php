@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<div class="page-head">
    <h1>Profil Saya</h1>
    <p>{{ auth()->user()->email }}</p>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
    <div class="card pad" style="text-align:center">
        <div style="display:flex;justify-content:center">@include('partials.avatar', ['player' => $player, 'size' => 'xl'])</div>
        <h2 style="font-size:1.3rem;margin-top:12px">{{ $player->name }}</h2>
        <div style="display:flex;justify-content:center;gap:7px;margin-top:8px;flex-wrap:wrap">
            <span class="pill lvl-{{ $player->level }}">{{ $player->levelName() }}</span>
            <span class="pill pill-muted">Ranking #{{ $rank }}</span>
            <span class="pill pill-soft">Rating {{ $player->rating }}</span>
        </div>

        <div class="kv" style="margin-top:14px"><span class="muted small">Kota</span><b class="small">{{ $player->city }}</b></div>
        <div class="kv"><span class="muted small">Umur</span><b class="small">{{ $player->age ?: '—' }}</b></div>
        <div class="kv"><span class="muted small">Tangan</span><b class="small">{{ $player->hand }}</b></div>
        <div class="kv"><span class="muted small">Gaya</span><b class="small">{{ $player->style }}</b></div>

        @if($player->bio)
            <div class="small muted" style="margin-top:12px;background:var(--bg-elev);border-radius:11px;padding:11px;text-align:left">{{ $player->bio }}</div>
        @endif

        <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" style="margin-top:14px">
            @csrf
            <label class="btn btn-soft btn-block" style="cursor:pointer">
                {{ $player->photo_path ? 'Ganti foto' : 'Unggah foto' }}
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" hidden data-auto-submit>
            </label>
            <p class="faint small" style="margin-top:6px">JPG/PNG/WebP, maks 2 MB. Dipotong 200×200.</p>
        </form>
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Edit profil</b>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="name">Nama</label>
                <input class="input" id="name" name="name" value="{{ old('name', $player->name) }}" required maxlength="60">
            </div>
            <div class="grid grid-2" style="gap:10px;margin-top:10px">
                <div class="field">
                    <label for="city">Kota</label>
                    <input class="input" id="city" name="city" value="{{ old('city', $player->city) }}" required maxlength="80">
                </div>
                <div class="field">
                    <label for="age">Umur</label>
                    <input class="input" id="age" name="age" type="number" min="10" max="90" value="{{ old('age', $player->age) }}">
                </div>
                <div class="field">
                    <label for="hand">Tangan dominan</label>
                    <select class="select" id="hand" name="hand">
                        @foreach(['Kanan', 'Kiri'] as $hand)
                            <option @selected(old('hand', $player->hand) === $hand)>{{ $hand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="style">Gaya main</label>
                    <select class="select" id="style" name="style">
                        @foreach(['All-round', 'Agresif', 'Defensif', 'Servis-voli'] as $style)
                            <option @selected(old('style', $player->style) === $style)>{{ $style }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="field" style="margin-top:10px">
                <label for="bio">Bio</label>
                <textarea class="textarea" id="bio" name="bio" rows="3" maxlength="500">{{ old('bio', $player->bio) }}</textarea>
            </div>
            <button class="btn btn-primary btn-block" style="margin-top:12px" type="submit">Simpan perubahan</button>
        </form>

        <div class="divider"></div>
        <div class="kv"><span class="muted small">Menang / Kalah</span><b class="small">{{ $player->wins }} / {{ $player->losses }}</b></div>
        <div class="kv"><span class="muted small">Win rate</span><b class="small">{{ $player->winRate() }}%</b></div>
        <div class="kv"><span class="muted small">Streak</span><b class="small">{{ $player->streak }}</b></div>
    </div>
</div>
@endsection
