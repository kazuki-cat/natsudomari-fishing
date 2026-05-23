// 天気データのTypescript型定義
export interface WeatherData {
  temperature: number | null; // 現在の気温
  windSpeed: number | null; // 現在の風速
  windDirection: string; // 現在の風向き
  weatherDescription: string; //天気の説明
  waveHeight: string; //波高テキスト
  waveHeightValue: number | null; // 波高数値
  forecast: ForecastDay[]; // 週間予報(7日分)
}

// 週間予報の1日分の型
export interface ForecastDay {
  date: string; // 表示用日付
  weatherDescription: string; // 天気の説明
  windSpeed: number | null; // 風速推定値
  pop: number | null; // 降水確率
  temperatureMax: number | null; // 最高気温
  temperatureMin: number | null; // 最低気温
}

// 営業可否の型
// useWeatherのoperationStatusとOperationStatusコンポーネントのpropsで使う
export type OperationStatus = "high" | "medium" | "low";
