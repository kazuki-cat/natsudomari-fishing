<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    // 一括代入を許可するカラム
    protected $fillable = ['catch_report_id', 'user_id', 'body'];

    // CommentはUserにBelongsTo(多対一)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // CommentはCatchReportにbelongsTo(多対一)
    public function catchReport(): BelongsTo
    {
        return $this->belongsTo(CatchReport::class);
    }
}
