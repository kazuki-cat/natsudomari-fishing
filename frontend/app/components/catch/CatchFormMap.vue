<script setup lang="ts">
// 釣果投稿フォーム内で使うインタラクティブなマップコンポーネント
// 地図をクリックするとピンが立ち、緯度・経度を親コンポーネント(pages/reports/new.vue)に通知する

// 地図ライブラリ
import "leaflet/dist/leaflet.css";
import L from "leaflet";

// defineEmits = 親コンポーネントへイベントを発火させる定義
// 'update:latitude' / 'update:longitude'を発火して親に座標を伝える
const emit = defineEmits<{
  (e: "update:latitude", val: number): void;
  (e: "update:longitude", val: number): void;
}>();

// Props(親から受け取る現在の座標値)
// v-model:latitude / v-model:longitudeで双方向バイディングに使う
const props = defineProps<{
  latitude: number | null;
  longitude: number | null;
}>();

// マップを表示するHTML要素への参照
const mapContainer = ref<HTMLElement | null>(null);

// 現在のピンのマーカーインスタンス(クリックのたびに差し替える)
let map: L.Map | null = null;
let marker: L.Marker | null = null;

// ピンのアイコン(📍)
const pinIcon = L.divIcon({
  html: "📍",
  className: "text-2xl",
  iconSize: [30, 30],
  iconAnchor: [15, 30], // アイコンの底辺中央座標の基点にする
  popupAnchor: [0, -30], // ポップアップの位置設定
});

onMounted(() => {
  if (!mapContainer.value) return;

  // マップを初期化(夏泊半島を中心に表示)
  map = L.map(mapContainer.value, {
    // パン(スクロール)できる範囲を制限する
    maxBounds: [
      [40.7, 140.64], // 左下(南西)の境界
      [41.2, 141.2], // 右上(北東)の境界
    ],
    // 境界での跳ね返り強度(0=滑らかに超える / 1=ぴったり止まる)
    maxBoundsViscosity: 1.0,
    // これ以上ズームアウトできない最小ズームレベル
    minZoom: 11,
  }).setView([41.002, 140.88361], 13); // 初期表示位置

  // タイルレイヤーを追加(地図の背景画像)
  // OpenStreetMapのサーバーから地図画(タイル)を取得して表示
  // attribution = 地図データの著作権表示(OSMの利用規約で必須)
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  // 既に座標が入力済みの場合(フォーム再編集時)はピンを表示
  if (props.latitude && props.longitude) {
    placeMarker(props.latitude, props.longitude);
  }

  // マップクリックイベント: クリックした座標にピンを立てる
  map.on("click", (e: L.LeafletMouseEvent) => {
    // e.latlng = クリックした地点の緯度・経度
    placeMarker(e.latlng.lat, e.latlng.lng);

    // 小数点以下6桁に丸めてから親コンポーネントに通知
    // DBのカラム定義がdecimal(10,7)で小数点以下7桁までの保存するため
    emit("update:latitude", Math.round(e.latlng.lat * 1000000) / 1000000);
    emit("update:longitude", Math.round(e.latlng.lng * 1000000) / 1000000);
  });
});

// ピンを指定座標に表示するヘルパー関数(既存ピンがあれば削除して新しいピンを追加)
const placeMarker = (lat: number, lng: number) => {
  if (!map) return;
  if (marker) marker.remove(); // 既存のピンを削除

  // 新しいピンを作成して追加
  marker = L.marker([lat, lng], { icon: pinIcon })
    .addTo(map)
    .bindPopup("釣れた場所")
    .openPopup();
};
</script>
<template>
  <div class="space-y-2">
    <!-- マップ表示エリア(クリックでピンを立てる) -->
    <div ref="mapContainer" class="w-full h-64 rounded-lg shadow z-0" />

    <!-- 操作説明 -->
    <p class="text-xs text-gray-400">
      地図をクリックして釣れた場所にピンを立ててください
    </p>

    <!-- ピンが立っている場所は座標を表示 -->
    <p v-if="latitude && longitude" class="text-xs text-sea-600 font-medium">
      📍 選択済み
    </p>
  </div>
</template>
