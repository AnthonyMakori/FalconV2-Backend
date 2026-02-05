<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WatchProgress;

class WatchProgressController extends Controller
{
    public function continueWatching()
    {
        $user = auth()->user();

        return WatchProgress::where('user_id', $user->id)
            ->whereColumn('progress_seconds', '<', 'duration_seconds')
            ->with('movie:id,title,thumbnail')
            ->orderByDesc('last_watched_at')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->movie->id,
                'title' => $item->movie->title,
                'thumbnail' => $item->movie->thumbnail,
                'progress' => $item->duration_seconds > 0
                    ? round(($item->progress_seconds / $item->duration_seconds) * 100)
                    : 0,
                'lastWatched' => $item->last_watched_at?->diffForHumans(),
            ]);
    }

    public function history()
    {
        $user = auth()->user();

        return WatchProgress::where('user_id', $user->id)
            ->with('movie:id,title,thumbnail')
            ->orderByDesc('last_watched_at')
            ->get();
    }
}


