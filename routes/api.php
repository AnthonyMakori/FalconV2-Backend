<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\API\MerchandiseController;
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

    Route::post('/movies/verify-code', [MovieAccessController::class, 'verify']);
    Route::get('/movies/{movie}/stream', [SecureStreamController::class, 'stream']);


});
  Route::apiResource('events', EventController::class);
  Route::apiResource('merchandise', MerchandiseController::class);
  Route::middleware('throttle:10,1')->post('/subscribe', [SubscriberController::class, 'store']);

  Route::middleware('auth:api')->get('/me', [UserProfileController::class, 'me']);



 Route::middleware('auth:api')->get('/me', function (Request $request) {
    return $request->user();
});



