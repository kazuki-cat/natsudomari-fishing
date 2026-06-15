<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CatchReport extends Model
{
    // テスト用ダミーデータ生成(CatchReportFactoryと連携)
    use HasFactory;

    // 一括代入を許可するカラムのリスト(セキュリティ対策)
    protected $fillable = [
        'user_id',
        'caught_at',
        'fish_name',
        'tackle',
        'latitude',
        'longitude',
        'location_name',
        'image_path',
        'memo',
    ];

    // カラムの型キャスト設定
    protected $casts = [
        'caught_at' => 'date:Y-m-d',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // JSONに自動で含める ※DBには無い算出プロパティ
    // 毎回image_pathから計算して作る
    protected $appends = ['image_url'];

    // CatchReportはUserにBelongsTo(多対一)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // CatchReportはCommentをhasMany(一対多)
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // image_pathから画像の完全URLを組み立てて返すアクセサ
    // デフォルトディスク(.envのFILESYSTEM_DISK)のURLを返す
    // 開発=public(http://.../storage/...)、本番=s3(S3のURL)。フロントはこれを使うだけ
    public function getImageUrlAttribute(): ?string
    {
        // ※url()の[Undefined method]警告は誤検知。実際は正常に動く
        return $this->image_path
            ? Storage::url($this->image_path)
            : null;
    }
}
