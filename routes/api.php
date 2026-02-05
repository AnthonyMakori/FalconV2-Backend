<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\MerchandiseController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\ActorController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\EventPaymentController;
use App\Http\Controllers\MerchandiseMpesaController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\PaymentPlanController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\MovieAccessController;
use App\Http\Controllers\SecureStreamController;
use App\Http\Controllers\WatchProgressController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\GenreController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/admin/register', [AdminAuthController::class, 'register']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);

    Route::get('/movies', [MovieController::class, 'index']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//payment routes
Route::prefix('c2b')->group(function () {
    Route::get('register', [MpesaController::class, 'registerC2bUrls']);
    Route::get('access-token', [MpesaController::class, 'generateAccessToken']);
    Route::post('validation', [MpesaController::class, 'c2bValidation']);
    Route::post('confirmation', [MpesaController::class, 'c2bConfirmation']);
});

Route::prefix('stk')->group(function () {
    Route::post('initiate', [MpesaController::class, 'initiate']);
    Route::post('callback', [MpesaController::class, 'callback']);
});

Route::prefix('banks')->group(function () {
    Route::get('jenga/test', [MpesaController::class, 'testJenga']);
    Route::get('jenga/signature', [MpesaController::class, 'generateSignature']);
    Route::get('jenga/account-balance', [MpesaController::class, 'jengaBalance']);
    Route::post('jenga/access-token', [MpesaController::class, 'generateJengaToken']);
    Route::get('jenga/equity', [MpesaController::class, 'initiateEquity']);

    // Route::post('callback', [PaymentsController::class, 'callback']);
});

Route::prefix('events')->group(function(){
    Route::post('/stk/initiate', [EventPaymentController::class, 'initiate']);
    Route::post('/stk/callback', [EventPaymentController::class, 'callback']);
    });

Route::post('/initiate/merchandise', [MerchandiseMpesaController::class, 'initiate']);
Route::post('/stk/merchandise-callback', [MerchandiseMpesaController::class, 'merchandiseCallback']);

Route::middleware('auth:api')->group(function () {

    // Users Management
    Route::get('/users', [UserController::class, 'index']);          
    Route::post('/users', [UserController::class, 'store']);         
    Route::get('/users/{user}', [UserController::class, 'show']);    
    Route::put('/users/{user}', [UserController::class, 'update']);  
    Route::delete('/users/{user}', [UserController::class, 'destroy']); 

    // User status
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

    // Staff Management
    Route::prefix('staff')->group(function () {
    Route::get('/', [StaffController::class, 'index']); 
    Route::get('/{id}', [StaffController::class, 'show']); 
    Route::post('/', [StaffController::class, 'store']); 
    Route::put('/{id}', [StaffController::class, 'update']); 
    Route::delete('/{id}', [StaffController::class, 'destroy']); 
});
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class)->only(['index', 'store']);
    Route::post('roles/{role}/permissions', [PermissionController::class, 'updateRolePermissions']);

    Route::apiResource('blogs', BlogController::class);

    Route::get('/movies', [MovieController::class, 'index']);
    Route::post('/movies', [MovieController::class, 'store']);

    //settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);

    // routes/api.php

Route::prefix('admin')->group(function () {

    // Payment Plans
    Route::get('/payment-plans', [PaymentPlanController::class, 'index']);
    Route::post('/payment-plans', [PaymentPlanController::class, 'store']);
    Route::get('/payment-plans/{paymentPlan}', [PaymentPlanController::class, 'show']);
    Route::put('/payment-plans/{paymentPlan}', [PaymentPlanController::class, 'update']);
    Route::patch('/payment-plans/{paymentPlan}/toggle', [PaymentPlanController::class, 'toggleStatus']);
    Route::delete('/payment-plans/{paymentPlan}', [PaymentPlanController::class, 'destroy']);

    // Payment Methods
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::patch('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
    Route::patch('/payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggleStatus']);

});



    Route::post('/movies/verify-code', [MovieAccessController::class, 'verify']);
    Route::get('/movies/{movie}/stream', [SecureStreamController::class, 'stream'])->name('movies.stream');

    Route::post('/progress', [WatchProgressController::class, 'update']);
    Route::get('/progress', [WatchProgressController::class, 'list']);
    Route::get('/watch-progress/continue', [WatchProgressController::class, 'continueWatching']);
    Route::get('/watch-history', [WatchProgressController::class, 'history']);
    
    
    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{movie}', [WatchlistController::class, 'destroy']);

    Route::post('/movies/verify-code', [MovieAccessController::class, 'verify']);
    Route::get('/movies/{movie}/stream', [SecureStreamController::class, 'stream']);
    
    
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::get('/purchases/summary', [PurchaseController::class, 'summary']);
    
    Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
    
    Route::get('/me', [UserController::class, 'me']);
    
    
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/recent-uploads', [DashboardController::class, 'getRecentUploads']);
    Route::get('/dashboard/recent-activities', [DashboardController::class, 'getRecentActivities']);
    Route::get('/dashboard/views-overview', [DashboardController::class, 'getViewsOverview']);
    Route::get('/dashboard/revenue-overview', [DashboardController::class, 'getRevenueOverview']);
    
    
    Route::post('/verify-access-code', [PurchaseController::class, 'verifyAccessCode']);
    


});
  Route::apiResource('events', EventController::class);
  Route::apiResource('merchandise', MerchandiseController::class);
  Route::middleware('throttle:10,1')->post('/subscribe', [SubscriberController::class, 'store']);
  
  Route::post('/merchandise-order', [MerchandiseMpesaController::class, 'saveOrderDetails']);
  Route::get('/merchandise-payment-status/{checkoutRequestId}', [MerchandiseMpesaController::class, 'getPaymentStatus']);
  
  Route::get('/movies', [MovieController::class, 'index']);
//   Route::get('/movies/{movie}', [MovieController::class, 'show']);
  
  Route::get('/trending/movie/week', [MovieController::class, 'trending']);
//   Route::get('/movie/popular', [MovieController::class, 'popular']);
//   Route::get('/movie/{movie}', [MovieController::class, 'show']);
//   Route::get('/search/movie', [MovieController::class, 'search']);
  
Route::prefix('movie')->group(function () {

    /* ========= STATIC TMDB ROUTES ========= */
    Route::get('/popular', [MovieController::class, 'popular']);
    Route::get('/top_rated', [MovieController::class, 'topRated']);
    Route::get('/now_playing', [MovieController::class, 'nowPlaying']);
    Route::get('/upcoming', [MovieController::class, 'upcoming']);
    Route::get('/search', [MovieController::class, 'search']);

    /* ========= DYNAMIC TMDB ROUTES ========= */
    Route::get('/{id}/recommendations', [MovieController::class, 'recommendations']);
    Route::get('/{id}/credits', [MovieController::class, 'credits']);
    Route::get('/{id}', [MovieController::class, 'show']);
});

   
   Route::get('/genre/movie/list', [GenreController::class, 'movieGenres']);




  Route::middleware('auth:api')->get('/me', function (Request $request) {
    return $request->user();
});
;



//   Route::get('/test', function () {
//     return response()->json(['message' => 'API is working']);
// });



