<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductCrudController;
use App\Http\Controllers\Admin\CategoryCrudController;
use App\Http\Controllers\Admin\UserCrudController;

// Ensure Backpack routes are correctly prefixed and protected
Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    Route::crud('user', UserCrudController::class);
    Route::crud('product', ProductCrudController::class);
    Route::crud('category', CategoryCrudController::class);
});
