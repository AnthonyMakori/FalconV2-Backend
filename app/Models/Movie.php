<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
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

        // RELATIVE PATHS (movies/posters/...)
        'poster_path',
        'trailer_path',
        'movie_path',
        'bunny_video_id',

        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'free_preview' => 'boolean',
        'rental_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    /**
     * Keep these appended so nothing breaks
     */
    protected $appends = [
        'poster_path_full',
        'backdrop_path',
        'overview',
        'release_date',
    ];

    /* ================= TMDB ACCESSORS ================= */

    /**
     * Full poster URL (assets-based, NOT storage)
     * Example:
     * https://api.falconeyephilmz.com/assets/movies/posters/file.png
     */
    public function getPosterPathFullAttribute()
    {
        if (!$this->poster_path) {
            return null;
        }

        return url('/assets/' . ltrim($this->poster_path, '/'));
    }

    /**
     * TMDB-compatible backdrop
     * (reusing poster for now)
     */
    public function getBackdropPathAttribute()
    {
        return $this->poster_path_full;
    }

    /**
     * TMDB-compatible overview
     */
    public function getOverviewAttribute()
    {
        return $this->description;
    }

    /**
     * TMDB-compatible release date
     */
    public function getReleaseDateAttribute()
    {
        return $this->release_year
            ? $this->release_year . '-01-01'
            : null;
    }

    /* ================= RELATIONS ================= */

    public function casts()
    {
        return $this->hasMany(Cast::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function subtitles()
    {
        return $this->hasMany(MovieSubtitle::class);
    }
}
