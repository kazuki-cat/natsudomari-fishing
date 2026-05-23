<script setup lang="ts">
// トップページ:天気・営業予報を表示する
// ログイン不要で誰でも閲覧できる

// useWeather.tsから必要な値と関数を取得
const { weather, loading, error, operationStatus, fetchWeather } = useWeather();

// コンポーネントがマウントされたとき(ページ表示時)に実行
// APIを叩いて天気データを取得する
onMounted(fetchWeather);
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">
    <!-- ページタイトル -->
    <div class="text-center">
      <h1 class="text-2xl font-bold text-sea-800">
        夏泊ボートレンタル 営業予報
      </h1>
      <p class="text-sm text-gray-500 mt-1">
        夏泊半島の天気・風速をもとに営業の可能性をお知らせするサービスです
      </p>
    </div>

    <!-- 読み込み中の表示 -->
    <div v-if="loading" class="text-center py-12 text-gray-400">
      天気情報を取得中...
    </div>

    <!-- エラー時の表示 -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 rounded-xl p-4 text-center text-red-600"
    >
      {{ error }}
      <button
        class="block mx-auto mt-2 text-sm underline"
        @click="fetchWeather"
      >
        再試行
      </button>
    </div>
    <!-- データ取得成功時の表示 -->
    <template v-else-if="weather">
      <!-- 営業の可否の大きな表示(OperationStatus コンポーネント) -->
      <OperationStatus
        v-if="operationStatus"
        :status="operationStatus"
        :wind-speed="weather.windSpeed"
        :wave-height-value="weather.waveHeightValue"
      />

      <!-- 現在の天気情報カード(WeatherCard コンポーネント) -->
      <WeatherCard :weather="weather" />

      <!-- 週間予報テーブル -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-bold text-sea-700 mb-4">1週間の天気予報</h2>

        <!-- overflow-x-auto = 横スクロール対応(スマホでテーブルが見切れないように) -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-center">
            <thead>
              <tr class="text-xs text-gray-400 border-b">
                <th class="pb-2 text-left font-medium">日付</th>
                <th class="pb-2 font-medium">天気</th>
                <th class="pb-2 font-medium">最高</th>
                <th class="pb-2 font-medium">最低</th>
                <th class="pb-2 font-medium">降水確率</th>
                <th class="pb-2 font-medium">風速(推定)</th>
              </tr>
            </thead>
            <tbody>
              <!-- v-for で6日分の予報を繰り返し表示 -->
              <!-- :key は各行のユニークなキー(Vueのリスト最適化に必要) -->
              <tr
                v-for="day in weather.forecast.slice(1)"
                :key="day.date"
                class="border-b last:border-0 hover:bg-sea-50 transition"
              >
                <td class="py-2.5 text-left font-medium text-gray-700">
                  {{ day.date }}
                </td>
                <td class="py-2.5 text-gray-600">
                  {{ day.weatherDescription }}
                </td>
                <!-- nullの場合は '-' を表示 -->
                <td class="py-2.5 text-red-500 font-medium">
                  {{
                    day.temperatureMax !== null ? day.temperatureMax + "℃" : "-"
                  }}
                </td>
                <td class="py-2.5 text-blue-500 font-medium">
                  {{
                    day.temperatureMin !== null ? day.temperatureMin + "℃" : "-"
                  }}
                </td>
                <td class="py-2.5 text-sea-600">
                  {{ day.pop !== null ? day.pop + "%" : "-" }}
                </td>
                <td class="py-2.5 text-gray-500">
                  {{ day.windSpeed !== null ? day.windSpeed + "m/s" : "-" }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="text-xs text-gray-400 mt-3 space-y-1">
        <p>※ 実際の海況は出航当日に必ず確認ください。</p>
        <p>※ 2日以降の風速は気象庁から提供されないため「-」と表示されます。</p>
      </div>
    </template>
  </div>
</template>
