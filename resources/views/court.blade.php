@extends('layouts.app')
@section('title', $court->name)

@section('content')
<div class="page-head">
    <h1>{{ $court->name }}</h1>
    <p>{{ $court->venue }} · {{ $court->area }} · ★ {{ $court->rating }}</p>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
    <div class="card pad">
        <div style="display:flex;gap:6px;flex-wrap:wrap">
            <span class="pill surface-{{ strtolower($court->surface) }}">{{ $court->surface }}</span>
            <span class="pill pill-muted">{{ $court->lights ? 'Ada lampu' : 'Tanpa lampu' }}</span>
        </div>
        <div class="kv" style="margin-top:12px"><span class="muted small">Tarif off-peak</span><b>Rp{{ number_format($court->rate_off, 0, ',', '.') }}/jam</b></div>
        <div class="kv"><span class="muted small">Tarif peak</span><b>Rp{{ number_format($court->rate_peak, 0, ',', '.') }}/jam</b></div>
        <div class="divider"></div>
        <b class="sec-title">Fasilitas</b>
        <div style="display:flex;gap:6px;flex-wrap:wrap">
            @foreach(($court->facilities ?? []) as $facility)
                <span class="pill pill-soft">{{ $facility }}</span>
            @endforeach
        </div>
        <a class="btn btn-primary btn-block" style="margin-top:14px" href="{{ route('rent', ['court' => $court->id]) }}">Sewa lapangan</a>
    </div>

    <div class="card pad">
        <b class="sec-title" style="margin-top:0">Review ({{ $reviews->count() }})</b>
        @forelse($reviews as $review)
            <div class="kv">
                <span class="small"><b>{{ $review->user->name }}</b><span class="faint" style="display:block">{{ $review->text }}</span></span>
                <span style="color:var(--star);font-weight:800">{{ str_repeat('★', $review->rating) }}</span>
            </div>
        @empty
            <p class="faint small">Belum ada review.</p>
        @endforelse

        @auth
            <div class="divider"></div>
            <b class="sec-title">{{ $myReview ? 'Ubah review' : 'Tulis review' }}</b>
            <form method="POST" action="{{ route('reviews.store', $court) }}">
                @csrf
                <div class="field" style="margin-bottom:8px">
                    <label for="rating">Rating</label>
                    <select class="select" id="rating" name="rating" required>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected($myReview?->rating === $i)>{{ str_repeat('★', $i) }} {{ $i }}/5</option>
                        @endfor
                    </select>
                </div>
                <div class="field">
                    <label for="text">Ulasan</label>
                    <textarea class="textarea" id="text" name="text" rows="3" maxlength="500" placeholder="Kesan kamu tentang lapangan ini...">{{ $myReview?->text }}</textarea>
                </div>
                <button class="btn btn-primary btn-block" style="margin-top:10px" type="submit">Simpan review</button>
            </form>
        @endauth
    </div>
</div>
@endsection
