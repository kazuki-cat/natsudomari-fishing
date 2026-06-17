<?php

namespace Tests\Unit;

use App\Services\WeatherService;
use PHPUnit\Framework\TestCase;

// WeatherServiceの純粋ロジック(外部依存なし)のUnitテスト
class WeatherServiceTest extends TestCase
{
    // テスト対象のWeatherServiceインスタンスを保持する
    private WeatherService $service;

    // 各テストメソッドの実行前に毎回呼ばれる準備処理
    // ここでWeatherServiceを生成し、各テストで使い回す
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WeatherService;
    }

    // 風テキストから風速(m/s)を推定する
    public function test_estimate_wind_speed(): void
    {
        $this->assertSame(15.0, $this->service->estimateWindSpeed('南の風 暴風'));
        $this->assertSame(8.0, $this->service->estimateWindSpeed('北の風 強く'));
        // 「やや強く」は「強く」を含むが、5.0と正しく判定されること(バグ修正の検証)
        $this->assertSame(5.0, $this->service->estimateWindSpeed('東の風 やや強く'));
        $this->assertSame(1.5, $this->service->estimateWindSpeed('南の風 弱く'));
        $this->assertSame(3.0, $this->service->estimateWindSpeed('南の風')); // 記載なし = デフォルト
    }

    // 波高テキストから最大の数値(m)を抽出する(全角 → 半角変換含む)
    public function test_parse_wave_height(): void
    {
        $this->assertSame(1.5, $this->service->parseWaveHeight('１メートル　後　1.５メートル'));
        $this->assertSame(0.5, $this->service->parseWaveHeight('0.５メートル'));
        $this->assertNull($this->service->parseWaveHeight('')); // 空はnull
    }

    // 風テキストから風向きを抽出する(長い方位を優先)
    public function test_extract_wind_direction(): void
    {
        $this->assertSame('北北東', $this->service->extractWindDirection('北北東の風'));
        $this->assertSame('南', $this->service->extractWindDirection('南の風 やや強く'));
        $this->assertSame('不明', $this->service->extractWindDirection('風のみ')); // 方位なし
    }

    // 天気テキストを簡略化する(悪い天気を優先: 雪>雨>曇>晴)
    public function test_simplify_weather(): void
    {
        $this->assertSame('雪', $this->service->simplifyWeather('晴れ後一時雨か雪'));
        $this->assertSame('雨', $this->service->simplifyWeather('晴れ時々雨'));
        $this->assertSame('曇り', $this->service->simplifyWeather('曇り'));
        $this->assertSame('晴れ', $this->service->simplifyWeather('晴'));
    }
}
