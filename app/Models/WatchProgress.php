<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchProgress extends Model
{
    use HasFactory;

    protected $table = 'watch_progresses';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'movie_id',
        'progress_seconds',
        'duration_seconds',
        'last_watched_at',
    ];

    protected $casts = [
        'last_watched_at' => 'datetime',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
