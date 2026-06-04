// 釣果・コメント・ユーザーのTypeScript型定義

// ユーザー情報
export interface User {
  id: number;
  name: string;
  email: string;
}

// 釣果投稿の型(APIレスポンスの形状)
export interface CatchReport {
  id: number;
  user: User;
  caught_at: string;
  fish_name: string;
  tackle: string;
  latitude: number; // 緯度
  longitude: number; // 経度
  location_name: string;
  image_path: string | null;
  image_url: string | null; // 画像の完全URL(バックエンドのimage_urlアクセサが返す)
  memo: string | null;
  comments_count: number; // コメント件数
  created_at: string;
}

// コメント型
export interface Comment {
  id: number;
  user: User;
  body: string;
  created_at: string;
}

// 釣果投稿フォームの型(画像はFileオブジェクト、座標はnull許容)
export interface CatchReportForm {
  caught_at: string;
  fish_name: string;
  tackle: string;
  latitude: number | null; // 地図でピンを立てる前はnull
  longitude: number | null;
  location_name: string;
  memo: string;
  image: File | null; // ファイル選択前はnull
}
