<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Main auth routes
Route::post('user/register', [UserController::class, 'register'])->name('register');
Route::post('user/login', [UserController::class, 'login'])->name('login');

// For Logged in users
Route::group(["middleware" => ['auth:sanctum']], function() {

    Route::post('user/logout', [UserController::class, 'logout']);

    // cart manipulations
    Route::post('addProductInCart', [ApiController::class, 'addProductInCart']);
    Route::post('removeProductFromCart', [ApiController::class, 'removeProductFromCart']);
    Route::post('setCartProductQuantity', [ApiController::class, 'setCartProductQuantity']);

    // gets cart with discounts applied
    Route::get('getUserCart', [ApiController::class, 'getUserCart']);
    
});