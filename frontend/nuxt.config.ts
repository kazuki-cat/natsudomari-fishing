// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2025-07-15",
  // 開発者ツールの有無
  devtools: { enabled: false },
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
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        {
          name: "description",
          content:
            "夏泊半島周辺のボートレンタル営業予報と釣果情報を共有するサービス",
        },
        // OGP(SNS共有時のカード表示用)
        { property: "og:title", content: "夏泊釣り情報" },
        {
          property: "og:description",
          content:
            "夏泊半島のボートレンタル営業予報と釣果情報を共有するサービス",
        },
        { property: "og:type", content: "website" },
        {
          property: "og:url",
          content: "https://natsudomari-fishing.duckdns.org",
        },
        {
          property: "og:image",
          content: "https://natsudomari-fishing.duckdns.org/ogp.png",
        },
        // Twitter/Xでリンクを貼ったときの設定
        { name: "twitter:card", content: "summary_large_image" },
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
