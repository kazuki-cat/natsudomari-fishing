<script setup lang="ts">
// 釣果投稿フォームページ(ログインしているユーザーのみアクセス可)
// 投稿完了後は /reports(タイムライン)にリダイレクトする

useHead({ title: "釣果を投稿" });

import type { CatchReportForm } from "~/types/catchReport";

// middleware/auth.tsにてログイン済みユーザーか確認
definePageMeta({ middleware: "auth" });

const { createReport } = useCatchReports();
const router = useRouter();
const submitting = ref(false);
const errorMessages = ref<string[]>([]);

// 魚種の選択肢(夏泊半島周辺で良く釣れる魚)
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

// 仕掛けの選択肢
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

// 釣り場名の選択肢(夏泊半島周辺)
const locationOptions = [
  "夏泊半島・大島",
  "小湊漁港",
  "清水川漁港",
  "東滝漁港",
  "白浜漁港",
  "稲生漁港",
  "浦田漁港",
  "茂浦漁港",
  "浪打漁港",
  "土屋漁港",
  "その他",
];

// フォームの初期値(オブジェクト全体をリアクティブにする)
const form = reactive<CatchReportForm>({
  // 今日の日付をデフォルト値にする
  // toISOString()はUTC基準のためJST(+9H)だとズレた為→toLocaleDateString('sv')でローカル日付取得
  caught_at: new Date().toLocaleDateString("sv") ?? "",
  fish_name: "",
  tackle: "",
  latitude: null,
  longitude: null,
  location_name: "",
  memo: "",
  image: null,
});

// 画像ファイル選択時の処理
// input[type=file]のchangeイベントから選択されたFileオブジェクトをformにセット
const onImageChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (file) form.image = file;
};

// フォーム送信処理
// バリデーション → APIで投稿 → タイムラインへリダイレクト
const onSubmit = async () => {
  const errors: string[] = [];

  // 各必須項目のバリデーション
  if (form.caught_at > new Date().toLocaleDateString("sv")) {
    errors.push("釣れた日は今日以前の日付を選択してください");
  }
  if (!form.fish_name) errors.push("魚種を選択してください");
  if (!form.tackle) errors.push("仕掛けを選択してください");
  if (!form.location_name) errors.push("釣り場名を選択してください");

  // エラーがあれば表示して送信を中止
  if (errors.length > 0) {
    errorMessages.value = errors;
    return;
  }

  submitting.value = true;
  errorMessages.value = []; // 送信前にエラーをリセット
  try {
    await createReport(form);
    router.push("/reports"); // 投稿成功後はタイムラインへ遷移
  } catch {
    errorMessages.value = ["投稿に失敗しました。もう一度お試しください。"];
  } finally {
    submitting.value = false;
  }
};
</script>
<template>
  <div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-sea-800 mb-6">釣果を投稿</h1>

    <form
      class="bg-white rounded-xl shadow p-6 space-y-5"
      @submit.prevent="onSubmit"
    >
      <!-- 釣れた日(今日より未来は選択不可) -->
      <div>
        <label
          for="caught_at"
          class="block text-sm font-medium text-gray-700 mb-1"
          >釣れた日 <span class="text-red-500">*</span></label
        >
        <input
          id="caught_at"
          v-model="form.caught_at"
          type="date"
          :max="new Date().toLocaleDateString('sv')"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400"
        />
        <p class="text-xs text-gray-400 mt-1 md:hidden">
          ※今日以前の日付を選択してください
        </p>
      </div>

      <!-- 魚種(選択のみ) -->
      <div>
        <label
          for="fish_name"
          class="block text-sm font-medium text-gray-700 mb-1"
          >魚種 <span class="text-red-500">*</span></label
        >
        <select
          id="fish_name"
          v-model="form.fish_name"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400 bg-white"
        >
          <option value="" disabled>選択してください</option>
          <option v-for="opt in fishOptions" :key="opt" :value="opt">
            {{ opt }}
          </option>
        </select>
      </div>

      <!-- 仕掛け(選択のみ) -->
      <div>
        <label for="tackle" class="block text-sm font-medium text-gray-700 mb-1"
          >仕掛け <span class="text-red-500">*</span></label
        >
        <select
          id="tackle"
          v-model="form.tackle"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400 bg-white"
        >
          <option value="" disabled>選択してください</option>
          <option v-for="opt in tackleOptions" :key="opt" :value="opt">
            {{ opt }}
          </option>
        </select>
      </div>

      <!-- 釣り場名(選択のみ) -->
      <div>
        <label
          for="location_name"
          class="block text-sm font-medium text-gray-700 mb-1"
          >釣り場名 <span class="text-red-500">*</span></label
        >
        <img
          src="/images/fishing-map.png"
          alt="釣り場参考マップ"
          class="w-full rounded-lg mb-2"
        />
        <select
          id="location_name"
          v-model="form.location_name"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400 bg-white"
        >
          <option value="" disabled>選択してください</option>
          <option v-for="opt in locationOptions" :key="opt" :value="opt">
            {{ opt }}
          </option>
        </select>
      </div>

      <!-- 詳しい場所マップ(任意) -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >詳しい場所 (任意)</label
        >
        <ClientOnly>
          <CatchFormMap
            :latitude="form.latitude"
            :longitude="form.longitude"
            @update:latitude="form.latitude = $event"
            @update:longitude="form.longitude = $event"
          />
          <template #fallback>
            <div
              class="h-64 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-sm"
            >
              地図を読み込み中...
            </div>
          </template>
        </ClientOnly>
      </div>

      <!-- 写真(任意) -->
      <div>
        <label for="image" class="block text-sm font-medium text-gray-700 mb-1"
          >写真 (任意)</label
        >
        <input
          id="image"
          type="file"
          accept="image/*"
          class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-sea-100 file:text-sea-700 file:text-sm"
          @change="onImageChange"
        />
      </div>

      <!-- メモ(任意) -->
      <div>
        <label for="memo" class="block text-sm font-medium text-gray-700 mb-1"
          >メモ (任意)</label
        >
        <textarea
          id="memo"
          v-model="form.memo"
          rows="3"
          maxlength="1000"
          placeholder="釣れた時間帯、使用したルアー、詳しい状況などを自由にどうぞ"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400 resize-none"
        />
        <p class="text-xs text-gray-400 mt-1 text-right">
          {{ form.memo.length }} / 1000文字以内
        </p>
      </div>

      <!-- エラーメッセージ -->
      <div
        v-if="errorMessages.length > 0"
        class="bg-red-50 border border-red-200 text-red-600 rounded-lg p-3 text-sm"
      >
        <ul class="space-y-1">
          <li v-for="msg in errorMessages" :key="msg" class="text-red-600">
            ・{{ msg }}
          </li>
        </ul>
      </div>

      <button
        type="submit"
        :disabled="submitting"
        class="w-full bg-sea-600 hover:bg-sea-700 disabled:bg-gray-300 text-white font-bold py-2.5 rounded-lg transition"
      >
        {{ submitting ? "投稿中..." : "投稿する" }}
      </button>
    </form>
  </div>
</template>
