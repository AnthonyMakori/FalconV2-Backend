<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function movieGenres()
    {
        // Collect unique genres from movies table
        $genres = Movie::query()
            ->select('genre')
            ->whereNotNull('genre')
            ->distinct()
            ->get()
            ->map(function ($movie, $index) {
                return [
                    'id' => $index + 1,
                    'name' => $movie->genre,
                ];
            })
            ->values();

        return response()->json([
            'genres' => $genres
        ]);
    }
}
