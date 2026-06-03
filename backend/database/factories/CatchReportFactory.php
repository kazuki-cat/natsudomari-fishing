<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * テスト用の釣果投稿ダミーデータを生成するファクトリ
 *
 * @extends Factory<\App\Models\CatchReport>
 */
class CatchReportFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 釣り場名 + 海上座標のセット(20ヶ所)
        $spots = [
            ['name' => '夏泊半島・大島', 'lat' => 41.00607, 'lng' => 140.86670],
            ['name' => '夏泊半島・大島', 'lat' => 41.02161, 'lng' => 140.86533],
            ['name' => '夏泊半島・大島', 'lat' => 41.01980, 'lng' => 140.88352],
            ['name' => '夏泊半島・大島', 'lat' => 41.01022, 'lng' => 140.84301],
            ['name' => '夏泊半島・大島', 'lat' => 41.01203, 'lng' => 140.88249],
            ['name' => '夏泊半島・大島', 'lat' => 41.00555, 'lng' => 140.84988],
            ['name' => '小湊漁港',       'lat' => 40.93816, 'lng' => 140.98309],
            ['name' => '清水川漁港',     'lat' => 40.97134, 'lng' => 140.97450],
            ['name' => '東滝漁港',       'lat' => 40.98897, 'lng' => 140.95287],
            ['name' => '白浜漁港',       'lat' => 41.00737, 'lng' => 140.91064],
            ['name' => '稲生漁港',       'lat' => 40.98871, 'lng' => 140.86224],
            ['name' => '浦田漁港',       'lat' => 40.97420, 'lng' => 140.85400],
            ['name' => '茂浦漁港',       'lat' => 40.96201, 'lng' => 140.85331],
            ['name' => '浪打漁港',       'lat' => 40.93673, 'lng' => 140.86069],
            ['name' => '土屋漁港',       'lat' => 40.92571, 'lng' => 140.86121],
            ['name' => 'その他',         'lat' => 40.96953, 'lng' => 141.01502],
            ['name' => 'その他',         'lat' => 41.02394, 'lng' => 140.92163],
            ['name' => 'その他',         'lat' => 40.98819, 'lng' => 140.82138],
            ['name' => 'その他',         'lat' => 40.93115, 'lng' => 140.82687],
            ['name' => 'その他',         'lat' => 40.98871, 'lng' => 140.99510],
        ];

        // 魚種 + 画像ファイル名の対応表
        $fishImages = [
            'メバル' => 'mebaru', 'アイナメ' => 'ainame', 'ソイ' => 'soi',
            'ヒラメ' => 'hirame', 'カレイ' => 'karei', 'マダイ' => 'madai',
            'アジ' => 'aji', 'サバ' => 'saba', 'イワシ' => 'iwashi',
            'イナダ' => 'inada', 'ワラサ' => 'warasa', 'ブリ' => 'buri',
            'シイラ' => 'shiira', 'サワラ' => 'sawara', 'マグロ' => 'maguro',
            'シーバス' => 'shiibasu', 'ヤリイカ' => 'yariika', 'クロダイ' => 'kurodai',
            'マゴチ' => 'magochi', 'タコ' => 'tako', 'その他' => 'sonota',
        ];

        // 日本語の釣りメモ(ランダムに1つ選ぶ)
        $memos = [
            '朝マズメに連発しました😆',
            '潮が動く時間帯が良かったで!!',
            '風が強くて苦戦しましたが何とか釣れた。',
            'リリースサイズも多めでした。',
            '今シーズン初の良型でした！',
            '底付近を狙うとアタリが増えたよ。',
            '夕方にかけて活性が上がりました(*_*)',
            'アタリは多いのに乗らず悔しい一日でした😂',
            '久しぶりに釣りを満喫できましたね。',
            '次回はもう少し大物を狙いたいです💪',
            'ボトムをネチネチ攻めてなんとか獲りました。',
            '根掛かりが多く仕掛けを何個かロストしちまった。。。',
        ];

        // 魚とスポットを先に1つずつ確定する(複数フィールドで使い回すため)
        $fish = fake()->randomElement(array_keys($fishImages));
        $spot = fake()->randomElement($spots);

        return [
            // User::factory()で投稿者も自動生成(リレーション)
            'user_id' => User::factory(),
            // 今日以前のランダムな日付(6年前から今日まで範囲指定)
            'caught_at' => fake()->dateTimeBetween('-6 year', 'now')->format('Y-m-d'),
            'fish_name' => $fish,
            'tackle' => fake()->randomElement([
                'サビキ釣り', '泳がせ釣り', 'イソメ釣り', 'フカセ釣り', 'ロックフィッシュ',
                'ショアジギング', 'タイラバゲーム', 'メバリング', 'アジング', 'エギング', 'その他',
            ]),
            // スポット座標に ±約150mの揺らぎを足す(ピンの重なり防止)
            'latitude' => $spot['lat'] + fake()->randomFloat(5, -0.0015, 0.0015),
            'longitude' => $spot['lng'] + fake()->randomFloat(5, -0.0015, 0.0015),
            'location_name' => $spot['name'],
            // 50%の確率で画像あり(あるなら魚に対応した画像)
            'image_path' => fake()->boolean(50) ? 'catch_images/' . $fishImages[$fish] . '.jpg' : null,
            // 50%の確率でメモあり
            'memo' => fake()->boolean(50) ? fake()->randomElement($memos) : null,
        ];
    }
}
