---
# 夏泊ボート釣りの情報サービス
青森県の夏泊半島周辺でボート釣りの運行有無を予測したWebサービスです。
ユーザー間での釣果情報の共有機能も実装しております。
---

## 画像イメージ

## ※完成後に追加予定

## 機能

- **当日のボート営業予想** 気象庁APIを活用した当日の営業予報の確認と週間天気予想
- **釣果投稿** - 魚種・仕掛け・釣り場をドロップダウンで選択、地図でピン指定・写真アップロード
- **タイムライン** - 釣り日の新旧ソート・魚種・場所・仕掛けでのフィルター機能
- **釣り場マップ** - 投稿された釣果をOpenStreetMap上でピン表示、クリック詳細へ
- **コメント** - 投稿へのコメントで情報交換

---

## 技術スタック

| カテゴリ       | 技術                                    |
| -------------- | --------------------------------------- |
| フロントエンド | Nuxt.js 3 / TypeScript / TailwindCSS    |
| バックエンド   | Laravel 11 (PHP 8.2 ) / Laravel Sanctum |
| データベース   | MySQL 8.0                               |
| 天気データ     | 気象庁API (無料・APIキー不要)           |
| 地図           | Leaflet.js + OpenStreetMap              |
| ファイル保存   | AWS S3                                  |
| インフラ       | AWS EC2 + RDS                           |
| 開発環境       | Docker / Nginx                          |

---

## ローカル起動手順

```bash
git clone https://github.com/kazuki-cat/natsudomari-fishing.git
cd natsudomari-fishing

docker compose up -d
```

- フロントエンド: http://localhost
- API: http://localhost/api

---

## 作者

## kazuki
