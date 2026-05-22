<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

// 気象庁の無料APIから天気データを取得・成形するサービスクラス(APIキー不要・完全無料)
class WeatherService {
    // 青森県の地域コード(青森県全体の天気予報が取得できる)
    private const FORECAST_CODE = '020000';

    // 天気コードと天気明のマッピング
    // 参考ページ(https://qiita.com/nak435/items/7f3588d3f75beb5890fa)
    // 週間予報APIはテキストではなくコード(100, 201など)で天気を返すため変換が必要
    private const WEATHER_CODES = [
        // 100番台:晴れ系
        '100' => '晴',                  '101' => '晴時々曇',              '102' => '晴一時雨',
        '103' => '晴時々雨',            '104' => '晴一時雪',              '105' => '晴時々雪',
        '106' => '晴一時雨か雪',        '107' => '晴時々雨か雪',          '108' => '晴一時雨か雷雨',
        '110' => '晴後時々曇',          '111' => '晴後曇',                '112' => '晴後一時雨',
        '113' => '晴後時々雨',          '114' => '晴後雨',                '115' => '晴後一時雪',
        '116' => '晴後時々雪',          '117' => '晴後雪',                '118' => '晴後雨か雪',
        '119' => '晴後雨か雷雨',        '120' => '晴朝夕一時雨',          '121' => '晴朝の内一時雨',
        '122' => '晴夕方一時雨',        '123' => '晴山沿い雷雨',          '124' => '晴山沿い雪',
        '125' => '晴午後は雷雨',        '126' => '晴昼頃から雨',          '127' => '晴夕方から雨',
        '128' => '晴夜は雨',            '130' => '朝の内霧後晴',          '131' => '晴明け方霧',
        '132' => '晴朝夕曇',            '140' => '晴時々雨で雷を伴う',    '160' => '晴一時雪か雨',
        '170' => '晴時々雪か雨',        '181' => '晴後雪か雨',

        // 200番台:曇り系
        '200' => '曇',                  '201' => '曇時々晴',              '202' => '曇一時雨',
        '203' => '曇時々雨',            '204' => '曇一時雪',              '205' => '曇時々雪',
        '206' => '曇一時雨か雪',        '207' => '曇時々雨か雪',          '208' => '曇一時雨か雷雨',
        '209' => '霧',                  '210' => '曇後時々晴',            '211' => '曇後晴',
        '212' => '曇後一時雨',          '213' => '曇後時々雨',            '214' => '曇後雨',
        '215' => '曇後一時雪',          '216' => '曇後時々雪',            '217' => '曇後雪',
        '218' => '曇後雨か雪',          '219' => '曇後雨か雷雨',          '220' => '曇朝夕一時雨',
        '221' => '曇朝の内一時雨',      '222' => '曇夕方一時雨',          '223' => '曇日中時々晴',
        '224' => '曇昼頃から雨',        '225' => '曇夕方から雨',          '226' => '曇夜は雨',
        '228' => '曇昼頃から雪',        '229' => '曇夕方から雪',          '230' => '曇夜は雪',
        '231' => '曇海上海岸は霧か霧雨','240' => '曇時々雨で雷を伴う',    '250' => '曇時々雪で雷を伴う',
        '260' => '曇一時雪か雨',        '270' => '曇時々雪か雨',          '281' => '曇後雪か雨',

        // 300番台:雨系
        '300' => '雨',                  '301' => '雨時々晴',              '302' => '雨時々止む',
        '303' => '雨時々雪',            '304' => '雨か雪',                '306' => '大雨',
        '308' => '雨で暴風を伴う',      '309' => '雨一時雪',              '311' => '雨後晴',
        '313' => '雨後曇',              '314' => '雨後時々雪',            '315' => '雨後雪',
        '316' => '雨か雪後晴',          '317' => '雨か雪後曇',            '320' => '朝の内雨後晴',
        '321' => '朝の内雨後曇',        '322' => '雨朝晩一時雪',          '323' => '雨昼頃から晴',
        '324' => '雨夕方から晴',        '325' => '雨夜は晴',              '326' => '雨夕方から雪',
        '327' => '雨夜は雪',            '328' => '雨一時強く降る',        '329' => '雨一時みぞれ',
        '340' => '雪か雨',              '350' => '雨で雷を伴う',          '361' => '雪か雨後晴',
        '371' => '雪か雨後曇',

        // 400番台:雪系
        '400' => '雪',                  '401' => '雪時々晴',              '402' => '雪時々止む',
        '403' => '雪時々雨',            '405' => '大雪',                  '406' => '風雪強い',
        '407' => '暴風雪',              '409' => '雪一時雨',              '411' => '雪後晴',
        '413' => '雪後曇',              '414' => '雪後雨',                '420' => '朝の内雪後晴',
        '421' => '朝の内雪後曇',        '422' => '雪昼頃から雨',          '423' => '雪夕方から雨',
        '425' => '雪一時強く降る',      '426' => '雪後みぞれ',            '427' => '雪一時みぞれ',
        '450' => '雪で雷を伴う',
    ];

