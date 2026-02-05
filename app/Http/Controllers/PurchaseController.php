<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Movie;


class PurchaseController extends Controller
{
    /**
     * Fetch successful purchases for authenticated user
     */
    public function index(Request $request)
{
    $user = $request->user();

    return $user->purchases()
        ->where('status', 'success')
        ->with('movie:id,title,thumbnail') 
        ->latest()
        ->get()
        ->map(function ($purchase) {
            return [
                'id'         => $purchase->id,
                'movie_id'   => $purchase->movie_id,
                'title'      => $purchase->movie?->title,      
                'thumbnail'  => $purchase->movie?->thumbnail,  
                'amount'     => $purchase->amount,
                'status'     => $purchase->status,
                'created_at' => $purchase->created_at,
                'type'       => 'Movie',
            ];
        });
}


    /**
     * Get total amount spent by user (current month)
     */
    public function summary(Request $request)
    {
        $user = $request->user();

        $total = $user->purchases()
            ->where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return response()->json([
            'total_spent' => $total,
        ]);
    }

    /**
     * Verify access code before allowing movie playback
     */
  public function verifyAccessCode(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string',
            'movie_id'    => 'required|integer|exists:movies,id',
        ]);
    
        $user = $request->user();
    
        $purchase = Purchase::where('access_code', $request->access_code)
            ->where('movie_id', $request->movie_id)
            ->where('user_id', $user->id)
            ->where('email', $user->email)
            ->where('status', 'success')
            ->first();
    
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid access code or this purchase does not belong to your account.'
            ], 403);
        }
    
        // ðŸ”¥ Fetch movie video path
        $movie = Movie::find($purchase->movie_id);
    
        return response()->json([
            'success' => true,
            'message' => 'Access granted',
            'video_url' => $movie->movie_path, // ðŸŽ¬ THIS IS KEY
        ]);
    }
}
