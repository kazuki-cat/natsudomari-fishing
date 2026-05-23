<script setup lang="ts">
// 夏泊半島の現在の天気情報をカード形式で表示するコンポーネント
// 天気・気温・風速・風向き・波の高さを表示する
import type { WeatherData } from "~/types/weather";

// index.vueから受け取る
defineProps<{ weather: WeatherData }>();

// 風速を人間が読みやすいラベルに変換
const windLabel = (speed: number | null) => {
  if (speed === null) return "-";
  if (speed < 3) return "弱風";
  if (speed < 6) return "中程度";
  if (speed < 10) return "強風";
  return "非常に強い";
};
</script>

<template>
  <div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-lg font-bold text-sea-700 mb-4">夏泊半島 現在の天気</h2>

    <!-- grid: スマホは2列、mb以上は4列で並べる -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
      <!-- 天気 -->
      <div class="bg-sea-50 rounded-lg p-3">
        <p class="text-xs text-gray-500 mb-1">天気</p>
        <p class="text-xl font-bold text-sea-700">
          {{ weather.weatherDescription }}
        </p>
      </div>

      <!-- 気温(nullの場合'-'を表示) -->
      <div class="bg-sea-50 rounded-lg p-3">
        <p class="text-xs text-gray-500 mb-1">気温</p>
        <p class="text-2xl font-bold text-sea-700">
          {{ weather.temperature !== null ? weather.temperature + "℃" : "-" }}
        </p>
      </div>

      <!-- 風速(nullの場合'-'を表示)、windLabelで風の強さラベルも表示-->
      <div class="bg-sea-50 rounded-lg p-3">
        <p class="text-xs text-gray-500 mb-1">風速</p>
        <p class="text-2xl font-bold text-sea-700">
          {{ weather.windSpeed !== null ? weather.windSpeed + "m/s" : "-" }}
        </p>
        <p class="text-xs text-gray-500">{{ windLabel(weather.windSpeed) }}</p>
      </div>

      <!-- 風向き -->
      <div class="bg-sea-50 rounded-lg p-3">
        <p class="text-xs text-gray-500 mb-1">風向き</p>
        <p class="text-2xl font-bold text-sea-700">
          {{ weather.windDirection }}
        </p>
      </div>

      <!-- 波の高さ(nullの場合'-'を表示、col-span2 md:col-span-4で全幅に広げる)-->
      <div class="bg-sea-50 rounded-lg p-3 col-span-2 md:col-span-4">
        <p class="text-xs text-gray-500 mb-1">波の高さ</p>
        <p class="text-2xl font-bold text-sea-700">
          {{
            weather.waveHeightValue !== null
              ? weather.waveHeightValue + "m"
              : "-"
          }}
        </p>
      </div>
    </div>
  </div>
</template>
