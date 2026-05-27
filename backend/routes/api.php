<?php

// APIのURLルーティング定義ファイル

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\CatchReportController;

// 認証不要ルート //
// ユーザー登録(POST /api/register)
Route::post('/register', [AuthController::class, 'register']);

// ログイン(POST /api/login)
Route::post('/login', [AuthController::class, 'login']);

// 天気・営業予報(GET / api/weather)
Route::get('/weather', [WeatherController::class, 'index']);

// 釣果一覧・タイムライン(GET /api/reports?page=N)
Route::get('/reports', [CatchReportController::class, 'index']);

// 釣り場マップ用(座標付き釣果を全件返す)(GET /api/reports/map)
// ※ /reports/{id}より前に書かないと"map"がIDとして解釈されるので注意
Route::get('/reports/map', [CatchReportController::class, 'mapIndex']);

// 釣果詳細(GET /api/reports/N)
Route::get('/reports/{catchReport}', [CatchReportController::class, 'show']);


// 認証必要ルート(ログインしてないとアクセス不可) //
Route::middleware('auth:sanctum')->group(function () {

    // ログイン中ユーザー情報取得(GET /api/user)
    Route::get('/user', [AuthController::class, 'user']);

    // ログアウト(POST /api/logout)
    Route::post('/logout', [AuthController::class, 'logout']);

    // 釣果投稿(POST /api/reports)multipart/form-dataで画像も送れる
    Route::post('/reports', [CatchReportController::class, 'store']);

    // 釣果削除(DELETE /api/reports/1)自分の投稿のみ削除可
    Route::delete('/reports/{catchReport}', [CatchReportController::class, 'destroy']);
});
