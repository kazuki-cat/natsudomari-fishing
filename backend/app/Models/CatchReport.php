<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatchReport extends Model
{
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
}
