<?php

// APIのURLルーティング定義ファイル

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WeatherController;

// ユーザー登録(POST /api/register)
Route::post('/register', [AuthController::class, 'register']);
// ログイン(POST /api/login)
Route::post('/login', [AuthController::class, 'login']);


// 認証必要ルート(ログインしてないとアクセス不可)
Route::middleware('auth:sanctum')->group(function () {

    // ログイン中ユーザー情報取得(GET /api/user)
    Route::get('/user', [AuthController::class, 'user']);
    // ログアウト(POST /api/logout)
    Route::post('/logout', [AuthController::class, 'logout']);
});

// 天気・営業予報(GET / api/weather)
Route::get('/weather', [WeatherController::class, 'index']);
