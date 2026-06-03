<?php

namespace Tests\Feature;

use App\Models\CatchReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// 釣果投稿API(一覧・詳細・投稿・削除)のFeatureテスト
class CatchReportTest extends TestCase
{
    // 各テストごとにDBをマイグレーションし直してまっさらな状態にする
    use RefreshDatabase;

    // 誰でも釣果一覧を閲覧できる
    public function test_anyone_can_view_report_list(): void
    {
        CatchReport::factory()->count(3)->create();

        $response = $this->getJson('/api/reports');

        // ページネーションのdataに3件分入っている
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    // ページネーションのテスト
    public function test_report_list_is_paginated(): void
    {
        CatchReport::factory()->count(15)->create();

        $response = $this->getJson('/api/reports');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('last_page', 2);
    }

    // 魚種でフィルターできる
    public function test_report_list_can_filter_by_fish_name(): void
    {
        CatchReport::factory()->create(['fish_name' => 'アジ']);
        CatchReport::factory()->create(['fish_name' => 'メバル']);

        $response = $this->getJson('/api/reports?fish_name=アジ');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fish_name', 'アジ');
    }

    // 古い順でソートできる
    public function test_report_list_can_sort_by_oldest(): void
    {
        CatchReport::factory()->create(['caught_at' => '2020-01-01']);
        CatchReport::factory()->create(['caught_at' => '2024-01-01']);

        $this->getJson('/api/reports?sort=oldest')
            ->assertJsonPath('data.0.caught_at', '2020-01-01');
    }

    // 誰でも釣果詳細を閲覧できる
    public function test_anyone_can_view_report_detail(): void
    {
        $report = CatchReport::factory()->create();

        $response = $this->getJson("/api/reports/{$report->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $report->id);
    }

    // ログインユーザーは画像付きで釣果投稿できる
    public function test_authenticated_user_can_create_report(): void
    {
        // 実ファイルを書き込まずに保存をテストするための偽ストレージ
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user); // このユーザーでログイン状態にする

        // 画像送信はMultipartのためpostJson()ではなくpost()を使う
        // 第3引数のAccept: application/jsonで失敗時も422をJSONで受け取る
        $response = $this->post('/api/reports', [
            'caught_at' => now()->format('Y-m-d'),
            'fish_name' => 'アジ',
            'tackle' => 'サビキ釣り',
            'location_name' => '夏泊半島・大島',
            'latitude' => 41.002,
            'longitude' => 140.88361,
            'image' => UploadedFile::fake()->create('catch.jpg', 100, 'image/jpeg'),
        ], ['Accept' => 'application/json']);

        $response->assertStatus(201)
            ->assertJsonPath('data.fish_name', 'アジ');

        $this->assertDatabaseHas('catch_reports', [
            'fish_name' => 'アジ',
            'user_id' => $user->id,
        ]);

        // 画像が偽ストレージに実際に保存されている
        $imagePath = $response->json('data.image_path');
        $this->assertNotNull($imagePath);
        // ※assertExistsの[Undefined method]警告はintelephenseの誤検知(実際は正常に動く)
        Storage::disk('public')->assertExists($imagePath);
    }

    // 未ログインでは投稿できない
    public function test_guest_cannot_create_report(): void
    {
        $response = $this->postJson('/api/reports', [
            'caught_at' => now()->format('Y-m-d'),
            'fish_name' => 'アジ',
            'tackle' => 'サビキ釣り',
            'location_name' => '夏泊半島・大島',
        ]);

        $response->assertStatus(401);
    }

    // 未来の日付は投稿できない
    public function test_create_report_rejects_future_date(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reports', [
            'caught_at' => now()->addDay()->format('Y-m-d'), // 明日
            'fish_name' => 'アジ',
            'tackle' => 'サビキ釣り',
            'location_name' => '夏泊半島・大島',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['caught_at']);
    }

    // 投稿者本人は自分の釣果を削除できる
    public function test_owner_can_delete_own_report(): void
    {
        $user = User::factory()->create();
        $report = CatchReport::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/reports/{$report->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('catch_reports', ['id' => $report->id]);
    }

    // 他人の釣果は削除できない
    public function test_user_cannot_delete_others_report(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $report = CatchReport::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($other);

        $response = $this->deleteJson("/api/reports/{$report->id}");

        $response->assertStatus(403);
        // 削除されずに残っている
        $this->assertDatabaseHas('catch_reports', ['id' => $report->id]);
    }
}
