<?php
namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function index(Request $request)
    {
        $courts = Court::where('active', true)
            ->when($request->query('surface') && $request->query('surface') !== 'all',
                fn ($q) => $q->where('surface', $request->query('surface')))
            ->when($request->query('area') && $request->query('area') !== 'all',
                fn ($q) => $q->where('area', $request->query('area')))
            ->when($request->query('lights') && $request->query('lights') !== 'all',
                fn ($q) => $q->where('lights', $request->query('lights') === 'yes'))
            ->when($request->query('sort') === 'price',
                fn ($q) => $q->orderBy('rate_off'),
                fn ($q) => $q->orderByDesc('rating'))
            ->get();

        $reviews = \App\Models\Review::selectRaw('court_id, count(*) as total, avg(rating) as avg_rating')
            ->groupBy('court_id')
            ->get()
            ->keyBy('court_id');

        return view('courts', compact('courts', 'reviews'));
    }

    public function show(Court $court)
    {
        $reviews = $court->reviews()->with('user')->latest()->get();
        $myReview = auth()->check() ? $reviews->firstWhere('user_id', auth()->id()) : null;

        return view('court', compact('court', 'reviews', 'myReview'));
    }
}