    // メインメソッド: 現在の天気と週間予報を取得し、整形して返す
    // API https://www.jma.go.jp/bosai/forecast/data/forecast/020000.json
    public function getCurrentWeather(): array
    {
        try {
            // 気象庁APIにGETリクエスト(タイムアウト10秒)
            $response = Http::timeout(10)
                ->get("https://www.jma.go.jp/bosai/forecast/data/forecast/" . self::FORECAST_CODE . ".json");

            // APIが失敗したらフォールバック（ダミーデータ)を返す
            if (!$response->successful()) {
                return $this->fallback();
            }

            $data = $response->json(); // JSONをPHP配列に変換

            // 短期予報(data[0]): 今日・明日の詳細な天気
            // timeSeries[0]: 天気テキスト・風テキスト
            // timeSeries[2]: 気温
            $shortTerm = $data[0]['timeSeries'] ?? [];
            $weatherSeries = $shortTerm[0] ?? [];   // 天気・風シリーズ
            $tempSeries = $shortTerm[2] ?? [];      // 気温シリーズ

            // areas[0] = 津軽エリア(平内町・夏泊半島が属するエリア)
            // areas[0]=津軽、areas[1]=下北、areas[2]=三八上北
            $weathers = $weatherSeries['areas'][0]['weathers'] ?? []; // 天気テキスト配列
            $winds = $weatherSeries['areas'][0]['winds'] ?? []; // 風テキスト配列
            $waves = $weatherSeries['areas'][0]['waves'] ?? []; // 波テキスト配列
            $shortDates = $weatherSeries['timeDefines'] ?? []; // 日時配列
            $temps = $tempSeries['areas'][0]['temps'] ?? []; // 気温配列

            // 週間予想(data[1]): 7日分の予報
            // timeSeries[0]: weatherCode・降水確率
            // timeSeries[1]: 最高/最低気温
            $weekly = $data[1]['timeSeries'] ?? [];
            $weeklyWeatherSeries = $weekly[0] ?? [];
            $weeklyTempSeries = $weekly[1] ?? [];

            $weeklyDates = $weeklyWeatherSeries['timeDefines'] ?? [];
            // areas[0] = 津軽エリア(平内町・夏泊半島が属するエリア);
            $weeklyWeatherCodes = $weeklyWeatherSeries['areas'][0]['weatherCodes'] ?? [];
            $weeklyPops = $weeklyWeatherSeries['areas'][0]['pops'] ?? []; // 降水確率
            $weeklyTempsMax = $weeklyTempSeries['areas'][0]['tempsMax'] ?? []; // 最高気温
            $weeklyTempsMin = $weeklyTempSeries['areas'][0]['tempsMin'] ?? []; // 最低気温


            // 今日の天気テキストを簡略化(「晴れ後曇一時雨) → 「雨」など ※悪い天気を優先
            $todayWeather = $this->simplifyWeather($weathers[0] ?? '');

            $todayWind = $winds[0] ?? ''; // 今日の風テキスト
            $todayWave = $waves[0] ?? ''; // 今日の波高テキスト
            $waveHeightValue = $this->parseWaveHeight($todayWave); // 波高を数値に変換(営業判定用)

            // 風テキストから風速(m/s)を推定
            $windSpeed = $this->estimateWindSpeed($todayWind);
            // 風テキストから風向きを抽出
            $windDir = $this->extractWindDirection($todayWind);

            // 今日の気温(から文字の場合は0)
            $temperature = isset($temps[0]) && $temps[0] !== '' ? (float)$temps[0] : null;

            // 短期予報の気温データ(日付ごとに最高・裁定を整理)
            // JMAの短期予報気温は[日付1_時刻A, 日付_1_時刻B, 日付2_時刻A, ...]の順
            $tempTimeDefines = $tempSeries['timeDefines'] ?? [];
            $shortTempByDate = []; // ['YYY-MM-DD' => ['max' => N, 'min' => N]]の形に整理
            foreach ($tempTimeDefines as $i => $tempDate) {
                $val = $temps[$i] ?? '';
                if ($val === '') continue;
                $dateKey = (new \DateTime($tempDate))->format('Y-m-d');
                $hour = (int)(new \DateTime($tempDate))->format('H');
                // 6時以降 → 日中気温(最高)として扱う、未満 → (最低)
                if ($hour >= 6) {
                    $shortTempByDate[$dateKey]['max'] = (int)$val;
                } else {
                    $shortTempByDate[$dateKey]['min'] = (int)$val;
                }
            }

            // 週間予報の配列を組み立て(7日分)
            $forecast = $this->buildWeeklyForecast(
                $weeklyDates, $weeklyWeatherCodes, $weeklyPops,
                $weeklyTempsMax, $weeklyTempsMin,
                $winds, $shortDates,
                $shortTempByDate,
            );

            // フロントエンドに返すデータ構造
            return [
                'temperature'        => $temperature,     // 現在の気温
                'windSpeed'          => $windSpeed,       // 現在の風速(m/s) ※推定値
                'windDirection'      => $windDir,         // 現在の風向き(例: 東)
                'weatherDescription' => $todayWeather,    // 今日の天気(例: 曇)
                'waveHeight'         => $todayWave,       // 波高テキスト(例: "1メートル　後　1.5メートル")
                'waveHeightValue'    => $waveHeightValue, // 波高数値(営業判定用)
                'windText'           => $todayWind,       // 元の風テキスト(デバッグ用)
                'forecast'           => $forecast,        // 7日分の週間予報配列
            ];
        } catch (\Exception) {
            // 例外が発生した場合もフォールバックを返す
            return $this->fallback();
        }
    }

