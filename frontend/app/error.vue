<script setup lang="ts">
// Nuxtの共通エラーページ。404・500・その他を1枚で出し分ける。
// 通常ページではなく、エラー発生時にアプリ全体に変わって表示される。
import type { NuxtError } from "#app";

// Nuxtが自動でエラー情報を渡してくる(statusCode/message等)
const props = defineProps<{ error: NuxtError }>();

useHead({ title: "エラー" });

// status(新・推奨)を優先し、無ければstatusCode(旧)にフォールバック
// ※statusCodeは非推奨だが保険として併用(環境差吸収)
const code = computed(() => props.error?.status ?? props.error?.statusCode);
// 404かどうか
const is404 = computed(() => code.value === 404);

// ステータスごとにタイトルを出し分ける
const title = computed(() => {
  if (is404.value) return "ページが見つかりません";
  if (code.value === 500) return "サーバーエラーが発生しました";
  return "エラーが発生しました";
});

// ステータスごとに説明文を出し分ける
const message = computed(() => {
  if (is404.value)
    return "お探しのページは存在しないか、削除された可能性があります。";
  if (code.value === 500) return "時間をおいて再度お試しください。";
  return "予期せぬエラーが発生しました。";
});

// トップへ戻る(エラー状態をクリアしてから遷移する)
// ※clearErrorを使わないとエラー画面が残り続けるため必須
const handleHome = () => clearError({ redirect: "/" });
</script>

<template>
  <!-- 共通レイアウト(ヘッダー/フッター)を適用して統一感を出す -->
  <NuxtLayout>
    <div class="max-w-md mx-auto px-4 py-20 text-center">
      <!-- 大きなステータス番号(404等) -->
      <p class="text-7xl font-bold">{{ code || "エラー" }}</p>
      <h1 class="text-2xl font-bold text-sea-800 mt-4">{{ title }}</h1>
      <p class="text-sm text-gray-500 mt-2">{{ message }}</p>

      <!-- トップへ戻るボタン -->
      <button
        class="mt-8 bg-sea-600 hover:bg-sea-700 text-white font-bold px-6 py-2.5 rounded-lg transition"
        @click="handleHome"
      >
        トップページへ戻る
      </button>
    </div>
  </NuxtLayout>
</template>
