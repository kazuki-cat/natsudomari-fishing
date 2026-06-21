<?php

namespace Tests\Feature;

use App\Models\CatchReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// 認証API(登録・ログイン・ログアウト)のFeatureテスト
class AuthTest extends TestCase
{
    // 各テストごとにDBをマイグレーションし直してまっさらな状態にする
    use RefreshDatabase;

    // ユーザー登録が成功する
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 201 Created が返り、user情報とtokenが含まれる
        $response->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'name'], 'token']);

        // emailが漏れていないことを確認
        $this->assertArrayNotHasKey('email', $response->json('user'));

        // DBにユーザーが保存されている
        $this->assertDatabaseHas('users', ['email' => 'taro@example.com']);
    }

    // 不正な入力では登録できない(バリデーション)
    public function test_register_requires_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '', // 必須なのに空
            'email' => 'not-an-email', // メール形式ではない
            'password' => 'short', // 8文字未満、確認なし
        ]);

        // 422が返り、name/email/passwordにエラーが付く
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    // 正しい認証情報でログインできる
    public function test_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'taro@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'taro@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name'], 'token']);

        // emailが漏れていないことを確認
        $this->assertArrayNotHasKey('email', $response->json('user'));
    }

    // メールアドレスが存在しないとログインできない
    public function test_login_fails_with_nonexistent_email(): void
    {
        // ユーザーを1人も作らない(メールアドレスは存在しない)
        $response = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // パスワードが間違っているとログインできない
    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'taro@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'taro@example.com',
            'password' => 'wrongpassword',
        ]);

        // 認証失敗はemailにバリデーションエラーとして返る(422)
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ログイン中ユーザーはログアウトできる
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        // ログアウトは今使ってるトークンをDBから削除する処理
        // actingAs()だと偽トークンで削除できないので、本物のトークンを発行する
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'ログアウトしました']);

        // トークンがDBから削除されている
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // 未ログインではログアウトできない
    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }

    // ゲストログインができ、user情報とtokenが返る
    public function test_guest_can_login(): void
    {
        $response = $this->postJson('/api/guest-login');

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name'], 'token']);

        // ゲストユーザーがDBに作成される
        $this->assertDatabaseHas('users', ['email' => 'guest@example.com']);
    }

    // ゲストログインのたびに、前回のゲストの投稿・コメントがリセットされる
    public function test_guest_login_resets_previous_guest_data(): void
    {
        // 1回目のゲストログインでゲストユーザーを作る
        $this->postJson('/api/guest-login')->assertStatus(200);
        $guest = User::where('email', 'guest@example.com')->first();

        // ゲストの投稿と、ゲストが他人の投稿に付けたコメントを用意
        $ownReport = CatchReport::factory()->create(['user_id' => $guest->id]);
        $othersReport = CatchReport::factory()->create();
        $othersReport->comments()->create(['user_id' => $guest->id, 'body' => 'テスト']);

        // 投稿・コメントが存在することを確認
        $this->assertDatabaseHas('catch_reports', ['id' => $ownReport->id]);
        $this->assertDatabaseHas('comments', ['user_id' => $guest->id]);

        // 2回目のゲストログイン → 上のデータが消えるはず
        $this->postJson('/api/guest-login')->assertStatus(200);

        $this->assertDatabaseMissing('catch_reports', ['id' => $ownReport->id]);
        $this->assertDatabaseMissing('comments', ['user_id' => $guest->id]);
    }
}