    // 週間予報の配列を組み立てるプライベートメソッド
    private function buildWeeklyForecast(
        array $weeklyDates,     // 週間予報の日時配列(7日分)
        array $codes,           // 天気コード
        array $pops,            // 降水確率
        array $tempsMax,        // 最高気温(週間)
        array $tempsMin,        // 最低気温(週間)
        array $shortWinds,      // 短期予報の風テキスト(今日・明日の風速測定に使用)
        array $shortDates,      // 短期予報の日時
        array $shortTempByDate  // 短期予報の日付→気温マップ ['YYYY-MM-DD' => ['max'=>N, 'min'=>N]]
    ): array {
        // 曜日の配列(0=日, 1=月, ...)
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        $forecast = [];

        foreach ($weeklyDates as $i => $timeDefine) {
            $date = new \DateTime($timeDefine);
            // 表示用の日付文字列(例: 05/11（月）)
            $label = $date->format('m/d') . '（' . $days[(int)$date->format('w')] . '）';

            // 天気コードを天気名に変換(マッピングにない場合は'不明')
            $code = $codes[$i] ?? null;
            $description = $code ? (self::WEATHER_CODES[$code] ?? '不明') : '不明';

            // 今日・明日の風速は短期予報の風テキストから推定
            $windSpeed = null;
            foreach ($shortDates as $j => $shortDate) {
                // 日付部分(YYYY-MM-DD)が一致する短期予報を探す
                if (str_starts_with($shortDate, $date->format('Y-m-d'))) {
                    $windSpeed = $this->estimateWindSpeed($shortWinds[$j] ?? '');
                    break; // 同じ日付が複数あっても最初の1件だけ使う
                }
            }

            // 週間予報に気温が含まれない日(最初の1〜2日)は短期予報の値で補完する
            $dateKey = $date->format('Y-m-d');
            $maxTemp = $tempsMax[$i] !== '' ? (int)$tempsMax[$i] : ($shortTempByDate[$dateKey]['max'] ?? null);
            $minTemp = $tempsMin[$i] !== '' ? (int)$tempsMin[$i] : ($shortTempByDate[$dateKey]['min'] ?? null);

            $forecast[] = [
                'date'               => $label,         // 表示用日付(例: 05/22（金))
                'weatherDescription' => $description,   // 天気名(例： 曇、晴時々曇)
                'weatherCode'        => $code,          // 気象庁の天気コード(例: 200)
                'windSpeed'          => $windSpeed,     // 推定風速(m/s)短期予報から取得
                'pop'                => $pops[$i] !== '' ? (int)$pops[$i] : null, // 降水確率(%) 空文字はnull
                'temperatureMax'     => $maxTemp,       // 最高気温 空文字はnullで短期予報で補完
                'temperatureMin'     => $minTemp,       // 最低気温 空文字はnullで短期予報で補完
            ];
        }
        return $forecast;
    }

