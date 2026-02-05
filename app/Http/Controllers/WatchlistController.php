<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Watchlist;

class WatchlistController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return $user->watchlists()
            ->with('movie:id,title,thumbnail,duration_seconds')
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'movie_id' => $item->movie->id,
                'title' => $item->movie->title,
                'thumbnail' => $item->movie->thumbnail,
                'addedOn' => $item->created_at->diffForHumans(),
            ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
        ]);

        auth()->user()->watchlists()->firstOrCreate([
            'movie_id' => $request->movie_id,
        ]);

        return response()->json(['message' => 'Added to watchlist']);
    }

    public function destroy(Movie $movie)
    {
        auth()->user()
            ->watchlists()
            ->where('movie_id', $movie->id)
            ->delete();

        return response()->json(['message' => 'Removed from watchlist']);
    }
}



