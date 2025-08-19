<?php

use App\Http\Controllers\AccoutController;
use App\Http\Controllers\BeachesController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\LoginAccoutController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/login', [LoginController::class, 'login']);
Route::post('/login-account', [LoginAccoutController::class, 'login']);
Route::apiResource('account', AccoutController::class);
Route::prefix('admin')->group(function () {
    Route::apiResource('region', RegionController::class);
    Route::apiResource('beaches', BeachesController::class);
    Route::apiResource('content', ContentController::class);
});

Route::prefix('customer')->group(function () {
    Route::post('/sent-otp' , [ChangePasswordController::class , 'sentOtp']);
    Route::post('/change-pass', [ChangePasswordController::class, 'changePass']);
});