    // 波高テキストから数値(m)を抽出するプライベートメソッド
    // 全角数字を半角に変換して最大値を返す(「1メートル　後　1.5メートル」→ 1.5)
    private function parseWaveHeight(string $waveText): ?float
    {
        if ($waveText === '') return null; // 波高テキストがない場合はnull
        $text = mb_convert_kana($waveText, 'n'); //全角数字→半角に変換
        $text = str_replace('．', '.', $text); // 全角小数点→半角(mb_convert_kanaでは変換されないため)
        preg_match_all('/\d+\.?\d*/', $text, $matches);
        if (empty($matches[0])) return null;
        return (float)max($matches[0]);
    }

    // 風テキストから風速(m/s)を推定するプライベートメソッド
    // 気象庁の風テキストには「やや強く」「強く」などの表現が含まれる
    private function estimateWindSpeed(string $windText): float
    {
        if (str_contains($windText, '暴風') || str_contains($windText, '非常に強く')) return 15.0;
        if (str_contains($windText, '強く')) return 8.0;
        if (str_contains($windText, 'やや強く')) return 5.0;
        if (str_contains($windText, '穏やか') || str_contains($windText, '弱く')) return 1.5;
        return 3.0; // デフォルト(記載なし＝普通の風)
    }

    // 風テキストから風向きを抽出するプライベートメソッド
    // 「南の風やや強く」 → 「南」
    private function extractWindDirection(string $windText): string
    {
        // 16方位を長いものから順にチェック(「北北東」が「北」より先にマッチするように)
        foreach (['北北東', '北北西', '南南東', '南南西', '北東', '北西', '南東', '南西', '北', '南', '東', '西'] as $dir) {
            if (str_contains($windText, $dir)) return $dir;
        }
        return '不明';
    }

    // 天気テキストを簡略化するプライベートメソッド
    // 「晴れ後一時雨か雪」 → 「雪」(悪い天気状態を優先)
    private function simplifyWeather(string $weather): string
    {
        // 全角スペースや半角スペースを除去
        $weather = preg_replace('/[\x{3000}\s]+/u', '', $weather);
        // 優先度: 雪 > 雨 > 曇り > 晴れ(悪い天気を優先表示)
        if (str_contains($weather, '雪')) return '雪';
        if (str_contains($weather, '雨')) return '雨';
        if (str_contains($weather, '曇')) return '曇り';
        if (str_contains($weather, '晴')) return '晴れ';
        return $weather ?: '不明';
    }

    // API取得失敗時にをフォールバックデータ返すプライベートメソッド
    private function fallback(): array
    {
        return [
            'temperature'        => null,
            'windSpeed'          => null,
            'windDirection'      => '不明',
            'weatherDescription' => '取得失敗',
            'waveHeight'         => '',
            'waveHeightValue'    => null,
            'windText'           => '',
            'forecast'           => [],
        ];
    }
}
