<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BracketController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MatchFlowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');
Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
Route::get('/courts', [CourtController::class, 'index'])->name('courts.index');
Route::get('/courts/{court}', [CourtController::class, 'show'])->name('courts.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}/bracket', [BracketController::class, 'show'])->name('bracket.show');
Route::get('/stats', [StatsController::class, 'index'])->name('stats');
Route::view('/about', 'about')->name('about');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::post('/challenges', [ChallengeController::class, 'store'])->middleware('throttle:10,60')->name('challenges.store');
    Route::post('/challenges/{challenge}/accept', [ChallengeController::class, 'accept'])->name('challenges.accept');
    Route::post('/challenges/{challenge}/decline', [ChallengeController::class, 'decline'])->name('challenges.decline');
    Route::post('/challenges/{challenge}/cancel', [ChallengeController::class, 'cancel'])->name('challenges.cancel');
    Route::post('/challenges/{challenge}/schedule', [ChallengeController::class, 'schedule'])->name('challenges.schedule');

    Route::get('/matches/{challenge}/score', [MatchFlowController::class, 'create'])->name('matches.score');
    Route::post('/matches/{challenge}/score', [MatchFlowController::class, 'store'])->middleware('throttle:30,1')->name('matches.score.store');
    Route::post('/matches/{match}/confirm', [MatchFlowController::class, 'confirm'])->name('matches.confirm');

    Route::get('/rent', [RentController::class, 'index'])->name('rent');
    Route::get('/checkout', [BookingController::class, 'checkout'])->name('checkout');
    Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:20,1')->name('bookings.store');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::post('/courts/{court}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/events/{event}/register', [EventRegistrationController::class, 'store'])->name('events.register');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfilePhotoController::class, 'store'])->middleware('throttle:5,1')->name('profile.photo');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'readAll'])->name('notifications.read');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/results', [Admin\ResultController::class, 'index'])->name('results');
    Route::post('/matches/{match}/approve', [Admin\ResultController::class, 'approve'])->name('matches.approve');
    Route::post('/matches/{match}/reject', [Admin\ResultController::class, 'reject'])->name('matches.reject');

    Route::get('/players', [Admin\PlayerController::class, 'index'])->name('players');
    Route::put('/players/{player}', [Admin\PlayerController::class, 'update'])->name('players.update');
    Route::post('/players/{player}/suspend', [Admin\PlayerController::class, 'suspend'])->name('players.suspend');

    Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings');
    Route::put('/bookings/{booking}', [Admin\BookingController::class, 'update'])->name('bookings.update');

    Route::get('/courts', [Admin\CourtController::class, 'index'])->name('courts');
    Route::post('/courts', [Admin\CourtController::class, 'store'])->name('courts.store');
    Route::put('/courts/{court}', [Admin\CourtController::class, 'update'])->name('courts.update');
    Route::delete('/courts/{court}', [Admin\CourtController::class, 'destroy'])->name('courts.destroy');
    Route::post('/courts/{court}/maintenance', [Admin\CourtController::class, 'toggleMaintenance'])->name('courts.maintenance');

    Route::get('/addons', [Admin\AddonController::class, 'index'])->name('addons');
    Route::post('/addons', [Admin\AddonController::class, 'store'])->name('addons.store');
    Route::put('/addons/{addon}', [Admin\AddonController::class, 'update'])->name('addons.update');
    Route::delete('/addons/{addon}', [Admin\AddonController::class, 'destroy'])->name('addons.destroy');
    Route::post('/addons/{addon}/stock', [Admin\AddonController::class, 'stock'])->name('addons.stock');

    Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews');
    Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/events', [Admin\EventController::class, 'index'])->name('events');
    Route::post('/events', [Admin\EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [Admin\EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [Admin\EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}/registrations', [Admin\EventController::class, 'registrations'])->name('events.registrations');
    Route::post('/events/{event}/registrations/{registration}', [Admin\EventController::class, 'setRegistration'])->name('events.registrations.set');
    Route::get('/events/{event}/bracket', [Admin\EventController::class, 'bracket'])->name('events.bracket.show');
    Route::post('/events/{event}/bracket', [Admin\EventController::class, 'generateBracket'])->name('events.bracket.generate');
    Route::delete('/events/{event}/bracket', [Admin\EventController::class, 'destroyBracket'])->name('events.bracket.destroy');
    Route::post('/events/{event}/bracket/{bracketMatch}', [Admin\EventController::class, 'advance'])->name('events.bracket.advance');
});
