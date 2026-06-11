<?php

namespace Database\Seeders;

use App\Models\CatchReport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // デモ画像をデフォルトディスク(.envのFILESYSTEM_DISK)のcatch_imagesへコピー
        // 開発=public(ローカル)、本番=s3。Storage経由なのでS3でもアップされる
        $sourceDir = database_path('seeders/images'); // コピー元(リポジトリ内)
        foreach (File::files($sourceDir) as $image) {
            Storage::putFileAs('catch_images', $image->getPathname(), $image->getFilename());
        }

        // デモのユーザーと釣果を投入
        // デモ用の日本語ユーザー名(リアルに見せるため固定で用意)
        $names = ['田中 太郎', '佐藤 健', '鈴木 一郎', '高橋 真央', '渡辺 翔太'];
        foreach ($names as $name) {
            // 名前だけ指定、email等はUserFactoryが自動生成
            $user = User::factory()->create(['name' => $name]);
            // 各ユーザーに釣果を6件ずつ(計30件Factory)で生成
            CatchReport::factory()
                ->count(6)
                ->create(['user_id' => $user->id]);
        }
    }
}
