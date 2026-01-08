<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Requests\StoreMovieRequest;



class MovieController extends Controller
{
    public function store(StoreMovieRequest $request)
    {
        DB::transaction(function () use ($request, &$movie) {

            $movie = Movie::create($request->only([
                'title',
                'description',
                'release_year',
                'duration',
                'language',
                'genre',
                'status',
                'rental_price',
                'purchase_price',
                'rental_period',
                'free_preview',
                'preview_duration',
                'seo_title',
                'seo_description',
                'seo_keywords',
            ]));

            // Media uploads
            if ($request->hasFile('poster')) {
                $movie->poster_path = $request->file('poster')->store('movies/posters', 'public');
            }

            if ($request->hasFile('trailer')) {
                $movie->trailer_path = $request->file('trailer')->store('movies/trailers', 'public');
            }

            if ($request->hasFile('movie')) {
                $movie->movie_path = $request->file('movie')->store('movies/full', 'public');
            }

            $movie->save();

            // Cast
            if ($request->casts) {
                foreach ($request->casts as $cast) {
                    $movie->casts()->create(['name' => $cast]);
                }
            }

            // Tags
            if ($request->tags) {
                $tagIds = collect($request->tags)->map(function ($tag) {
                    return Tag::firstOrCreate(['name' => $tag])->id;
                });
                $movie->tags()->sync($tagIds);
            }

            // Subtitles
            if ($request->hasFile('subtitles')) {
                foreach ($request->file('subtitles') as $file) {
                    $movie->subtitles()->create([
                        'file_path' => $file->store('movies/subtitles', 'public')
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Movie created successfully',
            'movie' => $movie->load(['casts', 'tags', 'subtitles'])
        ], 201);
    }

    public function index()
    {
        // Load related casts, tags, and subtitles
        $movies = Movie::with(['casts', 'tags', 'subtitles'])->get();

        return response()->json($movies, 200);
    }
}
