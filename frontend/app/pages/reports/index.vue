<script setup lang="ts">
// 釣果タイムラインページ(ログイン不要で誰でも閲覧可能)
// フィルター(魚種・仕掛け・釣り場名)とソート(新しい順・古い順)に対応
// ページネーションで10件ずつ表示

useHead({ title: "釣果タイムライン" });

const { reports, loading, currentPage, lastPage, fetchReports } =
  useCatchReports();
const { isLoggedIn } = useAuth();
const error = ref(false);

// フィルター・ソートの状態
const sort = ref<"newest" | "oldest">("newest");
const filterFish = ref("");
const filterTackle = ref("");
const filterLocation = ref("");

// 魚種・仕掛け・釣り場名の選択肢(投稿フォームと同じリスト)
const fishOptions = [
  "メバル",
  "アイナメ",
  "ソイ",
  "ヒラメ",
  "カレイ",
  "マダイ",
  "アジ",
  "サバ",
  "イワシ",
  "イナダ",
  "ワラサ",
  "ブリ",
  "シイラ",
  "サワラ",
  "マグロ",
  "シーバス",
  "ヤリイカ",
  "クロダイ",
  "マゴチ",
  "タコ",
  "その他",
];
const tackleOptions = [
  "サビキ釣り",
  "泳がせ釣り",
  "イソメ釣り",
  "フカセ釣り",
  "ロックフィッシュ",
  "ショアジギング",
  "タイラバゲーム",
  "メバリング",
  "アジング",
  "エギング",
  "その他",
];
const locationOptions = [
  "夏泊半島・大島",
  "小湊漁港",
  "清水川漁港",
  "東滝漁港",
  "白浜漁港",
  "稲生漁港",
  "浦田漁港",
  "茂浦潐港",
  "浪打漁港",
  "土屋漁港",
  "その他",
];

// フィルター適用中だとtrueを返す
const isFiltered = computed(() => {
  return (
    filterFish.value ||
    filterTackle.value ||
    filterLocation.value ||
    sort.value !== "newest"
  );
});

// 現在のフィルター・ソート設定でページ1から取得
const applyFilters = () => {
  error.value = false;
  fetchReports({
    page: 1,
    sort: sort.value,
    fish_name: filterFish.value,
    tackle: filterTackle.value,
    location_name: filterLocation.value,
  }).catch(() => {
    error.value = true;
  });
};

// フィルターリセット
const resetFilters = () => {
  sort.value = "newest";
  filterFish.value = "";
  filterTackle.value = "";
  filterLocation.value = "";
  applyFilters();
};

// ページ変更(フィルター設定を維持したまま)
const changePage = (page: number) => {
  fetchReports({
    page,
    sort: sort.value,
    fish_name: filterFish.value,
    tackle: filterTackle.value,
    location_name: filterLocation.value,
  }).catch(() => {
    error.value = true;
  });
};

onMounted(applyFilters);
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <!-- ヘッダー -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-sea-800">釣果タイムライン</h1>
      <NuxtLink
        v-if="isLoggedIn"
        to="/reports/new"
        class="bg-sea-600 hover:bg-sea-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
      >
        釣果投稿
      </NuxtLink>
    </div>

    <!-- フィルター・ソートパネル -->
    <div class="bg-white rounded-xl shadow p-4 mb-6 space-y-3">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <!-- ソート -->
        <div>
          <label for="fishingDay" class="block text-xs text-gray-500 mb-1"
            >並び替え</label
          >
          <select
            id="fishingDay"
            v-model="sort"
            class="w-full border rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sea-400"
          >
            <option value="newest">釣れた日：新しい順</option>
            <option value="oldest">釣れた日：古い順</option>
          </select>
        </div>

        <!-- 魚種フィルター -->
        <div>
          <label for="fish" class="block text-xs text-gray-500 mb-1"
            >魚種</label
          >
          <select
            id="fish"
            v-model="filterFish"
            class="w-full border rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sea-400"
          >
            <option value="">すべて</option>
            <option v-for="opt in fishOptions" :key="opt" :value="opt">
              {{ opt }}
            </option>
          </select>
        </div>

        <!-- 仕掛けフィルター -->
        <div>
          <label for="tackle" class="block text-xs text-gray-500 mb-1"
            >仕掛け</label
          >
          <select
            id="tackle"
            v-model="filterTackle"
            class="w-full border rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sea-400"
          >
            <option value="">すべて</option>
            <option v-for="opt in tackleOptions" :key="opt" :value="opt">
              {{ opt }}
            </option>
          </select>
        </div>

        <!-- 釣り場名フィルター -->
        <div>
          <label for="location" class="block text-xs text-gray-500 mb-1"
            >釣り場名</label
          >
          <select
            id="location"
            v-model="filterLocation"
            class="w-full border rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sea-400"
          >
            <option value="">すべて</option>
            <option v-for="opt in locationOptions" :key="opt" :value="opt">
              {{ opt }}
            </option>
          </select>
        </div>
      </div>

      <!-- ボタン -->
      <div class="flex gap-2 justify-end">
        <button
          v-if="isFiltered"
          class="text-xs text-gray-400 hover:text-gray-600 px-3 py-1.5 rounded-lg border transition"
          @click="resetFilters"
        >
          リセット
        </button>
        <button
          class="text-xs bg-sea-600 hover:bg-sea-700 text-white font-medium px-4 py-1.5 rounded-lg transition"
          @click="applyFilters"
        >
          絞り込む
        </button>
      </div>
    </div>
    <!-- エラー -->
    <div v-if="error" class="text-center py-12 text-red-400">
      <p>データの取得に失敗しました</p>
      <button class="text-sea-600 underline mt-2 text-sm" @click="applyFilters">
        再試行
      </button>
    </div>

    <!-- ローディング -->
    <div v-else-if="loading" class="text-center py-12 text-gray-400">
      読み込み中...
    </div>

    <!-- 結果なし -->
    <div
      v-else-if="reports.length === 0"
      class="text-center py-12 text-gray-400"
    >
      <p>条件に一致する釣果がありません</p>
      <button
        v-if="isFiltered"
        class="text-sea-600 underline mt-2 text-sm block mx-auto"
        @click="resetFilters"
      >
        フィルターをリセット
      </button>
    </div>

    <!-- 釣果カード一覧 -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <CatchCard v-for="report in reports" :key="report.id" :report="report" />
    </div>

    <!-- ページネーション -->
    <div v-if="lastPage > 1" class="flex justify-center gap-2 mt-8">
      <button
        v-for="page in lastPage"
        :key="page"
        :class="[
          'w-9 h-9 rounded-lg text-sm font-medium transition',
          page === currentPage
            ? 'bg-sea-600 text-white'
            : 'bg-white text-gray-600 hover:bg-sea-50 border',
        ]"
        @click="changePage(page)"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>
