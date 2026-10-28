<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Padelio - Padel Ladder Indonesia. Ranking, challenge, sewa lapangan.">
    <title>@yield('title', 'Padelio') · Padelio</title>
    <link rel="icon" type="image/png" href="{{ asset('img/brand-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1">
</head>
<body>
@include('partials.sprite')

<nav class="nav">
    <div class="container nav-inner">
        <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
            <img src="{{ asset('img/brand-mark.webp') }}" alt="Padelio" class="brand-img">
            <span>Padel<b>io</b></span>
        </a>

        <div class="nav-links">
            @auth
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('leaderboard') }}" class="{{ request()->routeIs('leaderboard') ? 'active' : '' }}">Ranking</a>
                <a href="{{ route('challenges.index') }}" class="{{ request()->routeIs('challenges.*') ? 'active' : '' }}">Tantangan</a>
                <a href="{{ route('matches.index') }}" class="{{ request()->routeIs('matches.*') ? 'active' : '' }}">Pertandingan</a>
                <a href="{{ route('rent') }}" class="{{ request()->routeIs('rent') || request()->routeIs('checkout') ? 'active' : '' }}">Sewa</a>
                <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}">Event</a>
            @else
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('leaderboard') }}" class="{{ request()->routeIs('leaderboard') ? 'active' : '' }}">Ranking</a>
                <a href="{{ route('matches.index') }}" class="{{ request()->routeIs('matches.*') ? 'active' : '' }}">Pertandingan</a>
                <a href="{{ route('courts.index') }}" class="{{ request()->routeIs('courts.*') ? 'active' : '' }}">Lapangan</a>
                <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}">Event</a>
                <a href="{{ route('stats') }}" class="{{ request()->routeIs('stats') ? 'active' : '' }}">Statistik</a>
            @endauth
        </div>

        <div class="nav-actions">
            <button class="icon-btn" type="button" data-theme-toggle aria-label="Ganti tema">
                <svg style="width:21px;height:21px"><use href="#i-sun"/></svg>
            </button>

            @auth
                @php
                    $navUser = auth()->user();
                    $navUnread = $navUser->unreadNotifications();
                    $navNotifs = $navUser->userNotifications()->latest()->limit(4)->get();
                @endphp

                <details class="dd">
                    <summary class="icon-btn" aria-label="Notifikasi">
                        <svg style="width:21px;height:21px"><use href="#i-bell"/></svg>
                        @if($navUnread > 0)<span class="dot"></span>@endif
                    </summary>
                    <div class="dropdown">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 8px 10px">
                            <b class="small">Notifikasi</b>
                            <form method="POST" action="{{ route('notifications.read') }}">
                                @csrf
                                <button class="small" type="submit" style="color:var(--primary);font-weight:700">Tandai dibaca</button>
                            </form>
                        </div>
                        @forelse($navNotifs as $notif)
                            <a class="dd-item {{ $notif->read_at ? '' : 'unread' }}" href="{{ route('notifications.index') }}">
                                <svg style="width:18px;height:18px;flex:none;margin-top:2px"><use href="#{{ $notif->icon }}"/></svg>
                                <span class="grow"><span class="t" style="display:block">{{ $notif->message }}</span>
                                <span class="s">{{ $notif->created_at->diffForHumans() }}</span></span>
                            </a>
                        @empty
                            <p class="faint small" style="padding:10px">Belum ada notifikasi.</p>
                        @endforelse
                        <a class="btn btn-ghost btn-sm btn-block" style="margin-top:6px" href="{{ route('notifications.index') }}">Lihat semua</a>
                    </div>
                </details>

                <details class="dd">
                    <summary class="user-chip" aria-label="Menu akun">
                        @include('partials.avatar', ['player' => $navUser->player, 'size' => 'sm'])
                        <span class="meta desktop-only">
                            <span class="name">{{ $navUser->name }}</span>
                            <span class="role">{{ $navUser->isAdmin() ? 'Admin' : 'Pemain' }}</span>
                        </span>
                        <svg style="width:15px;height:15px;color:var(--text-faint)"><use href="#i-chev"/></svg>
                    </summary>
                    <div class="dropdown" style="width:250px">
                        <div style="padding:8px 10px 10px;border-bottom:1px solid var(--border);margin-bottom:6px">
                            <b class="small">{{ $navUser->name }}</b>
                            <div class="faint" style="font-size:.72rem">{{ $navUser->email }}</div>
                        </div>
                        @if($navUser->player)
                            <a class="dd-item" href="{{ route('profile.edit') }}"><svg style="width:18px;height:18px"><use href="#i-user"/></svg><span class="t">Profil Saya</span></a>
                        @endif
                        @if($navUser->isAdmin())
                            <a class="dd-item" href="{{ route('admin.dashboard') }}"><svg style="width:18px;height:18px"><use href="#i-shield"/></svg><span class="t">Panel Admin</span></a>
                        @endif
                        <a class="dd-item" href="{{ route('notifications.index') }}"><svg style="width:18px;height:18px"><use href="#i-bell"/></svg><span class="t">Notifikasi</span></a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dd-item" type="submit" style="width:100%"><svg style="width:18px;height:18px;color:var(--danger)"><use href="#i-logout"/></svg><span class="t" style="color:var(--danger)">Keluar</span></button>
                        </form>
                    </div>
                </details>
            @else
                <a class="btn btn-primary btn-sm" style="height:40px" href="{{ route('login') }}">Masuk</a>
            @endauth
        </div>
    </div>
</nav>

<main class="container" style="min-height:calc(100vh - 200px);padding-bottom:44px">
    @yield('content')
</main>

<footer class="desktop-only" style="border-top:1px solid var(--border);margin-top:26px;padding:20px 0 26px">
    <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
        <span class="small muted">&copy; {{ date('Y') }} <b>Padelio</b> — Padel Ladder Indonesia</span>
        <span class="small faint" style="display:flex;gap:14px">
            <a href="{{ route('about') }}">Tentang &amp; Cara Kerja</a>
            <span>Ranking transparan · Challenge · Sewa lapangan</span>
        </span>
    </div>
</footer>

<nav class="bottom-nav">
    @auth
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-home"/></svg>Beranda</a>
        <a href="{{ route('leaderboard') }}" class="{{ request()->routeIs('leaderboard') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-trophy"/></svg>Ranking</a>
        <a href="{{ route('rent') }}" class="{{ request()->routeIs('rent') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-cal"/></svg>Sewa</a>
        <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-event"/></svg>Event</a>
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-user"/></svg>Profil</a>
    @else
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-home"/></svg>Beranda</a>
        <a href="{{ route('leaderboard') }}" class="{{ request()->routeIs('leaderboard') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-trophy"/></svg>Ranking</a>
        <a href="{{ route('matches.index') }}" class="{{ request()->routeIs('matches.*') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-match"/></svg>Main</a>
        <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-event"/></svg>Event</a>
        <a href="{{ route('stats') }}" class="{{ request()->routeIs('stats') ? 'active' : '' }}"><svg class="b-ico"><use href="#i-chart"/></svg>Statistik</a>
    @endauth
</nav>

@include('partials.flash')
<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
