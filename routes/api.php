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

    // Route::apiResource('events', EventController::class);

    Route::apiResource('merchandise', MerchandiseController::class);


});
  Route::apiResource('events', EventController::class);
  Route::apiResource('merchandise', MerchandiseController::class);



