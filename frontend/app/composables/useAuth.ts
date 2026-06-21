// 認証に関するロジックをまとめたComposable
// どのページやコンポーネントからも`const { login } = useAuth()`で使える

import type { User } from "~/types/catchReport";

export const useAuth = () => {
  // State(状態管理)
  const user = useState<User | null>("auth.user", () => null);

  // 認証トークンをCookieに保存する
  // maxAge = Cookieの有効期限(秒): 60秒 × 60分 × 24時間 × 7日 = 1週間
  const token = useCookie<string | null>("auth_token", {
    maxAge: 60 * 60 * 24 * 7,
  });

  // ログイン状態を表す算出プロパティ
  const isLoggedIn = computed(() => !!user.value);

  // 認証付きAPIリクエスト関数
  // Authorizationヘッダーにトークンを自動付与してAPIを叩くラッパー関数
  // 投稿・削除・ログアウトなど認証が必要なAPIを呼ぶときはこれを使う
  const apiFetch = <T>(
    url: string,
    options: Parameters<typeof $fetch>[1] = {},
  ) => {
    return $fetch<T>(url, {
      ...options,

      headers: {
        ...((options.headers as Record<string, string>) || {}),
        ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
      },
    });
  };

  // ログイン処理
  const login = async (email: string, password: string) => {
    // POST /api/loginにリクエスト
    const data = await $fetch<{ user: User; token: string }>("/api/login", {
      method: "POST",
      body: { email, password },
    });
    // Cookieとステートにトークン・ユーザー情報を保存
    token.value = data.token;
    user.value = data.user;
    return data;
  };

  // ユーザー登録処理
  const register = async (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string,
  ) => {
    const data = await $fetch<{ user: User; token: string }>("/api/register", {
      method: "POST",
      body: {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      },
    });
    user.value = data.user;
    token.value = data.token;
    return data;
  };

  // ゲストログイン処理(登録不要でゲスト用トークンを取得)
  const guestLogin = async () => {
    // POST /api/guest-login → userとtokenが返る
    const data = await $fetch<{ user: User; token: string }>(
      "/api/guest-login",
      { method: "POST" },
    );
    // 通常ログインと同じく、Cookieとステートに保存
    token.value = data.token;
    user.value = data.user;
    return data;
  };

  // ログアウト処理
  const logout = async () => {
    if (token.value) {
      // サーバー側のトークンを削除(失敗しても続行するため、catch()で無視)
      await apiFetch("/api/logout", { method: "POST" }).catch(() => {});
    }
    // ローカルのトークンとユーザー情報をクリア
    token.value = null;
    user.value = null;
  };

  // ページ読み込み時にCookieのトークンでログイン状態を復元する
  // plugins/auth.client.tsから呼ばれる
  const fetchUser = async () => {
    if (!token.value) return; // トークンがなければスキップ
    try {
      // GET /api/user でログイン中のユーザー情報を取得
      const data = await apiFetch<{ user: User }>("/api/user");
      user.value = data.user;
    } catch {
      // トークンが無効(期限切れ等)の場合はクリア
      token.value = null;
      user.value = null;
    }
  };

  // 外部から使える値と関数を返す
  return {
    user,
    token,
    isLoggedIn,
    login,
    register,
    guestLogin,
    logout,
    fetchUser,
    apiFetch,
  };
};
