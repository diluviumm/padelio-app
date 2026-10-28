<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $player = $request->user()->player;
        abort_unless($player, 403);

        $contents = file_get_contents($request->file('photo')->getRealPath());
        $source = @imagecreatefromstring($contents);

        if (! $source) {
            return back()->withErrors(['photo' => 'Gambar tidak valid.']);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);
        $srcX = (int) (($width - $side) / 2);
        $srcY = (int) (($height - $side) / 2);

        $canvas = imagecreatetruecolor(200, 200);
        imagecopyresampled($canvas, $source, 0, 0, $srcX, $srcY, 200, 200, $side, $side);

        Storage::disk('public')->makeDirectory('avatars');
        $name = 'avatars/' . $player->id . '-' . bin2hex(random_bytes(6)) . '.jpg';
        imagejpeg($canvas, Storage::disk('public')->path($name), 82);
        imagedestroy($source);
        imagedestroy($canvas);

        if ($player->photo_path) {
            Storage::disk('public')->delete($player->photo_path);
        }

        $player->update(['photo_path' => $name]);

        return back()->with('status', 'Foto profil diperbarui.');
    }
}
