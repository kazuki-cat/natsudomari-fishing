<script setup lang="ts">
// タイムラインに表示する釣果カードコンポーネント(1つの釣果投稿を1枚のカードとして表示)
import type { CatchReport } from "~/types/catchReport";

// pages/reports/index.vueから受け取る
defineProps<{ report: CatchReport }>();

// 日付を日本語表示にフォーマットする関数('YYYY-MM-DD' → 'YYYY年M月D日')
const formatDate = (dateStr: string) => {
  const d = new Date(dateStr);
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`;
};
</script>
<template>
  <!-- カード全体(ホバー時に影が濃くなるトランジション付き) -->
  <div class="bg-white rounded-xl shadow hover:shadow-md transition p-5">
    <!-- カードヘッダー: 魚種・日付・釣れた場所 -->
    <div class="flex items-start justify-between mb-3">
      <div>
        <p class="font-bold text-sea-700 text-lg">{{ report.fish_name }}</p>
        <p class="text-xs text-gray-400">
          {{ formatDate(report.caught_at) }} / {{ report.location_name }}
        </p>
      </div>
      <!-- ユーザー名バッジ -->
      <span class="text-xs bg-sea-100 text-sea-700 px-2 rounded-full">{{
        report.user.name
      }}</span>
    </div>

    <!-- 写真がある場合は表示、ない場合はダミー画像 -->
    <img
      v-if="report.image_path"
      :src="`/storage/${report.image_path}`"
      :alt="report.fish_name"
      class="w-full h-40 object-cover rounded-lg mb-3"
    />

    <!-- ダミー画像(グラデーション背景に"写真なしテキスト")-->
    <div
      v-else
      class="w-full h-40 rounded-lg mb-3 bg-gradient-to-br from-sea-100 to-sea-200 flex flex-col items-center justify-center gap-1"
    >
      <span class="text-xl text-sea-700">写真なし</span>
    </div>

    <!-- 釣果の基本情報 -->
    <div class="text-sm text-gray-600 space-y-1">
      <p>
        <span class="font-medium text-gray-700">仕掛け：</span
        >{{ report.tackle }}
      </p>

      <!-- 100文字超はタイムラインでは切り捨て(100文字以上)、全文は詳細ページで表示 -->
      <p v-if="report.memo" class="text-gray-500 italic">
        {{
          report.memo.length > 100
            ? report.memo.slice(0, 100) + "..."
            : report.memo
        }}
      </p>
    </div>

    <!-- カードフッター: コメント件数・詳細リンク -->
    <div class="mt-3 flex items-center justify-between">
      <span class="text-xs text-gray-400">💬 {{ report.comments_count }}</span>
      <NuxtLink
        :to="`/reports/${report.id}`"
        class="text-xs text-sea-600 hover:text-sea-800 font-medium transition"
        >詳細を見る →</NuxtLink
      >
    </div>
  </div>
</template>
