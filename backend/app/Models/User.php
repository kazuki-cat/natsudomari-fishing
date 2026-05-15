<?php

namespace App\Models;

// メール認証を必須にしたい場合はこのコメントを外す
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
