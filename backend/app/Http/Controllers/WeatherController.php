<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;

// 天気・営業予報APIのコントローラー
// 実際のデータ取得ロジックはWeatherServiceに委託
class WeatherController extends Controller
{
    // コンストラクタインジェクション
    // Laravelが自動的にWeatherServiceのインスタンスを注入(DIコンテナ)
    public function __construct(private WeatherService $weatherService) {}

    // 天気・営業予報取得 GET /api/weather
    public function index(): JsonResponse {
        // WeatherServiceに処理を委託して結果をJSONで返す
        return response()->json($this->weatherService->getCurrentWeather());
    }
}
