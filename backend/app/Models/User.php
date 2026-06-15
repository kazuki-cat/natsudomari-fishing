<?php

namespace App\Models;

// メール認証を必須にしたい場合はこのコメントを外す
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, // APIトークン認証(Sanctum)
        HasFactory,   // テスト用ダミーデータ生成
        Notifiable;   // メール通知機能

    // UserはCatchReportをhasMany(一対多)
    public function catchReports(): HasMany
    {
        return $this->hasMany(CatchReport::class);
    }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // passwordやremember_tokenをAPIレスポンスに含めないための設定。
    protected $hidden = [
        'password',
        'remember_token',
        'email',
    ];

    // カラムの型キャスト設定
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
