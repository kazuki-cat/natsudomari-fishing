<script setup lang="ts">
// 全ページ共通のヘッダーナビゲーション
// ログイン状態によってメニューの内容が変わる

const { isLoggedIn, logout } = useAuth();
const router = useRouter();

// モバイルメニューの開閉状態
const mobileOpen = ref(false);

// ログアウト処理
const handleLogout = async () => {
  await logout(); // トークン削除・ユーザー状態クリア
  router.push("/"); // トップページへ遷移
  mobileOpen.value = false; // モバイルメニューを閉じる
};
</script>

<template>
  <header class="bg-sea-700 text-white shadow-md">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
      <!-- サイトロゴ(クリックでトップへ) -->
      <NuxtLink
        to="/"
        class="text-lg font-bold tracking-wide hover:opacity-80 transition"
      >
        夏泊釣り情報
      </NuxtLink>

      <!-- デスクトップ用ナビゲーション(md以上の画面サイズで表示) -->
      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <NuxtLink to="/" class="hover:text-sea-200 transition"
          >営業予報</NuxtLink
        >
        <NuxtLink to="/reports" class="hover:text-sea-200 transition"
          >釣果タイムライン</NuxtLink
        >
        <NuxtLink to="/map" class="hover:text-sea-200 transition"
          >釣り場マップ</NuxtLink
        >

        <!-- ログイン中のメニュー -->
        <template v-if="isLoggedIn">
          <NuxtLink
            to="/reports/new"
            class="bg-sea-500 hover:bg-sea-400 px-3 py-1.5 rounded transition"
          >
            釣果を投稿
          </NuxtLink>
          <button class="hover:text-sea-200 transition" @click="handleLogout">
            ログアウト
          </button>
        </template>

        <!-- 未ログイン時のメニュー -->
        <template v-else>
          <NuxtLink to="/auth/login" class="hover:text-sea-200 transition"
            >ログイン</NuxtLink
          >
          <NuxtLink
            to="/auth/register"
            class="bg-sea-500 hover:bg-sea-400 px-3 py-1.5 rounded transition"
            >新規登録</NuxtLink
          >
        </template>
      </nav>

      <!-- モバイル用ハンバーガーボタン(md未満の画面サイズで表示) -->
      <button class="md:hidden p-2" @click="mobileOpen = !mobileOpen">
        <span class="block w-6 h-0.5 bg-white mb-1" />
        <span class="block w-6 h-0.5 bg-white mb-1" />
        <span class="block w-6 h-0.5 bg-white" />
      </button>
    </div>

    <!-- モバイル用ドロワーメニュー(mobileOpenがtrueのとき表示) -->
    <div
      v-if="mobileOpen"
      class="md:hidden bg-sea-800 px-4 py-3 flex flex-col items-center gap-4 text-sm"
    >
      <NuxtLink to="/" class="hover:text-sea-200" @click="mobileOpen = false"
        >営業予報</NuxtLink
      >
      <NuxtLink
        to="/reports"
        class="hover:text-sea-200"
        @click="mobileOpen = false"
        >釣果タイムライン</NuxtLink
      >
      <NuxtLink to="/map" class="hover:text-sea-200" @click="mobileOpen = false"
        >釣り場マップ</NuxtLink
      >
      <template v-if="isLoggedIn">
        <NuxtLink
          to="/reports/new"
          class="hover:text-sea-200"
          @click="mobileOpen = false"
          >釣果を投稿</NuxtLink
        >
        <button class="hover:text-sea-200" @click="handleLogout">
          ログアウト
        </button>
      </template>
      <template v-else>
        <NuxtLink
          to="/auth/login"
          class="hover:text-sea-200"
          @click="mobileOpen = false"
          >ログイン</NuxtLink
        >
        <NuxtLink
          to="/auth/register"
          class="hover:text-sea-200"
          @click="mobileOpen = false"
          >新規登録</NuxtLink
        >
      </template>
    </div>
  </header>
</template>
