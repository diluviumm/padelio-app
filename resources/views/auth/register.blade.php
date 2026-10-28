@extends('layouts.app')
@section('title', 'Daftar')

@section('content')
<div class="auth-shell">
    <div class="card auth-card">
        <h1>Buat akun Padelio</h1>
        <p class="muted small">Sudah punya akun? <a href="{{ route('login') }}" style="color:var(--primary);font-weight:700">Masuk</a></p>

        <form method="POST" action="{{ route('register.store') }}" class="stack" style="margin-top:18px">
            @csrf
            <div class="field">
                <label for="name">Nama lengkap</label>
                <input class="input" id="name" name="name" value="{{ old('name') }}" required minlength="2" maxlength="60" autocomplete="name">
            </div>
            <div class="field">
                <label for="city">Kota</label>
                <input class="input" id="city" name="city" value="{{ old('city') }}" maxlength="80" placeholder="cth: Yogyakarta">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="field">
                <label for="password">Password <span class="faint">(min. 8 karakter)</span></label>
                <input class="input" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Ulangi password</label>
                <input class="input" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>
            <button class="btn btn-primary btn-block" type="submit">Daftar</button>
        </form>
    </div>
</div>
@endsection
