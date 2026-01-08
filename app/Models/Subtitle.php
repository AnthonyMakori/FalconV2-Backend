<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    use HasFactory;

    protected $fillable = ['file_path', 'movie_id'];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
