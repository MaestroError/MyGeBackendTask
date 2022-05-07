<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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


Route::post('user/register', [UserController::class, 'register'])->name('register');
Route::post('user/login', [UserController::class, 'login'])->name('login');

// For Logged in users
Route::group(["middleware" => ['auth:sanctum']], function() {
    Route::post('user/logout', [UserController::class, 'logout']);
});