<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WatchProgressController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'progress_seconds' => 'required|integer',
            'duration_seconds' => 'required|integer'
        ]);

        WatchProgress::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'movie_id' => $request->movie_id
            ],
            [
                'progress_seconds' => $request->progress_seconds,
                'duration_seconds' => $request->duration_seconds,
                'last_watched_at' => now()
            ]
        );

        return response()->json(['message' => 'Progress saved']);
    }

    public function list()
    {
        return WatchProgress::with('movie')
            ->where('user_id', auth()->id())
            ->orderByDesc('last_watched_at')
            ->get();
    }
}

