<?php
namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Court $court, Request $request)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['nullable', 'string', 'max:500'],
        ]);

        Review::updateOrCreate(
            ['court_id' => $court->id, 'user_id' => $request->user()->id],
            ['rating' => $data['rating'], 'text' => $data['text'] ?? '']
        );

        return back()->with('status', 'Review disimpan.');
    }
}
