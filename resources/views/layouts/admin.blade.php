<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Padelio</title>
    <link rel="icon" type="image/png" href="{{ asset('img/brand-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2">
</head>
<body>
@include('partials.sprite')
@php($pendingCount = \App\Models\MatchGame::where('status', 'pending')->count())
@php($adminUser = auth()->user())

<div class="admin-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}" style="padding:6px 12px 16px">
            <img src="{{ asset('img/brand-mark.webp') }}" alt="Padelio" style="height:26px;width:auto">
            <span>Padel<b>io</b> <span style="font-size:.62rem;background:var(--purple-soft);color:var(--purple);padding:2px 7px;border-radius:99px;vertical-align:2px">ADMIN</span></span>
        </a>
        <div class="s-title">Kelola</div>
        <a class="s-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><svg style="width:18px;height:18px"><use href="#i-home"/></svg>Dashboard</a>
        <a class="s-link {{ request()->routeIs('admin.results') ? 'active' : '' }}" href="{{ route('admin.results') }}"><svg style="width:18px;height:18px"><use href="#i-match"/></svg>Hasil @if($pendingCount)<span class="badge-n">{{ $pendingCount }}</span>@endif</a>
        <a class="s-link {{ request()->routeIs('admin.players') ? 'active' : '' }}" href="{{ route('admin.players') }}"><svg style="width:18px;height:18px"><use href="#i-users"/></svg>Pemain</a>
        <a class="s-link {{ request()->routeIs('admin.bookings') ? 'active' : '' }}" href="{{ route('admin.bookings') }}"><svg style="width:18px;height:18px"><use href="#i-cal"/></svg>Booking</a>
        <a class="s-link {{ request()->routeIs('admin.courts') ? 'active' : '' }}" href="{{ route('admin.courts') }}"><svg style="width:18px;height:18px"><use href="#i-pin"/></svg>Lapangan</a>
        <a class="s-link {{ request()->routeIs('admin.addons') ? 'active' : '' }}" href="{{ route('admin.addons') }}"><svg style="width:18px;height:18px"><use href="#i-gear"/></svg>Peralatan</a>
        <a class="s-link {{ request()->routeIs('admin.reviews') ? 'active' : '' }}" href="{{ route('admin.reviews') }}"><svg style="width:18px;height:18px"><use href="#i-star"/></svg>Review</a>
        <a class="s-link {{ request()->routeIs('admin.events*') ? 'active' : '' }}" href="{{ route('admin.events') }}"><svg style="width:18px;height:18px"><use href="#i-event"/></svg>Event</a>

        <div style="margin-top:auto;padding-top:14px">
            <a class="s-link" href="{{ route('home') }}"><svg style="width:18px;height:18px"><use href="#i-eye"/></svg>Lihat situs</a>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-top">
            <div class="container" style="padding:0">
                <div>
                    <div class="faint small" style="text-transform:uppercase;letter-spacing:.08em">Panel Admin</div>
                    <h1 id="page-title" style="font-size:1.2rem">@yield('heading', 'Dashboard')</h1>
                </div>
                <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
                    <button class="icon-btn" type="button" data-theme-toggle aria-label="Ganti tema"><svg style="width:21px;height:21px"><use href="#i-sun"/></svg></button>
                    <details class="dd">
                        <summary class="user-chip" aria-label="Menu admin">
                            @include('partials.avatar', ['player' => $adminUser->player, 'size' => 'sm'])
                            <span class="meta desktop-only"><span class="name">{{ $adminUser->name }}</span><span class="role">Admin</span></span>
                        </summary>
                        <div class="dropdown" style="width:230px">
                            <a class="dd-item" href="{{ route('home') }}"><svg style="width:18px;height:18px"><use href="#i-eye"/></svg><span class="t">Lihat situs</span></a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="dd-item" type="submit" style="width:100%"><svg style="width:18px;height:18px;color:var(--danger)"><use href="#i-logout"/></svg><span class="t" style="color:var(--danger)">Keluar</span></button></form>
                        </div>
                    </details>
                </div>
            </div>
            <div class="mobile-tabs">
                <a class="tab {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="tab {{ request()->routeIs('admin.results') ? 'active' : '' }}" href="{{ route('admin.results') }}">Hasil</a>
                <a class="tab {{ request()->routeIs('admin.players') ? 'active' : '' }}" href="{{ route('admin.players') }}">Pemain</a>
                <a class="tab {{ request()->routeIs('admin.bookings') ? 'active' : '' }}" href="{{ route('admin.bookings') }}">Booking</a>
                <a class="tab {{ request()->routeIs('admin.courts') ? 'active' : '' }}" href="{{ route('admin.courts') }}">Lapangan</a>
                <a class="tab {{ request()->routeIs('admin.addons') ? 'active' : '' }}" href="{{ route('admin.addons') }}">Peralatan</a>
                <a class="tab {{ request()->routeIs('admin.reviews') ? 'active' : '' }}" href="{{ route('admin.reviews') }}">Review</a>
                <a class="tab {{ request()->routeIs('admin.events*') ? 'active' : '' }}" href="{{ route('admin.events') }}">Event</a>
            </div>
        </div>

        @yield('content')
    </div>
</div>

@include('partials.flash')
<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
