// 天気データの取得・状態管理・営業判定ロジックをまとめたComposable
import type { WeatherData, OperationStatus } from "~/types/weather";

export const useWeather = () => {
  // 天気データ(APIから取得したデータを格納)
  const weather = ref<WeatherData | null>(null);

  // 読み込み中フラグ(trueでローディング表示)
  const loading = ref(false);

  // エラーメッセージ(API失敗時に格納)
  const error = ref<string | null>(null);

  // 営業可否判定(風速、波高の値によって'high' / 'medium' / 'low' を返す)
  const operationStatus = computed<OperationStatus | null>(() => {
    if (!weather.value) return null;

    const wind = weather.value.windSpeed;
    const wave = weather.value.waveHeightValue;

    // どちらかがnullの場合は判定不可
    if (wind === null || wave === null) return null;
    // 波高1m以上または風速6m/s以上 → 営業の可能性:低
    if (wave > 1 || wind >= 6) return "low";
    // 波高0.5m以上または風速3m/s以上 → 営業の可能性:中
    if (wave > 0.5 || wind >= 3) return "medium";
    // 波高0.5m以下かつ風速3m/s以下 → 営業の可能性:高
    return "high";
  });

  // 天気データ取得
  const fetchWeather = async () => {
    loading.value = true;
    error.value = null;

    try {
      // GET /api/weather(LaravelがWeatherServiceで気象庁APIから取得したデータを返す)
      const data = await $fetch<WeatherData>("/api/weather");
      weather.value = data;
    } catch {
      error.value = "天気情報の取得に失敗しました";
    } finally {
      loading.value = false;
    }
  };

  return { weather, loading, error, operationStatus, fetchWeather };
};
