<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class MovieController extends Controller
{
    public function uploadMovie(Request $request)
    {
        try {
            // Validate input (note: max size for movie is in KB; 6GB = 6291456 KB)
            $validated = $request->validate([
                'title'         => 'required|string|max:255',
                'description'   => 'required|string',
                'price'         => 'required|numeric',
                'poster'        => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', 
                'category'      => 'required|string',
                'currency'      => 'required|string',
                'date_released' => 'required|date',
                'movie' => 'required|mimes:mp4,mkv,ts|max:6291456',
            ]);

            // Store poster
            // $posterPath = $request->file('poster')->store('movie_posters', 'public');

            // Store movie file
            // $moviePath = $request->file('movie')->store('movies', 'public');

            // save to assets folder
             // Prepare paths
        $movie = $request->file('movie');
        $poster = $request->file('poster');

        $movieName = time() . '_' . $movie->getClientOriginalName();
        $posterName = time() . '_' . $poster->getClientOriginalName();

        $movieDir = base_path('assets/movies');
        $posterDir = base_path('assets/movie_posters');

        // Create folders if they don't exist
        File::ensureDirectoryExists($movieDir);
        File::ensureDirectoryExists($posterDir);

        // Move files
        $movie->move($movieDir, $movieName);
        $poster->move($posterDir, $posterName);

        // Save paths relative to project root
        $moviePath = 'assets/movies/' . $movieName;
        $posterPath = 'assets/movie_posters/' . $posterName;

            // Save movie details in the database
            $movie = Movie::create([
                'title'        => $validated['title'],
                'description'  => $validated['description'],
                'price'        => $validated['price'],
                'poster'       => $posterPath,
                'category'     => $validated['category'],
                'currency'     => $validated['currency'],
                'date_released'=> $validated['date_released'],
                'movie'   => $moviePath,
            ]);

            return response()->json([
                'message' => 'Movie uploaded successfully',
                'movie'   => $movie
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMovies()
    {
        $movies = Movie::all();
        return response()->json($movies);
    }

    public function comingSoonMovies()
    {
        $today = Carbon::today();
        $movies = Movie::where('date_released', '>', $today)
                        ->orderBy('date_released', 'asc')
                        ->get();

        return response()->json($movies);
    }
}


