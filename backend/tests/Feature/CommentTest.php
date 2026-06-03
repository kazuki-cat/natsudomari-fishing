<?php

namespace Tests\Feature;

use App\Models\CatchReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// コメントAPI(一覧・投稿・バリデーション)のFeatureテスト
class CommentTest extends TestCase
{
    // 各テストごとにDBをマイグレーションし直してまっさらな状態にする
    use RefreshDatabase;

    // 誰でもコメント一覧を閲覧できる
    public function test_anyone_can_view_comments(): void
    {
        $report = CatchReport::factory()->create();
        $user = User::factory()->create();
        // リレーション経由でコメントを作成(catch_reportは自動セット)
        $report->comments()->create([
            'user_id' => $user->id,
            'body' => 'いいですね！',
        ]);

        $response = $this->getJson("/api/reports/{$report->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', 'いいですね！');
    }

    // ログインユーザーはコメントを投稿できる
    public function test_authenticated_user_can_post_comment(): void
    {
        $report = CatchReport::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/reports/{$report->id}/comments", [
            'body' => 'コメントテスト',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'コメントテスト');

        $this->assertDatabaseHas('comments', [
            'catch_report_id' => $report->id,
            'user_id' => $user->id,
            'body' => 'コメントテスト',
        ]);
    }

    // 未ログインではコメントできない
    public function test_guest_cannot_post_comment(): void
    {
        $report = CatchReport::factory()->create();

        $response = $this->postJson("/api/reports/{$report->id}/comments", [
            'body' => 'コメントテスト',
        ]);

        $response->assertStatus(401);
    }

    // 本文は必須(空はバリデーションエラー)
    public function test_comment_body_is_required(): void
    {
        $report = CatchReport::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/reports/{$report->id}/comments", [
            'body' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    // 本文は300文字まで(301文字はエラー)
    public function test_comment_body_has_max_length(): void
    {
        $report = CatchReport::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/reports/{$report->id}/comments", [
            'body' => str_repeat('あ', 301),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }
}
