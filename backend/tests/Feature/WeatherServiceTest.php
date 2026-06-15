<?php

namespace Tests\Feature;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// 天気サービステスト
// 週間予報の降水確率(pop)が空のとき、短期予報の降水確率で補完されるかのFeatureテスト
class WeatherServiceTest extends TestCase
{
    // 気象庁APIを「偽の固定データ」に差し替えるヘルパー
    // ※コードが実際に読む項目だけを用意した最小の模擬JSON)
    private function fakeJmaResponse(): void
    {
        $data = [
            // data[0] = 短期予報(今日・明日の詳細)
            [
                'timeSeries' => [
                    // [0] 天気・風・波
                    [
                        'timeDefines' => ['2026-06-15T00:00:00+09:00'],
                        'areas' => [[
                            'weathers' => ['晴れ'],
                            'winds' => ['南の風'],
                            'waves' => ['0.5メートル'],
                        ]],
                    ],
                    // [1] 降水確率(6時間ごと) → 6/15の4スロットで最大は40
                    [
                        'timeDefines' => [
                            '2026-06-15T00:00:00+09:00',
                            '2026-06-15T06:00:00+09:00',
                            '2026-06-15T12:00:00+09:00',
                            '2026-06-15T18:00:00+09:00',
                        ],
                        'areas' => [[
                            'pops' => ['10', '40', '20', '0'],
                        ]],
                    ],
                    // [2] 気温
                    [
                        'timeDefines' => ['2026-06-15T09:00:00+09:00'],
                        'areas' => [[
                            'temps' => ['20'],
                        ]],
                    ],
                ],
            ],
            // data[1] = 週間予報(本物は7日分だがテストでは2日分で十分)
            [
                'timeSeries' => [
                    // [0] 天気コード・降水確率
                    [
                        'timeDefines' => [
                            '2026-06-15T00:00:00+09:00',
                            '2026-06-16T00:00:00+09:00',
                        ],
                        'areas' => [[
                            'weatherCodes' => ['100', '200'],
                            // 6/15のpopは空("") = 短期予報で補完されるべき(6/16は30)
                            'pops' => ['', '30'],
                        ]],
                    ],
                    // [1] 最高・最低気温
                    [
                        'timeDefines' => [
                            '2026-06-15T00:00:00+09:00',
                            '2026-06-16T00:00:00+09:00',
                        ],
                        'areas' => [[
                            'tempsMax' => ['', '25'],
                            'tempsMin' => ['', '15'],
                        ]],
                    ],
                ],
            ],
        ];

        // 気象庁へのリクエストを、上の偽データに差し替える
        Http::fake([
            'www.jma.go.jp/*' => Http::response($data, 200),
        ]);
    }

    // 週間予報のpopが空の日は、短期予報の降水確率(その日の最大)で補完される
    public function test_empty_weekly_pop_is_filled_from_short_term(): void
    {
        // 気象庁APIを偽の固定データに差し替える
        $this->fakeJmaResponse();

        $weather = (new WeatherService)->getCurrentWeather();

        // forecast[0] = 6/15(週間popは空) → 短期の最大40で埋まるはず(修正前はnull = "-" だった)
        $this->assertNotNull($weather['forecast'][0]['pop']);
        $this->assertSame(40, $weather['forecast'][0]['pop']);
    }

    // 週間予報にpopがある日は、そのまま週間予報の値を使う（短期予報で上書きしない)
    public function test_weekly_pop_is_used_when_present(): void
    {
        // 気象庁APIを偽の固定データに差し替える
        $this->fakeJmaResponse();

        $weather = (new WeatherService)->getCurrentWeather();

        // forecast[1] = 6/16(週間pop=30) → 30のまま
        $this->assertSame(30, $weather['forecast'][1]['pop']);
    }
}
