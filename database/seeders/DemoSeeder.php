<?php
namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ChallengeStatus;
use App\Enums\EventStatus;
use App\Enums\MatchStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\Challenge;
use App\Models\Court;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MatchConfirmation;
use App\Models\MatchGame;
use App\Models\MatchSet;
use App\Models\Player;
use App\Models\Review;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Padelio',
            'email' => 'admin@padelio.id',
            'password' => 'admin12345',
            'role' => 'admin',
        ]);

        $hadiUser = User::create([
            'name' => 'Hadi',
            'email' => 'user@padelio.id',
            'password' => 'user12345',
            'role' => 'user',
        ]);

        $andiUser = User::create([
            'name' => 'Andi',
            'email' => 'andi@padelio.id',
            'password' => 'andi12345',
            'role' => 'user',
        ]);

        $rioUser = User::create([
            'name' => 'Rio',
            'email' => 'rio@padelio.id',
            'password' => 'rio12345',
            'role' => 'user',
        ]);

        $rows = [
            ['Agus', 3450, 55, 10, 9, 'Jakarta', 'upper-intermediate'],
            ['Rina', 3320, 50, 12, 5, 'Surabaya', 'upper-intermediate'],
            ['Eko', 3180, 47, 15, 3, 'Bandung', 'upper-intermediate'],
            ['Vina', 3050, 44, 17, 2, 'Yogyakarta', 'upper-intermediate'],
            ['Rudi', 2910, 40, 19, 1, 'Semarang', 'upper-intermediate'],
            ['Sinta', 2780, 37, 21, 0, 'Jakarta', 'upper-intermediate'],
            ['Budi', 2350, 41, 7, 6, 'Jakarta', 'intermediate'],
            ['Andi', 2320, 38, 9, 3, 'Bandung', 'intermediate'],
            ['Rio', 2290, 35, 11, 2, 'Surabaya', 'intermediate'],
            ['Hadi', 2240, 32, 11, 5, 'Yogyakarta', 'intermediate'],
            ['Fajar', 2210, 28, 14, 1, 'Semarang', 'intermediate'],
            ['Arif', 2180, 25, 16, 0, 'Malang', 'intermediate'],
            ['Putri', 1850, 22, 8, 4, 'Jakarta', 'upper-beginner'],
            ['Dimas', 1780, 20, 10, 2, 'Bandung', 'upper-beginner'],
            ['Lina', 1650, 18, 11, 1, 'Surabaya', 'upper-beginner'],
            ['Bayu', 1520, 15, 12, 0, 'Medan', 'upper-beginner'],
            ['Nina', 1400, 13, 13, 0, 'Makassar', 'upper-beginner'],
            ['Yoga', 1280, 11, 14, 0, 'Palembang', 'upper-beginner'],
            ['Sari', 820, 12, 3, 5, 'Jakarta', 'beginner'],
            ['Dewi', 780, 10, 5, 2, 'Bogor', 'beginner'],
            ['Rizky', 740, 9, 6, 1, 'Depok', 'beginner'],
            ['Tono', 690, 8, 7, 0, 'Bekasi', 'beginner'],
            ['Maya', 640, 6, 8, 0, 'Jakarta', 'beginner'],
            ['Joko', 580, 5, 9, 0, 'Tangerang', 'beginner'],
        ];

        $players = [];
        foreach ($rows as [$name, $rating, $wins, $losses, $streak, $city, $level]) {
            $players[$name] = Player::create([
                'name' => $name,
                'rating' => $rating,
                'wins' => $wins,
                'losses' => $losses,
                'streak' => $streak,
                'city' => $city,
                'level' => $level,
                'age' => 24 + (strlen($name) % 12),
                'hand' => 'Kanan',
                'style' => 'All-round',
                'bio' => $name === 'Hadi' ? 'Kapten komunitas padel Jogja. Fokus: voli dan bandeja yang konsisten.' : '',
            ]);
        }

        $players['Hadi']->update(['user_id' => $hadiUser->id]);
        $players['Andi']->update(['user_id' => $andiUser->id]);
        $players['Rio']->update(['user_id' => $rioUser->id]);

        $courts = [
            ['GBK Lap 3', 'GBK Senayan', 'Synthetic', 'Indoor', true, 180000, 120000, 4.7],
            ['GBK Lap 4', 'GBK Senayan', 'Hard', 'Outdoor', true, 150000, 100000, 4.5],
            ['Padel Kemang 1', 'Kemang Padel Club', 'Synthetic', 'Semi-outdoor', true, 200000, 140000, 4.8],
            ['Padel Kemang 2', 'Kemang Padel Club', 'Clay', 'Outdoor', false, 120000, 90000, 4.2],
            ['BSD Court A', 'BSD Padel Arena', 'Synthetic', 'Indoor', true, 175000, 125000, 4.6],
            ['Senayan Court', 'Senayan Sports Hub', 'Carpet', 'Indoor', true, 160000, 110000, 4.4],
        ];

        foreach ($courts as [$name, $venue, $surface, $area, $lights, $peak, $off, $rating]) {
            Court::create([
                'name' => $name, 'venue' => $venue, 'surface' => $surface, 'area' => $area,
                'lights' => $lights, 'rate_peak' => $peak, 'rate_off' => $off, 'rating' => $rating,
                'facilities' => ['Locker', 'Shower', 'Kantin', 'Parkir'],
            ]);
        }

        $courtIds = Court::orderBy('id')->pluck('id')->values();

        $addons = [
            ['Raket Padel (2 pcs)', 50000, '/sesi', 200000, 12, 'i-racket'],
            ['Bola Padel (3 pcs)', 35000, '/sesi', 0, 30, 'i-ball'],
            ['Sepatu Padel', 40000, '/sesi', 100000, 8, 'i-gear'],
            ['Handuk', 15000, '/sesi', 0, 20, 'i-star'],
            ['Air Mineral', 10000, '/buah', 0, 60, 'i-bolt'],
            ['Pelatih 1 jam', 150000, '/jam', 0, 6, 'i-medal'],
            ['Ball Machine', 100000, '/sesi', 0, 3, 'i-bolt'],
            ['Grip Tape', 15000, '/buah', 0, 40, 'i-gear'],
        ];

        foreach ($addons as [$name, $price, $unit, $deposit, $stock, $icon]) {
            Addon::create(compact('name', 'price', 'unit', 'deposit', 'stock', 'icon'));
        }

        $addonIds = Addon::orderBy('id')->pluck('id')->values();

        $openEvent = Event::create([
            'name' => 'Turnamen Mini Padelio', 'type' => 'Turnamen',
            'detail' => 'Turnamen 8 pemain, sistem gugur. Daftar sekarang, peserta disetujui admin.',
            'status' => EventStatus::Open, 'starts_at' => now()->addDays(7),
            'icon' => 'i-trophy', 'color' => '#2563eb',
        ]);

        $liveEvent = Event::create([
            'name' => 'King of Court', 'type' => 'King of Court',
            'detail' => 'Bracket sudah live. Lihat bagan dan dukung favoritmu.',
            'status' => EventStatus::Live, 'starts_at' => now()->subDay(),
            'icon' => 'i-fire', 'color' => '#f97316',
        ]);

        Event::create([
            'name' => 'Mini League September', 'type' => 'Mini League',
            'detail' => 'Liga mini 4 pekan. Pendaftaran dibuka minggu depan.',
            'status' => EventStatus::Soon, 'starts_at' => now()->addDays(14),
            'icon' => 'i-cal', 'color' => '#38bdf8',
        ]);

        foreach (['Agus', 'Rina', 'Eko', 'Vina', 'Rudi', 'Sinta', 'Budi', 'Andi'] as $name) {
            EventRegistration::create([
                'event_id' => $liveEvent->id,
                'player_id' => $players[$name]->id,
                'status' => 'accepted',
            ]);
        }

        EventRegistration::create([
            'event_id' => $openEvent->id,
            'player_id' => $players['Hadi']->id,
            'status' => 'pending',
        ]);

        Challenge::create([
            'from_player_id' => $players['Rio']->id,
            'to_player_id' => $players['Hadi']->id,
            'status' => ChallengeStatus::PendingAccept,
            'note' => 'menantang Anda',
        ]);

        Challenge::create([
            'from_player_id' => $players['Hadi']->id,
            'to_player_id' => $players['Andi']->id,
            'status' => ChallengeStatus::Scheduled,
            'scheduled_at' => now()->addDays(2)->setTime(8, 0),
            'court_id' => $courtIds[0],
            'responded_at' => now()->subDay(),
        ]);

        Challenge::create([
            'from_player_id' => $players['Fajar']->id,
            'to_player_id' => $players['Hadi']->id,
            'status' => ChallengeStatus::PendingAccept,
            'note' => 'main sore?',
        ]);

        $doneChallenge = Challenge::create([
            'from_player_id' => $players['Hadi']->id,
            'to_player_id' => $players['Rio']->id,
            'status' => ChallengeStatus::Done,
            'scheduled_at' => now()->subDays(2)->setTime(19, 0),
            'court_id' => $courtIds[2],
            'responded_at' => now()->subDays(4),
            'completed_at' => now()->subDays(2),
        ]);

        $match = MatchGame::create([
            'challenge_id' => $doneChallenge->id,
            'a_id' => $players['Hadi']->id,
            'b_id' => $players['Rio']->id,
            'winner_id' => $players['Hadi']->id,
            'status' => MatchStatus::PendingConfirm,
            'played_at' => now()->subDays(2),
        ]);

        foreach ([[6, 3], [6, 4]] as $index => [$a, $b]) {
            MatchSet::create(['match_id' => $match->id, 'set_no' => $index + 1, 'a_games' => $a, 'b_games' => $b]);
        }

        MatchConfirmation::create(['match_id' => $match->id, 'player_id' => $players['Hadi']->id, 'confirmed_at' => now()->subDay()]);

        $approvedData = [
            ['Hadi', 'Budi', [[6, 4], [6, 3]], 40, -10, 5],
            ['Hadi', 'Andi', [[4, 6], [6, 4], [7, 6]], 40, -10, 10],
            ['Budi', 'Hadi', [[6, 3], [6, 4]], 15, -10, 15],
        ];

        foreach ($approvedData as [$a, $b, $sets, $deltaA, $deltaB, $daysAgo]) {
            $c = Challenge::create([
                'from_player_id' => $players[$a]->id,
                'to_player_id' => $players[$b]->id,
                'status' => ChallengeStatus::Done,
                'scheduled_at' => now()->subDays($daysAgo)->setTime(9, 0),
                'court_id' => $courtIds[1],
                'responded_at' => now()->subDays($daysAgo + 2),
                'completed_at' => now()->subDays($daysAgo),
            ]);

            $m = MatchGame::create([
                'challenge_id' => $c->id,
                'a_id' => $players[$a]->id,
                'b_id' => $players[$b]->id,
                'winner_id' => $players[$a]->id,
                'status' => MatchStatus::Approved,
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays($daysAgo)->addHour(),
                'delta_a' => $deltaA,
                'delta_b' => $deltaB,
                'played_at' => now()->subDays($daysAgo),
            ]);

            foreach ($sets as $i => [$ga, $gb]) {
                MatchSet::create(['match_id' => $m->id, 'set_no' => $i + 1, 'a_games' => $ga, 'b_games' => $gb]);
            }

            MatchConfirmation::create(['match_id' => $m->id, 'player_id' => $players[$a]->id, 'confirmed_at' => now()->subDays($daysAgo)]);
            MatchConfirmation::create(['match_id' => $m->id, 'player_id' => $players[$b]->id, 'confirmed_at' => now()->subDays($daysAgo)]);
        }

        $booking = Booking::create([
            'code' => 'BK-DEMO0001',
            'user_id' => $hadiUser->id,
            'court_id' => $courtIds[0],
            'date' => Carbon::tomorrow()->toDateString(),
            'slot' => '10:00',
            'price' => 120000,
            'addons_total' => 50000,
            'total' => 170000,
            'status' => BookingStatus::Confirmed,
            'payment_method' => 'QRIS',
        ]);

        BookingAddon::create(['booking_id' => $booking->id, 'addon_id' => $addonIds[0], 'qty' => 1, 'price' => 50000]);

        Booking::create([
            'code' => 'BK-DEMO0002',
            'user_id' => $hadiUser->id,
            'court_id' => $courtIds[2],
            'date' => Carbon::tomorrow()->addDay()->toDateString(),
            'slot' => '18:00',
            'price' => 200000,
            'addons_total' => 0,
            'total' => 200000,
            'status' => BookingStatus::Confirmed,
            'payment_method' => 'Transfer Bank',
        ]);

        Review::create(['court_id' => $courtIds[0], 'user_id' => $hadiUser->id, 'rating' => 5, 'text' => 'Lapangan bersih, lampu terang, locker nyaman.']);
        Review::create(['court_id' => $courtIds[2], 'user_id' => $hadiUser->id, 'rating' => 4, 'text' => 'Venue bagus, parkir luas. Agak ramai sore.']);

        UserNotification::create(['user_id' => $hadiUser->id, 'icon' => 'i-sword', 'message' => 'Rio menantangmu — cek halaman Tantangan.']);
        UserNotification::create(['user_id' => $hadiUser->id, 'icon' => 'i-match', 'message' => 'Konfirmasi skor vs Rio menunggumu.']);
    }
}
