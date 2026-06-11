<?php

namespace App\Http\Controllers;

use App\Models\CatchReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// 釣果投稿のCRUDを担当するコントローラー
class CatchReportController extends Controller
{
    // 釣果一覧取得(タイムライン用) GET /api/reports
    // クエリパラメータ: sort(newest/oldest), fish_name, location_name, tackle
    public function index(Request $request): JsonResponse
    {
        $query = CatchReport::with('user')->withCount('comments');

        // フィルター: 魚種
        if ($request->filled('fish_name')) {
            $query->where('fish_name', $request->fish_name);
        }

        // フィルター: 釣り場名
        if ($request->filled('location_name')) {
            $query->where('location_name', $request->location_name);
        }

        // フィルター: 仕掛け
        if ($request->filled('tackle')) {
            $query->where('tackle', $request->tackle);
        }

        // ソート: caught_at(釣った日)で並び替え。デフォルトは新しい順
        if ($request->sort === 'oldest') {
            $query->orderBy('caught_at', 'asc');
        } else {
            $query->orderBy('caught_at', 'desc');
        }

        return response()->json($query->paginate(10));
    }

    // マップ表示用の釣果取得 GET /api/reports/map
    // 座標(latitude/longitude)が入力されている投稿のみ返す
    public function mapIndex(): JsonResponse
    {
        $reports = CatchReport::with('user')
            ->whereNotNull('latitude')  // 緯度がnullではないもの
            ->whereNotNull('longitude') // 経度がnullではないもの
            ->latest()
            ->get();

        return response()->json(['data' => $reports]);
    }

    // 釣果投稿 POST /api/reports(要認証)
    public function store(Request $request): JsonResponse
    {
        // バリデーション
        $validated = $request->validate([
            'caught_at'     => 'required|date|before_or_equal:today', // 必須・日付形式・今日以前
            'fish_name'     => 'required|string|max:100',             // 必須・100文字以内
            'tackle'        => 'required|string|max:100',             // 必須・100文字以内
            'latitude'      => 'nullable|numeric|between:-90,90',     // 任意・数値・緯度の範囲内
            'longitude'     => 'nullable|numeric|between:-180,180',   // 任意・数値・経度の範囲内
            'location_name' => 'required|string|max:100',             // 必須・100文字以内
            'memo'          => 'nullable|string|max:1000',            // 必須・1000文字以内
            'image'         => 'nullable|image|max:10240'             // 任意・画像ファイル・10MB以内
        ]);

        // 画像ファイルが送られてきた場合はストレージに保存
        $imagePath = null;
        if ($request->hasFile('image')) {
            // デフォルトディスク(.envのFILESYSTEM_DISK)のcatch_imagesフォルダに保存しパスを返す
            // 開発=public(storage/app/public→/storageで配信)、本番=s3(S3に保存)
            $imagePath = $request->file('image')->store('catch_images');
        }

        // DBに保存(Auth:id()でログイン中のユーザーIDを取得)
        $report = CatchReport::create([
            ...$validated, // バリデーション済みデータを展開
            'user_id' => Auth::id(),
            'image_path' => $imagePath,
        ]);

        // 保存した投稿をuser情報付きで返す(HTTPステータス201 Created)
        return response()->json(['data' => $report->load('user')], 201);
    }

    // 釣果詳細情報取得 GET /api/reports/{id}
    public function show(CatchReport $catchReport): JsonResponse
    {
        // $catchReport はLaravelのルートモデルバイディング
        // URLの{catchReport}のIDで自動的にDBからレコードを取得する
        return response()->json([
            'data' => $catchReport->load('user')->loadCount('comments'),
        ]);
    }

    // 釣果削除 DELETE /api/reports/{id}(要認証・本人のみ)
    public function destroy(CatchReport $catchReport): JsonResponse
    {
        // 投稿者本人かチェック(他人の投稿は削除できない)
        if ($catchReport->user_id !== Auth::id()) {
            return response()->json(['message' => '権限がありません'], 403);
        }

        // 画像ファイルが存在する場合は一緒に削除(デフォルトディスクから)
        if ($catchReport->image_path) {
            Storage::delete($catchReport->image_path);
        }

        // DBからレコードを削除
        $catchReport->delete();

        return response()->json(['message' => '削除しました']);
    }
}
