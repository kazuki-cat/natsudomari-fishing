<script setup lang="ts">
// 釣果詳細ページに表示する「釣れた場所」のミニマップ
// クリックすると釣り場マップページにその座標をフォーカスして遷移する
// 操作不可(ドラッグ・ズーム禁止)のプレビューマップ

import "leaflet/dist/leaflet.css";
import L from "leaflet";

// Props(pages/report/[id].vueから受け取る値)
const props = defineProps<{
  latitude: number; // 釣れた場所の緯度
  longitude: number; // 釣れた場所の経度
  imageUrl?: string | null; // 釣果写真の完全URL(マップ遷移時に渡す)
}>();

const router = useRouter();
const mapContainer = ref<HTMLElement | null>(null);

// 釣り場マップページへ遷移する関数(URLクエリパラメータで座標と画像パスを渡す)
const goToMap = () => {
  // クエリパラメータの組み立て
  const query: Record<string, string> = {
    lat: String(props.latitude),
    lng: String(props.longitude),
  };
  // 画像がある場合は完全URLも渡す(マップページでポップアップに表示するため)
  if (props.imageUrl) query.img = props.imageUrl;

  // 例: /map?lat=4.002&lng=140.883&img=catch_images/xxx.jpgへ遷移
  router.push({ path: "/map", query });
};

onMounted(() => {
  if (!mapContainer.value) return;

  // マップを初期化(ドラッグ・ズームを無効化してプレビュー専用にする)
  const map = L.map(mapContainer.value, {
    zoomControl: false, // ズームボタン非表示
    dragging: false, // ドラッグ無効
    scrollWheelZoom: false, // スクロールズーム無効
    doubleClickZoom: false, // ダブルクリックズーム無効
    touchZoom: false, // タッチズーム無効
    keyboard: false, // キーボード操作無効
  }).setView([props.latitude, props.longitude], 12);

  // 著作権表示を左下に移動
  map.attributionControl.setPosition("bottomleft");

  // タイルレイヤーを追加(地図の背景画像)
  // OpenStreetMapのサーバーから地図画(タイル)を取得して表示
  // attribution = 地図データの著作権表示(OSMの利用規約で必須)
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  // ピンアイコン(📍)
  const icon = L.divIcon({
    html: "📍",
    className: "text-2xl",
    iconSize: [30, 30],
    iconAnchor: [15, 30],
  });

  // 釣れた場所にピンを追加(ポップアップなし・シンプルな表示のみ)
  L.marker([props.latitude, props.longitude], { icon }).addTo(map);
});
</script>

<template>
  <!-- 相対配置のコンテナ(ボタンをマップ上に重ねるため) -->
  <div class="relative h-48 rounded-lg shadow overflow-hidden">
    <!-- マップ表示エリア -->
    <div ref="mapContainer" class="w-full h-full z-0" />

    <!-- オーバーレイ: マップ全体をクリック可能にしボタンを右下に配置 -->
    <div
      class="absolute inset-0 z-10 cursor-pointer flex items-end justify-end p-2"
      @click="goToMap"
    >
      <span
        class="bg-white text-sea-600 text-xs font-medium px-2 py-1 rounded shadow hover:bg-sea-50 transition"
        >釣り場マップで見る →</span
      >
    </div>
  </div>
</template>
