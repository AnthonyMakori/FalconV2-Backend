<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TmdbMovieResource extends JsonResource
{
    /**
     * Normalize asset paths so the frontend can always resolve them.
     */
    protected function normalizeAssetPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Already relative to assets
        if (str_starts_with($path, 'movies/')) {
            return $path;
        }

        // Remove accidental leading slashes or assets prefix
        return ltrim(
            str_replace('assets/', '', $path),
            '/'
        );
    }

    public function toArray($request)
    {
        return [
            /* ================= TMDB CORE ================= */
            'id' => $this->id,
            'title' => $this->title,
            'original_title' => $this->title,

            // Keep overview behavior unchanged
            'overview' => $this->description,

            /**
             * RELATIVE paths only.
             * Frontend resolves to:
             * https://api.falconeyephilmz.com/assets/{path}
             */
            'poster_path' => $this->normalizeAssetPath($this->poster_path),

            // TMDB expects a backdrop_path
            'backdrop_path' => $this->normalizeAssetPath($this->poster_path),

            'release_date' => $this->release_year
                ? "{$this->release_year}-01-01"
                : null,

            'vote_average' => 0,
            'vote_count' => 0,
            'popularity' => 0,
            'adult' => false,
            'video' => false,
            'genre_ids' => [],

            /* ================= FALCONEYE EXTRAS ================= */
            'movie_path' => $this->movie_path,
            'trailer_path' => $this->trailer_path,
            'free_preview' => (bool) $this->free_preview,
            
            /* ================= PAYMENT INFO ================= */
        'purchase_price' => $this->purchase_price, 
        'rental_price' => $this->rental_price,     
        ];
    }
}
