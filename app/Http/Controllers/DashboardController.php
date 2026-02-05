<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Movie;
use App\Models\Event;
use App\Models\Merchandise;
use App\Models\Subscriber;
use App\Models\Purchase;
use App\Models\EventPayment;
use App\Models\MerchandisePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
  public function getStats()
        {
            // Total Revenue: sum of successful purchases (Movies)
            $totalRevenue = Purchase::where('status', 'success')->sum('amount');
        
            // Total Revenue (Merchandise)
            $totalMerchRevenue = MerchandisePayment::where('status', 'success')->sum('amount');
        
            // Total Revenue (Events)
            $totalEventRevenue = EventPayment::where('status', 'success')->sum('amount');
        
            // Active Subscribers
            $activeSubscribers = Subscriber::count();
        
            // Total Views: total purchases (regardless of status)
            $totalViews = Purchase::count();
        
            // New Users (last 30 days)
            $newUsers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        
            return response()->json([
                'totalRevenue' => $totalRevenue,
                'totalMerchRevenue' => $totalMerchRevenue,
                'totalEventRevenue' => $totalEventRevenue,
                'activeSubscribers' => $activeSubscribers,
                'totalViews' => $totalViews,
                'newUsers' => $newUsers,
            ]);
        }

   public function getRecentUploads()
        {
            // Latest 5 movies
            $movies = Movie::latest()->take(5)->get(['id', 'title', 'created_at', 'thumbnail']);
        
            // Latest 5 events (using correct columns and aliases)
            $events = Event::latest()->take(5)->get([
                'id',
                'name as title',       
                'created_at',
                'poster as thumbnail',
            ]);
        
            // Latest 5 merchandise (assuming columns are name & image, alias if needed)
            $merchandises = Merchandise::latest()->take(5)->get([
                'id',
                'name as title',
                'created_at',
                'image as thumbnail',
            ]);
        
            return response()->json([
                'movies' => $movies,
                'events' => $events,
                'merchandises' => $merchandises,
            ]);
        }


    public function getRecentActivities()
    {
        // Recent user registrations (last 5)
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'created_at']);

        // Recent purchases (last 5)
        $recentPurchases = Purchase::latest()->take(5)->get(['id', 'user_id', 'amount', 'status', 'created_at']);

        return response()->json([
            'recentUsers' => $recentUsers,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    public function getViewsOverview()
    {
        // Count purchases per month for the last 12 months
        $data = Purchase::select(
            DB::raw("DATE_FORMAT(created_at, '%b') as month"),
            DB::raw("COUNT(*) as views")
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(12))
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b')"))
        ->orderBy(DB::raw("MIN(created_at)"))
        ->get();

        return response()->json($data);
    }

    public function getRevenueOverview()
    {
        // Sum of successful purchases per month for the last 12 months
        $data = Purchase::select(
            DB::raw("DATE_FORMAT(created_at, '%b') as month"),
            DB::raw("SUM(amount) as revenue")
        )
        ->where('status', 'success')
        ->where('created_at', '>=', Carbon::now()->subMonths(12))
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b')"))
        ->orderBy(DB::raw("MIN(created_at)"))
        ->get();

        return response()->json($data);
    }
}
