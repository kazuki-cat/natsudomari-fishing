<script setup lang="ts">
// 未ログインのユーザーに釣果投稿を促すCTA(タイムライン・マップの末尾に表示)
// tokenで判定し、ログイン中は表示しない(Cookieから同期で読めるのでチラ見えしない)
const { token } = useAuth();
</script>

<template>
  <!-- 未ログイン時だけ表示 -->
  <div v-if="!token" class="bg-white rounded-xl shadow p-6 mt-8 text-center">
    <p class="text-sea-700 font-bold mb-1">あなたの釣果も共有しませんか？</p>
    <p class="text-sm text-gray-500 mb-4">
      ログインすると、写真付きで釣果を投稿できます
    </p>
    <div class="flex justify-center gap-3">
      <!-- ログインボタン -->
      <NuxtLink
        to="/auth/login"
        class="bg-sea-600 hover:bg-sea-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition"
        >ログイン</NuxtLink
      >
      <!-- 新規登録ボタン -->
      <NuxtLink
        to="/auth/register"
        class="border border-sea-600 text-sea-600 hover:bg-sea-50 text-sm font-medium px-5 py-2 rounded-lg transition"
        >新規登録</NuxtLink
      >
    </div>
  </div>
</template>
