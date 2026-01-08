<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SecureStreamController extends Controller
{
    public function stream(Movie $movie)
    {
        abort_unless(auth()->check(), 403);

        $hasPurchased = Purchase::where('user_id', auth()->id())
            ->where('movie_id', $movie->id)
            ->exists();

        abort_unless($hasPurchased, 403);

        return response()->file(storage_path("app/{$movie->video_path}"));
    }
}

