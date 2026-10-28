@extends('layouts.app')
@section('title', 'Tentang')

@section('content')
<div class="page-head">
    <h1><svg style="width:1em;height:1em"><use href="#i-bolt"/></svg> Tentang Padelio</h1>
    <p>Ladder komunitas padel dengan rating ELO, challenge, dan sewa lapangan.</p>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Cara kerja ELO</b>
        <p class="small muted">Peluang menang dihitung dengan <code>E = 1 / (1 + 10^((rating lawan - rating kamu) / 400))</code>. Kemenangan menghasilkan poin, kekalahan mengurangi 25% dari poin kemenangan.</p>
        <div class="divider"></div>
        <b class="sec-title">Level</b>
        @foreach(config('padelio.levels') as $key => $level)
            <div class="kv"><span class="pill lvl-{{ $key }}">{{ $level['name'] }}</span><span class="faint small">{{ $level['min'] }} – {{ $level['max'] == 9999 ? '∞' : $level['max'] }}</span></div>
        @endforeach
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Alur pertandingan</b>
        <ol class="small muted" style="padding-left:18px;line-height:1.9">
            <li>Kirim tantangan ke pemain lain.</li>
            <li>Lawan menerima, lalu salah satu mengatur jadwal.</li>
            <li>Input skor best of 3 setelah bertanding.</li>
            <li>Kedua pemain konfirmasi skor.</li>
            <li>Admin menyetujui, ELO diperbarui otomatis.</li>
        </ol>
    </div>
</div>
@endsection
