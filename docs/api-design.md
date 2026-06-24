# API設計書 - 夏泊釣り情報サービス

## 概要

| 項目 | 内容 |
|------|------|
| ベースURL | `/api` |
| 認証方式 | Bearerトークン(Laravel Sanctum) |
| リクエスト形式 | JSON(画像投稿のみ `multipart/form-data`) |
| 認証ヘッダー | `Authorization: Bearer {token}` |

- `register` / `login` で発行されたトークンを、認証が必要なAPIの`Authorization`ヘッダーに付与する
- 認証不要のAPIは未ログインでも利用可能

---

## エンドポイント一覧

| 分類 | メソッド | パス | 認証 | 説明 |
|------|----------|------|------|------|
| 認証 | POST | `/api/register` | - | ユーザー登録 |
| 認証 | POST | `/api/login` | - | ログイン |
| 認証 | POST | `/api/guest-login` | - | ゲストユーザーログイン(登録不要・トークン発行) |
| 認証 | POST | `/api/logout` | ✔︎ | ログアウト |
| 認証 | GET | `/api/user` | ✔︎ | ログイン中ユーザー取得 |
| 釣果 | GET | `/api/reports` | - | 釣果一覧(ページネーション/フィルター) |
| 釣果 | GET | `/api/reports/map` | - | マップ用(座標付き釣果) |
| 釣果 | GET | `/api/reports/{id}` | - | 釣果詳細 |
| 釣果 | POST | `/api/reports` | ✔︎ | 釣果投稿(画像可) |
| 釣果 | DELETE | `/api/reports/{id}` | ✔︎ | 釣果削除(本人のみ) |
| コメント | GET | `/api/reports/{id}/comments` | - | コメント一覧 |
| コメント | POST | `/api/reports/{id}/comments` | ✔︎ | コメント投稿 |
| 天気 | GET | `/api/weather` | - | 営業予報・週間天気 |

> `register` / `login`は`throttle:6,1`(1分間に6回まで)でブルートフォース対策。
> `POST /reports`・`POST .../comments`は`throttle:20,1`(1分間に20回まで)で連投スパム対策。

---

## 認証 API

