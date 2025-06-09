<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\LiveStreamingController;
use App\Http\Controllers\Admin\StreamingController;
use App\Http\Controllers\Admin\PaymentSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Include public API routes (includes auth routes with rate limiting)
    require __DIR__.'/api_v1_public.php';
    
    // User Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/user', [AuthController::class, 'user']);
    });
    
    // Admin Protected Routes
    Route::middleware(['auth:sanctum', 'admin.permission'])->group(function () {
        Route::post('auth/admin/logout', [\App\Http\Controllers\Api\Admin\AuthController::class, 'logout']);
        Route::post('auth/admin/refresh', [\App\Http\Controllers\Api\Admin\AuthController::class, 'refresh']);
        Route::get('auth/admin/profile', [\App\Http\Controllers\Api\Admin\AuthController::class, 'profile']);
        
        // Admin Routes
        Route::prefix('admin')->group(function () {
            // Dashboard
            Route::get('dashboard', [DashboardController::class, 'index']);
            Route::get('dashboard/stats', [DashboardController::class, 'stats']);
            
            // Admin Management (Super Admin Only)
            Route::middleware('admin.permission:manage_admins')->group(function () {
                Route::get('admins', [AdminController::class, 'index']);
                Route::post('admins', [AdminController::class, 'store']);
                Route::put('admins/{admin}', [AdminController::class, 'update']);
                Route::delete('admins/{admin}', [AdminController::class, 'destroy']);
                Route::patch('admins/{admin}/toggle-status', [AdminController::class, 'toggleStatus']);
                Route::get('permissions', [AdminController::class, 'permissions']);
            });
            
            // Products
            Route::middleware('admin.permission:manage_products')->group(function () {
                Route::apiResource('products', ProductController::class);
                Route::get('products/categories', [ProductController::class, 'categories']);
                Route::post('products/categories', [ProductController::class, 'storeCategory']);
            });
            
            // Orders
            Route::middleware('admin.permission:manage_orders')->group(function () {
                Route::apiResource('orders', OrderController::class);
                Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
                Route::get('orders/{order}/items', [OrderController::class, 'items']);
            });
            
            // Live Streaming
            Route::middleware('admin.permission:manage_streaming')->prefix('streaming')->group(function () {
                Route::get('/', [StreamingController::class, 'index']);
                Route::post('start', [StreamingController::class, 'start']);
                Route::post('end', [StreamingController::class, 'end']);
                Route::get('stats', [StreamingController::class, 'stats']);
                Route::get('active', [StreamingController::class, 'active']);
                Route::post('message', [StreamingController::class, 'sendMessage']);
            });
            
            // WhatsApp
            Route::middleware('admin.permission:manage_whatsapp')->prefix('whatsapp')->group(function () {
                Route::get('/', [WhatsAppController::class, 'index']);
                Route::get('stats', [WhatsAppController::class, 'stats']);
                Route::post('send', [WhatsAppController::class, 'send']);
                Route::get('messages', [WhatsAppController::class, 'messages']);
                Route::post('broadcast', [WhatsAppController::class, 'broadcast']);
            });

            // Settings
            Route::middleware('admin.permission:manage_settings')->prefix('settings')->group(function () {
                Route::get('payment', [PaymentSettingsController::class, 'index']);
                Route::post('payment', [PaymentSettingsController::class, 'store']);
            });
        });
    });
});

// Fallback for undefined routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
});
