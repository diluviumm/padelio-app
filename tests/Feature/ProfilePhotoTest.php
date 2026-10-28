<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        Storage::fake('public');
    }

    private function user(): User
    {
        return User::where('email', 'user@padelio.id')->firstOrFail();
    }

    public function test_valid_image_is_stored_and_resized(): void
    {
        $this->actingAs($this->user())
            ->post(route('profile.photo'), ['photo' => UploadedFile::fake()->image('avatar.jpg', 400, 300)])
            ->assertSessionHasNoErrors();

        $player = Player::where('name', 'Hadi')->firstOrFail();
        $this->assertNotNull($player->photo_path);
        Storage::disk('public')->assertExists($player->photo_path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($player->photo_path));
        $this->assertSame(200, $width);
        $this->assertSame(200, $height);
    }

    public function test_non_image_file_is_rejected(): void
    {
        $this->actingAs($this->user())
            ->post(route('profile.photo'), ['photo' => UploadedFile::fake()->create('evil.jpg', 10, 'image/jpeg')])
            ->assertSessionHasErrors('photo');

        $this->assertNull(Player::where('name', 'Hadi')->value('photo_path'));
    }
}
