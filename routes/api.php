<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('restaurants')->group(function () {
        Route::get('/', [App\Http\Controllers\RestaurantController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\RestaurantController::class, 'show']);
        
        Route::middleware('role:restaurant_owner')->group(function () {
            Route::post('/', [App\Http\Controllers\RestaurantController::class, 'store']);
            Route::get('/my/list', [App\Http\Controllers\RestaurantController::class, 'myRestaurants']);
            Route::put('/{id}', [App\Http\Controllers\RestaurantController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\RestaurantController::class, 'destroy']);
            
            Route::prefix('{restaurantId}/menu')->group(function () {
                Route::get('/categories', [App\Http\Controllers\MenuController::class, 'getCategories']);
                Route::post('/categories', [App\Http\Controllers\MenuController::class, 'storeCategory']);
                Route::put('/categories/{categoryId}', [App\Http\Controllers\MenuController::class, 'updateCategory']);
                
                Route::get('/items', [App\Http\Controllers\MenuController::class, 'getItems']);
                Route::post('/items', [App\Http\Controllers\MenuController::class, 'storeItem']);
                Route::put('/items/{itemId}', [App\Http\Controllers\MenuController::class, 'updateItem']);
                Route::delete('/items/{itemId}', [App\Http\Controllers\MenuController::class, 'destroyItem']);
                
                Route::post('/items/{itemId}/variants', [App\Http\Controllers\MenuController::class, 'storeVariant']);
                Route::post('/items/{itemId}/addons', [App\Http\Controllers\MenuController::class, 'storeAddon']);
            });
        });
        
        Route::middleware('role:admin')->group(function () {
            Route::put('/{id}/status', [App\Http\Controllers\RestaurantController::class, 'updateStatus']);
        });
    });

    Route::prefix('orders')->group(function () {
        Route::middleware('role:customer')->group(function () {
            Route::post('/', [App\Http\Controllers\OrderController::class, 'store']);
            Route::get('/', [App\Http\Controllers\OrderController::class, 'index']);
            Route::get('/{id}', [App\Http\Controllers\OrderController::class, 'show']);
            Route::put('/{id}/cancel', [App\Http\Controllers\OrderController::class, 'cancel']);
            Route::post('/{id}/review', [App\Http\Controllers\OrderController::class, 'storeReview']);
        });

        Route::middleware('role:restaurant_owner')->group(function () {
            Route::get('/restaurant/{restaurantId}', [App\Http\Controllers\OrderController::class, 'restaurantOrders']);
            Route::put('/{id}/status', [App\Http\Controllers\OrderController::class, 'updateStatus']);
        });
    });

    Route::prefix('delivery')->group(function () {
        Route::post('/register', [App\Http\Controllers\DeliveryPartnerController::class, 'register']);
        
        Route::middleware('role:delivery_partner')->group(function () {
            Route::put('/profile', [App\Http\Controllers\DeliveryPartnerController::class, 'updateProfile']);
            Route::put('/location', [App\Http\Controllers\DeliveryPartnerController::class, 'updateLocation']);
            Route::put('/availability', [App\Http\Controllers\DeliveryPartnerController::class, 'toggleAvailability']);
            Route::get('/available-orders', [App\Http\Controllers\DeliveryPartnerController::class, 'availableOrders']);
            Route::post('/accept/{orderId}', [App\Http\Controllers\DeliveryPartnerController::class, 'acceptOrder']);
            Route::post('/reject/{orderId}', [App\Http\Controllers\DeliveryPartnerController::class, 'rejectOrder']);
            Route::get('/my-deliveries', [App\Http\Controllers\DeliveryPartnerController::class, 'myDeliveries']);
            Route::get('/pending-requests', [App\Http\Controllers\DeliveryPartnerController::class, 'pendingRequests']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::put('/verify/{partnerId}', [App\Http\Controllers\DeliveryPartnerController::class, 'verifyPartner']);
        });
    });

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard']);
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users']);
        Route::put('/users/{userId}/status', [App\Http\Controllers\AdminController::class, 'updateUserStatus']);
        
        // Restaurant management
        Route::get('/restaurants', [App\Http\Controllers\AdminController::class, 'restaurants']);
        Route::put('/restaurants/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveRestaurant']);
        Route::put('/restaurants/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectRestaurant']);
        Route::put('/restaurants/{id}/toggle-status', [App\Http\Controllers\AdminController::class, 'toggleRestaurantStatus']);
        
        // Delivery partner management
        Route::get('/delivery-partners', [App\Http\Controllers\AdminController::class, 'deliveryPartners']);
        Route::put('/delivery-partners/{id}/verify', [App\Http\Controllers\AdminController::class, 'verifyDeliveryPartner']);
        Route::put('/delivery-partners/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectDeliveryPartner']);
        Route::put('/delivery-partners/{id}/toggle-status', [App\Http\Controllers\AdminController::class, 'togglePartnerStatus']);
        
        Route::get('/orders', [App\Http\Controllers\AdminController::class, 'orders']);
        Route::get('/analytics', [App\Http\Controllers\AdminController::class, 'analytics']);
        Route::get('/settings', [App\Http\Controllers\AdminController::class, 'settings']);
        Route::put('/settings', [App\Http\Controllers\AdminController::class, 'updateSettings']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index']);
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount']);
        Route::put('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    });
});
