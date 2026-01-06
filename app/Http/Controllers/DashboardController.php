<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Actor;
use App\Models\Director;

class DashboardController extends Controller
{
    public function getStats()
    {
        return response()->json([
            'totalUsers' => User::count(),
            'totalSubscribers' => User::where('is_subscriber', true)->count(),
            'totalReviews' => \DB::table('reviews')->count(),
            'totalCastAndCrew' => Actor::count() + Director::count(),
            'totalSeries' => Series::count(),
            'totalMovies' => Movie::count(),
        ]);
    }

    public function getRevenue()
    {
        return response()->json([
            ['month' => 'Jan', 'revenue' => 4000],
            ['month' => 'Feb', 'revenue' => 3000],
            ['month' => 'Mar', 'revenue' => 5000],
            ['month' => 'Apr', 'revenue' => 4000],
            ['month' => 'May', 'revenue' => 6000],
            ['month' => 'Jun', 'revenue' => 7000],
        ]);
    }
}

