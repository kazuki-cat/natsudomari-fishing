<script setup lang="ts">
// 釣り場マップページで使うメインのマップコンポーネント
// 釣果投稿のピンを全件表示し、クリックで詳細ページへ遷移する

import type { CatchReport } from "~/types/catchReport";
import "leaflet/dist/leaflet.css";
import L from "leaflet";

// Props(pages/map.vueから受け取る値)
const props = defineProps<{
  reports: CatchReport[]; // 表示する釣果リスト
  focusLat?: number | null; // 特定の場所をフォーカスするときの緯度(任意)
  focusLng?: number | null; // 特定の場所をフォーカスするときの経度(任意)
  focusImage?: string | null; // フォーカスピンのポップアップに表示する画像パス(任意)
}>();

const router = useRouter();
const mapContainer = ref<HTMLElement | null>(null);

// コンポーネントがDOMにマウントされた後に実行
onMounted(() => {
  if (!mapContainer.value) return;

  // マップの初期化（フォーカス座標があればその場所を中心に、なければ夏泊半島(大島)をデフォルト
  const centerLat = props.focusLat ?? 41.002;
  const centerLng = props.focusLng ?? 140.88361;
  const zoom = props.focusLat ? 15 : 13; // フォーカス時は拡大表示

  // Leafletのマップインスタンスを作成
  const map = L.map(mapContainer.value).setView([centerLat, centerLng], zoom);

  // タイルレイヤーを追加(地図の背景画像)
  // OpenStreetMapのサーバーから地図画(タイル)を取得して表示
  // attribution = 地図データの著作権表示(OSMの利用規約で必須)
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  // 魚種と画像ファイル名のマッピング(アイコン定義)
  const fishIcons: Record<string, string> = {
    メバル: "/images/fish/mebaru.png",
    アイナメ: "/images/fish/ainame.png",
    ソイ: "/images/fish/soi.png",
    ヒラメ: "/images/fish/hirame.png",
    カレイ: "/images/fish/karei.png",
    マダイ: "/images/fish/madai.png",
    アジ: "/images/fish/aji.png",
    サバ: "/images/fish/saba.png",
    イワシ: "/images/fish/iwashi.png",
    イナダ: "/images/fish/inada.png",
    ワラサ: "/images/fish/warasa.png",
    ブリ: "/images/fish/buri.png",
    シイラ: "/images/fish/shiira.png",
    サワラ: "/images/fish/sawara.png",
    マグロ: "/images/fish/maguro.png",
    シーバス: "/images/fish/shiibasu.png",
    ヤリイカ: "/images/fish/yariika.png",
    クロダイ: "/images/fish/kurodai.png",
    マゴチ: "/images/fish/magochi.png",
    タコ: "/images/fish/tako.png",
    default: "/images/fish/default.png",
  };

  // マーカーを格納する配列(fitBoundsで使用)
  // fitBounds = 複数のアイコンが全部画面内に収まるようにズームと中心位置を自動調整するLeafletのメソッド
  const markers: L.Marker[] = [];

  // 釣果ごとにアイコンを追加(ポップアップ設定)
  props.reports.forEach((report) => {
    // 座標がない投稿はスキップ(map.vue側でもチェックしてるが二重でチェック)
    if (!report.latitude || !report.longitude) return;

    // ポップアップに表示する画像HTML(画像あり → 実際の画像、 なし → '写真なし'とテキスト表示)
    const imageHtml = report.image_path
      ? `<img src="${report.image_url}" style="width:150px;height:90px;object-fit:cover;border-radius:5px;display:block;margin-bottom:5px">`
      : `<div style="width:150px;height:90px;border-radius:5px;background:linear-gradient(135deg,#e0f2fe,#bae6fd);display:flex;align-items:center;justify-content:center;margin-bottom:5px;font-size:14px;color:#0ea5e9;">写真なし</div>`;

    // 魚種ごとのアイコン画像
    const icon = L.icon({
      iconUrl: fishIcons[report.fish_name] ?? "/images/fish/default.png",
      iconSize: [30, 30],
      iconAnchor: [15, 15],
    });

    // マーカーを作成してマップに追加
    const marker = L.marker([report.latitude, report.longitude], { icon })
      .bindPopup(
        `
      <div style="min-width:150px;">
        ${imageHtml}
        <strong>${report.fish_name}</strong><br>
        <span style="color:#666; font-size:12px;">${report.location_name}</span><br>
        <span style="color:#666; font-size:12px;">${report.caught_at}</span><br>
        <span class="detail-link" style="color:#0ea5e9; font-size:12px; font-weight:bold;">タップで詳細を見る →</span>
      </div>
      `,
        { maxWidth: 180 },
      )
      .addTo(map);

    // ポップアップが開いたときにクリックイベントを設定
    // ポップアップとそのDOM要素を取得(取得できなければ処理中止)
    marker.on("popupopen", () => {
      const popup = marker.getPopup();
      if (!popup) return;

      const el = popup.getElement();
      if (!el) return;

      // 「タップで詳細を見る →」をクリックで釣果詳細ページへ遷移
      const link = el.querySelector(".detail-link") as HTMLElement | null;
      if (!link) return;
      link.style.cursor = "pointer";

      // once:true = ポップアップを開くたびにリスナーが積み重なるのを防ぐ
      // ※これがないとクリック時に複数回ページ遷移してしまうバグが起きる
      link.addEventListener(
        "click",
        () => {
          router.push(`/reports/${report.id}`);
        },
        { once: true },
      );
    });

    // 作成したマーカーを配列に追加
    markers.push(marker);
  });

  // 全ピンが画面内に収まるようにズーム・中心を自動調整
  // フォーカス指定がなく、ピンが1つ以上ある場合のみ実行
  if (!props.focusLat && markers.length > 0) {
    // featureGroup = 複数マーカーをグループ化するLeafletのクラス
    const group = L.featureGroup(markers);
    // getBounds = マーカーグループ全体を囲む矩形（バウンディングボックス）を取得するメソッド
    // グループ全体が画面内に収まるようにズームと位置を調整(pad(0.3)で少し余白を持たせる)
    map.fitBounds(group.getBounds().pad(0.3));
  }

  // フォーカス指定がある場合は特別なピンを追加
  // 詳細ページの「釣り場マップで見る」から遷移した場合
  if (props.focusLat && props.focusLng) {
    const focusIcon = L.divIcon({
      html: "📍",
      className: "text-3xl",
      iconSize: [36, 36],
      iconAnchor: [18, 36],
    });

    // フォーカスピンのポップアップ: 画像がある場合は写真を表示
    const popupContent = props.focusImage
      ? `<div style="text-align:center;">
          <img src="${props.focusImage}" style="width:150px;height:90px;object-fit:cover;border-radius:5px;display:block;margin-bottom:4px">
          <strong>📍 ここで釣れました</strong>
        </div>`
      : "<strong>📍 ここで釣れました</strong>";

    L.marker([props.focusLat, props.focusLng], { icon: focusIcon })
      .bindPopup(popupContent, { maxWidth: 200 })
      .addTo(map)
      .openPopup(); // マップ表示と同時にポップアップを開く
  }
});
</script>
<template>
  <!-- マップを表示するコンテナ -->
  <div ref="mapContainer" class="w-full h-96 rounded-xl shadow z-0" />
</template>
