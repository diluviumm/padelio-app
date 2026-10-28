<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 16)->default('user')->index();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name', 60)->unique();
            $table->string('city', 80)->default('Jakarta');
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('hand', 10)->default('Kanan');
            $table->string('style', 20)->default('All-round');
            $table->string('bio', 500)->default('');
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('rating')->default(1200);
            $table->string('level', 30)->default('upper-beginner');
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->integer('streak')->default(0);
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->index('rating');
            $table->index('level');
        });

        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('venue', 120);
            $table->string('surface', 20)->default('Synthetic');
            $table->string('area', 20)->default('Outdoor');
            $table->boolean('lights')->default(true);
            $table->unsignedInteger('rate_peak')->default(150000);
            $table->unsignedInteger('rate_off')->default(100000);
            $table->decimal('rating', 2, 1)->default(4.5);
            $table->json('facilities')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('court_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('slot', 5);
            $table->string('reason', 120)->default('');
            $table->timestamps();
            $table->unique(['court_id', 'date', 'slot']);
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->unsignedInteger('price');
            $table->string('unit', 20)->default('/sesi');
            $table->unsignedInteger('deposit')->default(0);
            $table->integer('stock')->default(0);
            $table->string('icon', 30)->default('i-gear');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('to_player_id')->constrained('players')->cascadeOnDelete();
            $table->string('status', 24)->default('pending_accept');
            $table->string('note', 200)->default('');
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('court_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['to_player_id', 'status']);
            $table->index(['from_player_id', 'status']);
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('a_id')->constrained('players');
            $table->foreignId('b_id')->constrained('players');
            $table->foreignId('winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->string('status', 24)->default('pending_confirm');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->integer('delta_a')->nullable();
            $table->integer('delta_b')->nullable();
            $table->timestamp('played_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index(['a_id', 'b_id']);
        });

        Schema::create('match_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->unsignedTinyInteger('set_no');
            $table->unsignedTinyInteger('a_games');
            $table->unsignedTinyInteger('b_games');
            $table->timestamps();
            $table->unique(['match_id', 'set_no']);
        });

        Schema::create('match_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->timestamp('confirmed_at');
            $table->timestamps();
            $table->unique(['match_id', 'player_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('icon', 30)->default('i-bell');
            $table->string('message', 300);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('slot', 5);
            $table->unsignedInteger('price');
            $table->unsignedInteger('addons_total')->default(0);
            $table->unsignedInteger('total');
            $table->string('status', 16)->default('confirmed');
            $table->string('payment_method', 20)->default('QRIS');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'date']);
            $table->index(['court_id', 'date']);
        });

        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('qty')->default(1);
            $table->unsignedInteger('price');
            $table->timestamps();
            $table->unique(['booking_id', 'addon_id']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('text', 500)->default('');
            $table->timestamps();
            $table->unique(['court_id', 'user_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 30)->default('Turnamen');
            $table->string('detail', 300)->default('');
            $table->string('status', 16)->default('open');
            $table->timestamp('starts_at')->nullable();
            $table->string('icon', 30)->default('i-trophy');
            $table->string('color', 20)->default('#2563eb');
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('pending');
            $table->timestamps();
            $table->unique(['event_id', 'player_id']);
        });

        Schema::create('brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('size');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bracket_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bracket_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('round');
            $table->unsignedTinyInteger('position');
            $table->foreignId('a_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('b_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->timestamps();
            $table->unique(['bracket_id', 'round', 'position']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('subject_type', 80)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index('actor_id');
        });

        DB::statement("CREATE UNIQUE INDEX bookings_active_slot_unique ON bookings (court_id, date, slot) WHERE status IN ('hold','confirmed')");
        DB::statement('ALTER TABLE match_sets ADD CONSTRAINT match_sets_games_range CHECK (a_games BETWEEN 0 AND 7 AND b_games BETWEEN 0 AND 7)');
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_rating_range CHECK (rating BETWEEN 1 AND 5)');
        DB::statement('ALTER TABLE addons ADD CONSTRAINT addons_stock_nonnegative CHECK (stock >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('bracket_matches');
        Schema::dropIfExists('brackets');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('match_confirmations');
        Schema::dropIfExists('match_sets');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('court_maintenance');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('players');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
