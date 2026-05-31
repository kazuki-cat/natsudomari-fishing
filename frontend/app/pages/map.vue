<script setup lang="ts">
// 釣り場マップページ(座標付きの釣果投稿を地図上に魚種ごとにアイコン表示)
// 詳細ページからフォーカス遷移した場合は特定の場所を拡大表示する

import type { CatchReport } from "~/types/catchReport";

const { fetchMapReports } = useCatchReports();
const route = useRoute();

// マップに表示する釣果リスト
const reports = ref<CatchReport[]>([]);

// データ取得フラグ
const loaded = ref(false);

// URLクエリパラメータからフォーカス情報を取得
// 例: /map?lat=41.002&lng=140.883&img=catch_images/xxx.jpg
const focusLat = ref(route.query.lat ? Number(route.query.lat) : null); // 緯度
const focusLng = ref(route.query.lng ? Number(route.query.lng) : null); // 経度
const focusImage = ref(route.query.img ? String(route.query.img) : null); // 画像パス

// フィルター状態
const filterFish = ref("");
const filterTackle = ref("");
const filterYear = ref("");
const filterMonth = ref("");

// 投稿データから年を動的に生成
const yearOptions = computed(() =>
  [...new Set(reports.value.map((r) => new Date(r.caught_at).getFullYear()))]
    .sort()
    .reverse(),
);

// 魚種リスト
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

// 仕掛けリスト
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

// フィルタリングされた釣果リスト
const filteredReports = computed(() => {
  return reports.value.filter((r) => {
    if (filterFish.value && r.fish_name !== filterFish.value) return false; // 魚種フィルター
    if (filterTackle.value && r.tackle !== filterTackle.value) return false; // 仕掛けフィルター
    // 年フィルター(caught_atから年を取り出して比較)
    if (filterYear.value) {
      const year = new Date(r.caught_at).getFullYear();
      if (year !== Number(filterYear.value)) return false;
    }
    // 月フィルター(caught_atから月を取り出して比較)
    if (filterMonth.value) {
      const month = new Date(r.caught_at).getMonth() + 1;
      if (month !== Number(filterMonth.value)) return false;
    }
    return true;
  });
});

// フィルター変更のたびにマップを再描画するためのキー
const mapKey = ref(0);
watch([filterFish, filterTackle, filterYear, filterMonth], () => {
  // フィルター変更時はフォーカスピンを消す(再描画時にポップアップが再表示されるのを防ぐ)
  focusLat.value = null;
  focusLng.value = null;
  focusImage.value = null;
  // キーを更新してCatchMapを再生成
  mapKey.value++;
});

onMounted(async () => {
  // APIから座標付き釣果を全件取得
  const data = await fetchMapReports();
  // latitudeとlongitudeが両方ある投稿のみフィルタリング
  reports.value = data.data.filter((r) => r.latitude && r.longitude);
  // データ取得完了後にマップをレンダリングする
  loaded.value = true;
});
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <!-- ヘッダー -->
    <h1 class="text-2xl font-bold text-sea-700 mb-1">釣り場マップ</h1>
    <p class="text-sm text-gray-400 mb-6">
      魚アイコンをクリックすると釣果の詳細を確認できます
    </p>

    <!-- フィルター -->
    <div class="bg-white rounded-xl shadow p-4 mb-6 space-y-3">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <!-- 魚種 -->
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
            <option v-for="fish in fishOptions" :key="fish" :value="fish">
              {{ fish }}
            </option>
          </select>
        </div>

        <!-- 仕掛け -->
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
            <option
              v-for="tackle in tackleOptions"
              :key="tackle"
              :value="tackle"
            >
              {{ tackle }}
            </option>
          </select>
        </div>

        <!-- 年 -->
        <div>
          <label for="year" class="block text-xs text-gray-500 mb-1">年</label>
          <select
            id="year"
            v-model="filterYear"
            class="w-full border rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sea-400"
          >
            <option value="">すべて</option>
            <option v-for="y in yearOptions" :key="y" :value="String(y)">
              {{ y }}年
            </option>
          </select>
        </div>

        <!-- 月 -->
        <div>
          <label for="month" class="block text-xs text-gray-500 mb-1">月</label>
          <select
            id="month"
            v-model="filterMonth"
            class="w-full border rounded-lg px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sea-400"
          >
            <option value="">すべて</option>
            <option v-for="m in 12" :key="m" :value="String(m)">
              {{ m }}月
            </option>
          </select>
        </div>
      </div>

      <!-- リセットボタン -->
      <div class="flex justify-end">
        <button
          v-if="filterFish || filterTackle || filterYear || filterMonth"
          class="text-xs bg-sea-600 hover:bg-sea-700 text-white font-medium px-4 py-1.5 rounded-lg transition"
          @click="
            filterFish = '';
            filterTackle = '';
            filterYear = '';
            filterMonth = '';
          "
        >
          リセット
        </button>
      </div>
    </div>

    <!-- ローディング-->
    <div v-if="!loaded" class="text-center py-12 text-gray-400">
      読み込み中...
    </div>

    <template v-else>
      <!-- ヒット件数(フィルター選択中かつ1件以上ある場合のみ表示) -->
      <p
        v-if="
          (filterFish || filterTackle || filterYear || filterMonth) &&
          filteredReports.length === 0
        "
        class="text-xs text-red-400 mb-4"
      >
        該当する釣果がありません
      </p>
      <p
        v-else-if="
          (filterFish || filterTackle || filterYear || filterMonth) &&
          filteredReports.length > 0
        "
        class="text-xs text-gray-400 mb-4"
      >
        {{ filteredReports.length }}件の釣果
      </p>

      <!-- データ取得後にマップを表示(ClientOnly = Leafletはブラウザ専用) -->
      <ClientOnly>
        <CatchMap
          :key="mapKey"
          :reports="filteredReports"
          :focus-lat="focusLat"
          :focus-lng="focusLng"
          :focus-image="focusImage"
        />
      </ClientOnly>

      <!-- マップ下部リンク -->
      <div class="mt-6">
        <NuxtLink
          to="/reports"
          class="text-sm text-sea-600 hover:text-sea-800 font-medium transition"
        >
          ← 釣果タイムラインで見る
        </NuxtLink>
      </div>
    </template>
  </div>
</template>
