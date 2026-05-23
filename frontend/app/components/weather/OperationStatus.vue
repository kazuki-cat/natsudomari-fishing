<script setup lang="ts">
// 営業可否を大きく表示するコンポーネント
// 風速・波高に応じて色・メッセージが変わる

import type { OperationStatus } from "~/types/weather";

// index.vueから受け取る
const props = defineProps<{
  status: OperationStatus; // 'high' | 'medium' | 'low'
  windSpeed: number | null; // 現在の風速(m/s)
  waveHeightValue: number | null; // 現在の波高(m)
}>();

// 風速・波高を「風速 Xm/s 波高 Ym」形式にまとめるヘルパー
const seaInfo = computed(() => {
  const wind =
    props.windSpeed !== null ? `風速 ${props.windSpeed}m/s` : "風速 -";
  const wave =
    props.waveHeightValue !== null
      ? `波高 ${props.waveHeightValue}m`
      : "波高 -";
  return `${wind} / ${wave}`;
});

// statusに応じた表示設定を算出プロパティで定義
// props.statusが変わると自動で再計算
const config = computed(
  () =>
    ({
      high: {
        label: "営業の可能性: 高",
        sub: `${seaInfo.value} - 穏やかな海況です`,
        bg: "bg-green-50 border-green-300",
        text: "text-green-700",
        icon: "✅",
      },
      medium: {
        label: "営業の可能性: 中",
        sub: `${seaInfo.value} - 事前に確認をおすすめします`,
        bg: "bg-yellow-50 border-yellow-300",
        text: "text-yellow-700",
        icon: "⚠️",
      },
      low: {
        label: "営業の可能性: 低",
        sub: `${seaInfo.value} - 出航困難な可能性があります`,
        bg: "bg-red-50 border-red-300",
        text: "text-red-700",
        icon: "❌",
      },
      // props.statusをキーにして対応する設定を取得
    })[props.status],
);
</script>

<template>
  <!-- :class で動的にクラスを適用(statusによって色が変わる) -->
  <div :class="['border-2 rounded-xl p-5 text-center', config.bg]">
    <p class="text-4xl mb-2">{{ config.icon }}</p>
    <p :class="['text-xl font-bold', config.text]">{{ config.label }}</p>
    <p class="text-sm text-gray-600 mt-1">{{ config.sub }}</p>
    <p class="text-xs text-gray-500 mt-3">
      ※波高1m超 または 風速6m/s以上：低 / 波高0.5m超 または 風速3m/s以上：中
      それ以外：高<br />
      実際の営業状況は店舗(サンライズ・フクシ)へご確認ください TEL: 017-759-2031
    </p>
  </div>
</template>
