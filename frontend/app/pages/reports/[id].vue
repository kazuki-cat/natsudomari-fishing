<script setup lang="ts">
// 釣果詳細ページ([id] = 動的ルート)
import type { CatchReport } from "~/types/catchReport";

// 現在のルート情報(URLパラメータ・クエリ等)を取得
const route = useRoute();

const { fetchReport, deleteReport } = useCatchReports();
const { user, isLoggedIn } = useAuth();
const router = useRouter();

// 釣果データ(APIから取得後に格納)
const report = ref<CatchReport | null>(null);

// 日付を日本語形式にフォーマットする関数
// 'YYYY-MM-DD' → 'YYYY年M月D日'
const formatDate = (dateStr: string) => {
  const d = new Date(dateStr);
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`;
};

// ページ表示時に釣果詳細を取得してreportに格納
onMounted(async () => {
  const reportData = await fetchReport(Number(route.params.id));
  report.value = reportData.data;
});

// 削除処理
const handleDelete = async () => {
  // confirm = ブラウザのダイアログで確認(キャンセルなら処理中止)
  if (!confirm("この投稿を削除しますか？")) return;
  await deleteReport(Number(route.params.id));
  router.push("/reports");
};
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-8">
    <NuxtLink
      to="/reports"
      class="text-sea-600 hover:underline text-sm mb-4 block"
      >← タイムラインに戻る</NuxtLink
    >

    <!-- データ取得前はローディグ表示 -->
    <div v-if="!report" class="text-center py-12 text-gray-400">
      読み込み中...
    </div>

    <!-- データ取得後に表示 -->
    <template v-else>
      <!-- 釣果情報カード -->
      <div class="bg-white rounded-xl shadow p-6 mb-6">
        <!-- ヘッダー: タイトルと削除ボタン -->
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-sea-700">
              {{ report.fish_name }}
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">
              {{ formatDate(report.caught_at) }} / {{ report.location_name }}
            </p>
            <p class="text-sm text-gray-500 mt-0.5">
              投稿者：{{ report.user.name }}
            </p>
          </div>

          <!-- 削除ボタン: ログイン中かつ自分の投稿の場合のみ表示 -->
          <!-- user?.id = userがnullの場合のオプショナルチェーン -->
          <button
            v-if="isLoggedIn && user?.id === report.user.id"
            class="text-xs text-red-400 hover:text-red-600 transition"
            @click="handleDelete"
          >
            削除
          </button>
        </div>

        <!-- 釣果写真(あれば表示、なければダミー画像) -->
        <img
          v-if="report.image_path"
          :src="`/storage/${report.image_path}`"
          :alt="report.fish_name"
          class="w-full rounded-lg mb-4"
        />

        <!-- 写真なしのダミー表示 -->
        <div
          v-else
          class="w-full h-48 rounded-lg mb-4 bg-gradient-to-br from-sea-100 to-sea-200 flex flex-col items-center justify-center"
        >
          <p class="text-sm text-sea-700">写真なし</p>
        </div>

        <!-- 釣果の詳細情報 -->
        <div class="space-y-2 text-sm">
          <p>
            <span class="font-medium text-gray-700">仕掛け：</span
            >{{ report.tackle }}
          </p>
          <p
            v-if="report.memo"
            class="text-gray-500 bg-gray-50 rounded p-3 mt-2"
          >
            {{ report.memo }}
          </p>
        </div>

        <!-- 釣れた場所のミニマップ(座標がある投稿のみ表示) -->
        <div v-if="report.latitude && report.longitude" class="mt-4">
          <p class="text-sm font-medium text-gray-700 mb-2">📍 釣れた場所</p>
          <!-- ClientOnly = Leafletはブラウザ専用のため必須 -->
          <ClientOnly>
            <CatchSpotMap
              :latitude="report.latitude"
              :longitude="report.longitude"
              :image-path="report.image_path"
            />
          </ClientOnly>
        </div>
      </div>
    </template>
  </div>
</template>
