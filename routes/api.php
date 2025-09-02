<?php

use App\Http\Controllers\AccoutController;
use App\Http\Controllers\ApiHomeController;
use App\Http\Controllers\BeachesController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CommentAdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\ImageBannerController;
use App\Http\Controllers\LoginAccoutController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\UpdateAccoutController;
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
Route::apiResource('account', AccoutController::class)->except(['store']);
Route::post('account/change-otp', [AccoutController::class, 'store'])
    ->name('account.change-otp');
Route::post('account/verify-account', [AccoutController::class, 'verifyAccount'])
    ->name('account.verify-account');
Route::prefix('admin')->middleware(['auth:sanctum', 'user.type:admin'])->group(function () {
    Route::apiResource('region', RegionController::class);
    Route::apiResource('beaches', BeachesController::class);
    Route::apiResource('content', ContentController::class);
    Route::apiResource('image-banner', ImageBannerController::class);
    Route::get('account-list', [AccoutController::class, 'index']);
    Route::post('account-list', [AccoutController::class, 'permissionsAccout']);
    Route::get('comment', [CommentAdminController::class, 'list']);
    Route::post('block-comment', [CommentAdminController::class, 'blockComment']);
    Route::post('account-list', [AccoutController::class, 'permissionsAccout']);
});

Route::prefix('customer')->group(function () {
    Route::post('/sent-otp', [ChangePasswordController::class, 'sentOtp']);
    Route::post('/change-otp', [ChangePasswordController::class, 'changeOtp']);
    Route::post('/change-password', [ChangePasswordController::class, 'changePass']);
    Route::post('/update-account', [UpdateAccoutController::class, 'index']);
    Route::get('favorites', [FavoritesController::class, 'index']);
    Route::post('favorites', [FavoritesController::class, 'store']);
    Route::delete('favorites', [FavoritesController::class, 'delete']);
    Route::get('check-favorites', [FavoritesController::class, 'checkfavorites']);

    Route::post('comment', [CommentController::class, 'store']);
    Route::put('comment/{id}', [CommentController::class, 'update']);
    Route::delete('comment/{id}', [CommentController::class, 'delete']);
});
Route::get('list-beaches', [ApiHomeController::class, 'listBeaches']);
Route::get('list-regions', [ApiHomeController::class, 'region']);
Route::get('list-banner', [ApiHomeController::class, 'listImageBanner']);

Route::get('beaches', [ApiHomeController::class, 'show']);
Route::get('count-favorite', [ApiHomeController::class, 'countFavorite']);
Route::get('list-comment', [ApiHomeController::class, 'listComment']);
