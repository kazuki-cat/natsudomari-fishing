// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2025-07-15",
  // 開発者ツールの有無
  devtools: { enabled: true },
  // 使用するNuxtモジュール
  modules: ["@nuxtjs/tailwindcss"],
  // グローバルCSSの読み込み
  css: ["~/assets/css/main.css"],

  // コンポーネントの自動インポート設定
  components: {
    dirs: [
      {
        path: "~/components",
        pathPrefix: false,
      },
    ],
  },

  // サーバーサイド・クライアントサイド両方で使える環境変数の設定
  runtimeConfig: {
    public: {
      apiBase: "/api",
    },
  },

  // <head>タグの設定(SEO・メタ情報)
  app: {
    head: {
      title: "夏泊ボート釣り情報",
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        {
          name: "description",
          content:
            "夏泊半島周辺のボートレンタル営業予報と釣果情報を共有するサービス",
        },
      ],
      link: [
        // Google Fontsの高速化のための事前接続
        { rel: "preconnect", href: "https://fonts.googleapis.com" },
        {
          rel: "stylesheet",
          href: "https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap",
        },
      ],
    },
  },
});
