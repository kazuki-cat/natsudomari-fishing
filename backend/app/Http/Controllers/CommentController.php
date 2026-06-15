<?php

// 釣果投稿へのコメントを担当するコントローラー

namespace App\Http\Controllers;

use App\Models\CatchReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // コメント一覧取得 GET /api/reports/{catchReport}/comments
    public function index(CatchReport $catchReport): JsonResponse
    {
        // 指定した釣果投稿に紐づくコメントを、ユーザー情報付きで取得
        $comments = $catchReport->comments()->with('user')->latest()->get();

        return response()->json(['data' => $comments]);
    }

    // コメント投稿 POST /api/reports/{catchReport}/comments(要認証)
    public function store(Request $request, CatchReport $catchReport): JsonResponse
    {
        // バリデーション
        $validated = $request->validate([
            'body' => 'required|string|max:300', // 必須・300文字以内
        ]);

        // コメントをDBに保存
        // $catchReport->comments()でリレーション経由でcatch_report_idを自動セット
        $comment = $catchReport->comments()->create([
            'user_id' => Auth::id(), // ログイン中のユーザーID
            'body' => $validated['body'],
        ]);

        // 保存したコメントをuser情報付きで返す(HTTPステータス201 Created)
        return response()->json(['data' => $comment->load('user')], 201);
    }
}
