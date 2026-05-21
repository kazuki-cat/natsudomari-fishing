// 認証が必要なページを保護するルートミドルウェア
export default defineNuxtRouteMiddleware(() => {
  // useAuthからisLoggedIn(ログイン情報)を取得
  const { isLoggedIn } = useAuth();

  // ログインしていない場合はログインページへリダイレクト
  if (!isLoggedIn.value) {
    return navigateTo("/auth/login");
  }
});
