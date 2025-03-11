<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductCrudController;
use App\Http\Controllers\Admin\CategoryCrudController;
use App\Http\Controllers\Admin\UserCrudController;

Route::crud('user', UserCrudController::class);
Route::crud('product', ProductCrudController::class);
Route::crud('category', CategoryCrudController::class);
