<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MovieAccessController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'code' => 'required|string'
        ]);

        $code = MovieAccessCode::where('code', $request->code)
            ->where('movie_id', $request->movie_id)
            ->where('user_id', auth()->id())
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$code) {
            return response()->json(['message' => 'Invalid or expired code'], 403);
        }

        $code->update(['used_at' => now()]);

        return response()->json([
            'message' => 'Access granted',
            'stream_url' => route('movies.stream', $request->movie_id)
        ]);
    }
}
