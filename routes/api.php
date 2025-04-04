<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PayPalController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']); // Register normal user
Route::post('/login', [AuthController::class, 'login']); // Login

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/register-seller', [AuthController::class, 'registerSeller']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});

Route::middleware(['auth:sanctum', 'seller'])->group(function () {
    Route::get('/seller-dashboard', function () {
        return response()->json(['message' => 'Welcome, Seller! ']);
    });
});
Route::get('/public/products/{product_id}', [ProductController::class, 'getPublicProductDetail']);
Route::get('/public/products', [ProductController::class, 'getPublicProducts']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']); // Get all products
        Route::post('/', [ProductController::class, 'store']); // Create product (Only Sellers)
        Route::get('/{product_id}', [ProductController::class, 'show']); // Get product by ID
        Route::put('/{product_id}', [ProductController::class, 'update']); // Update product (Only Seller)
        Route::delete('/{product_id}', [ProductController::class, 'destroy']); // Delete product (Only Seller)
    });
});
// Get all categories (Public)
Route::get('/public/categories', [PublicCategoryController::class, 'index']);

// Get all products inside a category (Public)
Route::get('/public/categories/{category_id}/products', [PublicCategoryController::class, 'getProductsByCategory']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart/view', [CartController::class, 'viewCart']);
    Route::post('/cart/update', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/remove/{product_id}', [CartController::class, 'removeFromCart']);
    Route::post('/cart/place-order', [CartController::class, 'placeOrder']);
    Route::get('/cart/orders', [CartController::class, 'orderHistory']);
    Route::get('/admin/orders', [CartController::class, 'adminOrders']);
    // PayPal Routes
    Route::get('paypal/pay/{orderId}', [PayPalController::class, 'payWithPayPal']);
    Route::get('paypal/success/{orderId}', [PayPalController::class, 'paypalSuccess']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart/prepare-order', [App\Http\Controllers\Api\CartController::class, 'preparePayPalOrder']);
    Route::get('/paypal/success/{orderId}', [App\Http\Controllers\Api\PayPalController::class, 'paypalSuccess']);
    Route::get('/paypal/cancel', function () {
        return response()->json(['message' => 'Payment cancelled']);
    });
});
use App\Http\Controllers\Api\GoogleLoginController;

Route::post('auth/google', [GoogleLoginController::class, 'googleLogin']);
