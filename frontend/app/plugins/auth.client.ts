// plugins ＝ Nuxtアプリ起動時(全ページ読み込み前)に一度だけ実行される処理
// クライアントサイド専用のプラグイン(.client.ts = ブラウザでのみ実行)

// ページをリロードしたときにCookieのトークンでログイン状態を復元
export default defineNuxtPlugin(async () => {
  const { fetchUser } = useAuth();
  await fetchUser();
});
