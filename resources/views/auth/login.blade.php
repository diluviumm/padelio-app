@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div class="auth-shell">
    <div class="card auth-card">
        <h1>Masuk ke Padelio</h1>
        <p class="muted small">Belum punya akun? <a href="{{ route('register') }}" style="color:var(--primary);font-weight:700">Daftar gratis</a></p>

        <form method="POST" action="{{ route('login.store') }}" class="stack" style="margin-top:18px">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input class="input" id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <button class="btn btn-primary btn-block" type="submit">Masuk</button>
        </form>

        <div class="divider" style="margin:18px 0"></div>
        <p class="faint small">Akun demo: <b>user@padelio.id</b> / user12345 · <b>admin@padelio.id</b> / admin12345</p>
        <a class="btn btn-ghost btn-block" style="margin-top:10px" href="{{ route('home') }}">Jelajah sebagai tamu</a>
    </div>
</div>
@endsection
