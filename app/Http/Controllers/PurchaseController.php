<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    /**
     * Fetch successful purchases for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $purchases = Purchase::where('email', $user->email)
            ->where('status', 'success')
            ->latest()
            ->get([
                'id',
                'movie_id',
                'amount',
                'status',
                'created_at'
            ]);

        return response()->json($purchases);
    }

    /**
     * Get total amount spent by user
     */
    public function summary(Request $request)
    {
        $user = $request->user();

        $total = Purchase::where('email', $user->email)
            ->where('status', 'success')
            ->sum('amount');

        return response()->json([
            'total_spent' => $total
        ]);
    }
}
