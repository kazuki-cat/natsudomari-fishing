// ログインはアクセスさせない(ゲスト専用)ページを保護するルートミドルウェア
export default defineNuxtRouteMiddleware(() => {
  // トークンはCookieから同期的に読めるので、描画前に判定できる
  // (isLoggedInはfetchUser(非同期)後に確定するため、チラ見えの原因になる)
  const { token } = useAuth();

  // トークンがある(ログイン済み)場合はトップページへリダイレクト
  if (token.value) {
    return navigateTo("/");
  }
});
