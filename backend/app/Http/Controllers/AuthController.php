<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

// ユーザー認証に関するAPIを担当するコントローラー
class AuthController extends Controller
{
    // ユーザ登録 POST /api/register
    public function register(Request $request): JsonResponse
    {
        // バリデーション(失敗すると自動で422エラーを返す)
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        // ユーザーをDBに保存
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // APIトークンを発行(Sanctumの機能)
        $token = $user->createToken('auth_token')->plainTextToken;

        // ユーザ情報とトークンをJSON形式で返す(HTTPステータス201 Created)
        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    // ログイン POST /api/login
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // メールアドレスでユーザーを検索
        $user = User::where('email', $credentials['email'])->first();

        // ユーザが存在しない、またはパスワード不一致の場合はエラー
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['認証情報が正しくありません'],
            ]);
        }

        // 新しいトークンを発行して返す
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    // ログアウト POST /api/logout(要認証)
    public function logout(Request $request): JsonResponse
    {
        // 現在のリクエストで使われているトークンのみを削除(他のデバイスのトークンは残る)
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ログアウトしました']);
    }

    // ログイン中ユーザーの情報取得 GET /api/user(要認証)
    public function user(Request $request): JsonResponse
    {
        // auth:sanctumミドルウェアが認証済みなので、$request->user()で取得できる
        return response()->json(['user' => $request->user()]);
    }
}
