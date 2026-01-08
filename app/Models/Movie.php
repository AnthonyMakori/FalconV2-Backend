<?php

// app/Models/Movie.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Cast;
    
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
        'poster_path',
        'trailer_path',
        'movie_path',
        'rental_price',
        'purchase_price',
        'rental_period',
        'free_preview',
        'preview_duration',
        'seo_title',
        'seo_description',
        'seo_keywords'
    ];

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
        return $this->hasMany(Subtitle::class);
    }
}