### POST /api/register - ユーザー登録
- **認証**: 不要
- **リクエスト**
```json
{
  "name": "田中太郎",
  "email": "taro@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
- **バリデーション**: `name` 必須/255文字以内、`email` 必須/メール形式/重複不可、`password` 必須/8文字以上/確認一致
- **レスポンス(201 Created)**
```json
{
  "user": { "id":1, "name": "田中太郎", "created_at": "..."},
  "token": "1|xxxxxxxxxxxxxxxxxx"
}
```
- ※セキュリティのため、`email`はレスポンスに含めない(`$hidden`)
- **エラー**: 422(バリデーション失敗)

### POST /api/login - ログイン
- **認証** 不要
- **リクエスト**: `{ "email": "...", "password": "..." }`
- **レスポンス(200)**: `{ "user": {...}, "token": "..." }`
- **エラー**: 422(認証情報が正しくない)/ 429(試行回数超過)

### POST /api/guest-login - ゲストログイン
- **認証**: 不要
- 登録不要でゲスト用のトークンを発行する(「ゲストでログイン」ボタン用)
- ログインのたびに、ゲスト自身の過去データ(釣果・コメント・画像)をリセットしてからトークンを発行する
- **レスポンス(200)**: `{ "user": {...}, "token": "..." }`
- **レート制限**: `throttle:6,1`(1分間に6回まで)
- **エラー**: 429(試行回数超過)

### POST /api/logout - ログアウト
- **認証**: 必要
- 現在のトークンのみ削除(他デバイスのトークンは残す)
- **レスポンス(200)**: `{ "message": "ログアウトしました"}`
- **エラー** 401(未認証)

### GET /api/user - ログイン中ユーザー取得
- **認証**: 必要
- **レスポンス(200)**: `{ "user": {"id": "...", "name": "..." } }`

---

## 釣果 API

### GET /api/reports - 釣果一覧
- **認証**: 不要
- **クエリパラメータ**

| 名前 | 説明 |
|------|------|
| `page` | ページ番号(1ページ10件) |
| `sort` | `newest`(既定)/`oldest`(釣れた日順) |
| `fish_name` | 魚種で絞り込み |
| `location_name` | 釣り場名で絞り込み |
| `tackle` | 仕掛けで絞り込み |

- **レスポンス(200)**: ページネーション形式
```json
{
  "current_page": 1,
  "last_page": 3,
  "data": [
    {
      "id": 1, "fish_name": "アジ", "tackle": "サビキ釣り",
      "caught_at": "2026-06-10", "location_name": "夏泊半島・大島",
      "image_url": "https://.../catch_images/xxx.jpg",
      "comments_count": 2, "user": { "id": 1, "name": "田中太郎" }
    }
  ]
}
```

### GET /api/reports/map - マップ用釣果
- **認証**: 不要
- 緯度・経度が両方ある投稿のみ返す
- **レスポンス(200)**: `{ "data": [ { ...} ]}`

### GET /api/reports/{id} - 釣果詳細
- **認証**: 不要
- **レスポンス(200)**: `{ "data": { ...釣果, "user": {...}, "comments_count": 2} }`

### POST /api/reports - 釣果投稿
- **認証**: 必須
- **リクエスト形式**: `multipart/form-data`(画像送信のため)

| フィールド | 必須 | 説明 |
|------------|------|------|
| `caught_at` | ✔︎ | 釣れた日(今日以前) |
| `fish_name` | ✔︎ | 魚種(100文字以内) |
| `tackle` | ✔︎ | 仕掛け(100文字以内) |
| `location_name` | ✔︎ | 釣り場名(100文字以内) |
| `latitude` | - | 緯度(-90〜90) |
| `longitude` | - | 経度(-180〜180) |
| `memo` | - | メモ(1000文字以内) |
| `image` | - | 画像ファイル(10MB以内) |

- **レスポンス(201)**: `{ "data": { ...投稿, "user": {...} } }`
- **レート制限**: `throttle:20,1`(1分間に20回まで・連投スパム対策)
- **エラー**: 401(未認証)/422(バリデーション失敗)/429(リクエスト過多)

### DELETE /api/reports/{id} - 釣果削除
- **認証**: 必要(**投稿者本人のみ**)
- 画像があればストレージからも削除
- **レスポンス(200)**: `{ "message": "削除しました" }`
- **エラー**: 401(未認証)/403(他人の投稿)

---

## コメント API

### GET /api/reports/{id}/comments - コメント一覧
- **認証**: 不要
- **レスポンス(200)**: `{ "data": [ { "id": 1, "body": "...", "user": {...}, "created_at": "..." } ] }`

### POST /api/reports/{id}/comments - コメント投稿
- **認証**: 必須
- **リクエスト**: `{ "body": "コメント本文" }`(300文字以内)
- **レスポンス(201)**: `{ "data": { ...コメント, "user": {...} } }`
- **レート制限**: `throttle:20,1`(1分間に20回まで・連投スパム対策)
- **エラー**: 401(未認証)/422(本文が空or 300文字超)/429(リクエスト過多)

---

## 天気 API

### GET /api/weather - 営業予報・週間天気
- **認証**: 不要
- 気象庁API(青森県・津軽エリア)から取得・整形して返す
- **レスポンス(200)**
```json
{
  "temperature": 20,
  "windSpeed": 3.0,
  "windDirection": "南",
  "weatherDescription": "晴れ",
  "waveHeight": "0.5メートル",
  "waveHeightValue": 0.5,
  "forecast": [
    { "date": "06/17(火)", "weatherDescription": "曇",
    "windSpeed": 5.0, "pop": 40,
    "temperatureMax": 25, "temperatureMin": 15 }
  ]
}
```
- 取得失敗時はフォールバック(各種 null / `weatherDescription: "取得失敗"`)

---

## 共通エラーレスポンス

| ステータス | 意味 |
|:---:|------|
| 401 | 未認証(トークン無効/未送信) |
| 403 | 権限なし(他人のリソース操作) |
| 422 | バリデーションエラー |
| 429 | リクエスト過多(throttle) |

バリデーションエラー(422)の形式:
```json
{
  "message": "...",
  "errors": { "email": ["..."] }
}
```
