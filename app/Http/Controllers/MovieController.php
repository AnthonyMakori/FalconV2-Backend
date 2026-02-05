<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TmdbMovieResource;



class MovieController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'release_year' => 'required|integer',
            'duration' => 'required|integer',
            'language' => 'required|string',
            'genre' => 'required|string',
            'status' => 'required|string',

            'rental_price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'rental_period' => 'nullable|integer',
            'free_preview' => 'boolean',
            'preview_duration' => 'nullable|integer',

            'poster' => 'nullable|image|max:51200',
            'trailer' => 'nullable|mimes:mp4,mov,avi|max:102400',
            'subtitles.*' => 'nullable|mimes:srt,vtt',

            'bunny_video_id' => 'nullable|string',

            'seo_title' => 'nullable|string',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {

            $movie = Movie::create([
                'title' => $request->title,
                'description' => $request->description,
                'release_year' => $request->release_year,
                'duration' => $request->duration,
                'language' => $request->language,
                'genre' => $request->genre,
                'status' => $request->status,

                'rental_price' => $request->rental_price,
                'purchase_price' => $request->purchase_price,
                'rental_period' => $request->rental_period,
                'free_preview' => $request->boolean('free_preview'),
                'preview_duration' => $request->preview_duration,

                'bunny_video_id' => $request->bunny_video_id,

                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'seo_keywords' => $request->seo_keywords,
            ]);

            /* ================= POSTER ================= */
            if ($request->hasFile('poster')) {
                $poster = $request->file('poster');
                $name = time() . '_' . $poster->getClientOriginalName();
                $dir = base_path('assets/movies/posters');

                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $poster->move($dir, $name);
                $movie->poster_path = "movies/posters/{$name}";
            }
            /* ================= TRAILER ================= */
            if ($request->hasFile('trailer')) {
                $trailer = $request->file('trailer');
                $name = time() . '_' . $trailer->getClientOriginalName();
                $dir = public_path('assets/movies/trailers');
            
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
            
                $trailer->move($dir, $name);
                $movie->trailer_path = "movies/trailers/{$name}";
            }



            /* ================= SUBTITLES ================= */
            if ($request->hasFile('subtitles')) {
                $dir = base_path('assets/movies/subtitles');

                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                foreach ($request->file('subtitles') as $subtitle) {
                    $name = time() . '_' . $subtitle->getClientOriginalName();
                    $subtitle->move($dir, $name);

                    $movie->subtitles()->create([
                        'file_path' => "movies/subtitles/{$name}"
                    ]);
                }
            }

            /* ================= CASTS ================= */
            foreach ($request->input('casts', []) as $cast) {
                $movie->casts()->create(['name' => $cast]);
            }

            /* ================= TAGS ================= */
            $tagIds = collect($request->input('tags', []))
                ->map(fn ($tag) => Tag::firstOrCreate(['name' => $tag])->id);

            $movie->tags()->sync($tagIds);

            /* ================= BUNNY STREAM ================= */
            if ($movie->bunny_video_id) {
                $movie->movie_path =
                    "https://" . config('services.bunny.pull_zone') .
                    ".b-cdn.net/{$movie->bunny_video_id}/playlist.m3u8";
            }

            $movie->save();

            return response()->json([
                'message' => 'Movie created successfully',
                'movie' => $movie->load(['casts', 'tags', 'subtitles'])
            ], 201);
        });
    }

    /* ================= TMDB-LIKE RESPONSES ================= */

    private function tmdbResponse($paginator)
    {
        return response()->json([
            'page' => $paginator->currentPage(),
            'results' => TmdbMovieResource::collection($paginator),
            'total_pages' => $paginator->lastPage(),
            'total_results' => $paginator->total(),
        ]);
    }

    public function trending()
    {
        $movies = Movie::where('status', 'published')
            ->latest()
            ->paginate(20);

        return $this->tmdbResponse($movies);
    }

    public function popular()
    {
        $movies = Movie::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    
        return $this->tmdbResponse($movies);
    }


    public function show($id)
        {
            $movie = Movie::where('id', $id)
                ->where('status', 'published')
                ->first();
        
            if (!$movie) {
                return response()->json([
                    'status_message' => 'The resource you requested could not be found.'
                ], 404);
            }
        
            return response()->json(new TmdbMovieResource($movie));
        }


    public function search(Request $request)
    {
        $movies = Movie::where('title', 'LIKE', "%{$request->query}%")
            ->paginate(20);

        return $this->tmdbResponse($movies);
    }
    
    // Get top rated movies (we'll sort by vote_count or popularity)
        public function topRated()
        {
            $movies = Movie::where('status', 'published')
                ->orderBy('vote_count', 'desc')
                ->paginate(20);
        
            return $this->tmdbResponse($movies);
        }
        
        // Now playing movies (status-based)
        public function nowPlaying()
        {
            $movies = Movie::where('status', 'published')
                ->latest()
                ->paginate(20);
        
            return $this->tmdbResponse($movies);
        }
        
        // Upcoming movies (using release_year > current year)
        public function upcoming()
        {
            $year = date('Y');
            $movies = Movie::where('status', 'published')
                ->where('release_year', '>', $year)
                ->orderBy('release_year', 'asc')
                ->paginate(20);
        
            return $this->tmdbResponse($movies);
        }
        
        // Movie credits (casts only for now)
        public function credits(Movie $movie)
        {
            return response()->json([
                'cast' => $movie->casts()->get(['id', 'name']),
                'crew' => [], // add crew later if needed
            ]);
        }
        
        // Recommendations (for simplicity, return movies in the same genre)
     public function recommendations($id)
        {
            $movie = Movie::find($id);
        
            // TMDB behavior: missing movie = empty results
            if (!$movie) {
                return response()->json([
                    'page' => 1,
                    'results' => [],
                    'total_pages' => 1,
                    'total_results' => 0,
                ]);
            }
        
           $movies = Movie::where('status', 'published')
            ->where('id', '!=', $movie->id)
            ->where('genre', $movie->genre)
            ->inRandomOrder()
            ->paginate(10);

        
           return $this->tmdbResponse($movies);

        }




    public function index()
    {
        return Movie::with(['casts', 'tags', 'subtitles'])
            ->latest()
            ->paginate(20);
    }
}
