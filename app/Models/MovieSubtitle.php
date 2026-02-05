<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieSubtitle extends Model
{
    protected $fillable = [
        'movie_id',
        'language',
        'file_path',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
