<script setup lang="ts">
// 釣果詳細ページ([id] = 動的ルート)

useHead({ title: "釣果詳細" });

import type { CatchReport, Comment } from "~/types/catchReport";

// 現在のルート情報(URLパラメータ・クエリ等)を取得
const route = useRoute();

const { fetchReport, deleteReport, fetchComments, createComment } =
  useCatchReports();
const { user, isLoggedIn } = useAuth();
const router = useRouter();

// 釣果データ(APIから取得後に格納)
const report = ref<CatchReport | null>(null);
// コメントリスト
const comments = ref<Comment[]>([]);
// 新しいコメントの入力値
const newComment = ref("");
// コメント送信中フラグ
const submitting = ref(false);

// 日付を日本語形式にフォーマットする関数
// 'YYYY-MM-DD' → 'YYYY年M月D日'
const formatDate = (dateStr: string) => {
  const d = new Date(dateStr);
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`;
};

// ページ表示時に釣果詳細とコメントを同時に取得(Promise.allで並列実行して速くする)
onMounted(async () => {
  const [reportData, commentData] = await Promise.all([
    fetchReport(Number(route.params.id)),
    fetchComments(Number(route.params.id)),
  ]);
  report.value = reportData.data;
  comments.value = commentData.data;
});

// 削除処理
const handleDelete = async () => {
  // confirm = ブラウザのダイアログで確認(キャンセルなら処理中止)
  if (!confirm("この投稿を削除しますか？")) return;
  await deleteReport(Number(route.params.id));
  router.push("/reports");
};

// コメント送信処理
const submitComment = async () => {
  if (!newComment.value.trim()) return; // 空白のみコメントはスキップ
  submitting.value = true;
  try {
    const data = await createComment(Number(route.params.id), newComment.value);
    // 送信したコメントをリストに追加(ページリロードなしで即反映)
    comments.value.unshift((data as any).data);
    newComment.value = "";
  } finally {
    submitting.value = false;
  }
};
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-8">
    <NuxtLink
      to="/reports"
      class="text-sea-600 hover:underline text-sm mb-4 block"
      >← タイムラインに戻る</NuxtLink
    >

    <!-- データ取得前はローディグ表示 -->
    <div v-if="!report" class="text-center py-12 text-gray-400">
      読み込み中...
    </div>

    <!-- データ取得後に表示 -->
    <template v-else>
      <!-- 釣果情報カード -->
      <div class="bg-white rounded-xl shadow p-6 mb-6">
        <!-- ヘッダー: タイトルと削除ボタン -->
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-sea-700">
              {{ report.fish_name }}
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">
              {{ formatDate(report.caught_at) }} / {{ report.location_name }}
            </p>
            <p class="text-sm text-gray-500 mt-0.5">
              投稿者：{{ report.user.name }}
            </p>
          </div>

          <!-- 削除ボタン: ログイン中かつ自分の投稿の場合のみ表示 -->
          <!-- user?.id = userがnullの場合のオプショナルチェーン -->
          <button
            v-if="isLoggedIn && user?.id === report.user.id"
            class="text-xs text-red-400 hover:text-red-600 transition"
            @click="handleDelete"
          >
            削除
          </button>
        </div>

        <!-- 釣果写真(あれば表示、なければダミー画像) -->
        <img
          v-if="report.image_path"
          :src="report.image_url ?? ''"
          :alt="report.fish_name"
          class="w-full rounded-lg mb-4"
        />

        <!-- 写真なしのダミー表示 -->
        <div
          v-else
          class="w-full h-48 rounded-lg mb-4 bg-gradient-to-br from-sea-100 to-sea-200 flex flex-col items-center justify-center"
        >
          <p class="text-sm text-sea-700">写真なし</p>
        </div>

        <!-- 釣果の詳細情報 -->
        <div class="space-y-2 text-sm">
          <p>
            <span class="font-medium text-gray-700">仕掛け：</span
            >{{ report.tackle }}
          </p>
          <p
            v-if="report.memo"
            class="text-gray-500 bg-gray-50 rounded p-3 mt-2"
          >
            {{ report.memo }}
          </p>
        </div>

        <!-- 釣れた場所のミニマップ(座標がある投稿のみ表示) -->
        <div v-if="report.latitude && report.longitude" class="mt-4">
          <p class="text-sm font-medium text-gray-700 mb-2">📍 釣れた場所</p>
          <!-- ClientOnly = Leafletはブラウザ専用のため必須 -->
          <ClientOnly>
            <CatchSpotMap
              :latitude="report.latitude"
              :longitude="report.longitude"
              :image-url="report.image_url"
            />
          </ClientOnly>
        </div>
      </div>

      <!-- コメントセクション -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="font-bold text-sea-700 mb-4">
          💬 コメント（{{ comments.length }}件）
        </h2>

        <!-- コメントリスト -->
        <div class="space-y-3 mb-5">
          <div
            v-for="comment in comments"
            :key="comment.id"
            class="bg-sea-50 rounded-lg px-4 py-3 text-sm"
          >
            <div class="flex items-center justify-between mb-1">
              <span class="font-medium text-sea-700">{{
                comment.user.name
              }}</span
              ><span class="text-xs text-gray-400">{{
                formatDate(comment.created_at)
              }}</span>
            </div>
            <p class="text-gray-700">{{ comment.body }}</p>
          </div>
          <p v-if="comments.length === 0" class="text-gray-400 text-sm">
            まだコメントはありません
          </p>
        </div>

        <!-- コメント入力フォーム(ログイン時のみ表示) -->
        <div v-if="isLoggedIn">
          <textarea
            v-model="newComment"
            rows="2"
            maxLength="300"
            placeholder="コメントを入力..."
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400 resize-none"
          />
          <p class="text-xs text-gray-400 mt-1 text-right">
            {{ newComment.length }} / 300文字以内
          </p>
          <!-- :disabled = 送信中またはコメントがからの時はボタン無効 -->
          <button
            :disabled="submitting || !newComment.trim()"
            class="bg-sea-600 hover:bg-sea-700 disabled:bg-gray-300 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
            @click="submitComment"
          >
            送信
          </button>
        </div>

        <!-- 未ログイン時はログインを促す -->
        <p v-else class="text-sm text-gray-400">
          <NuxtLink to="/auth/login" class="text-sea-600 underline"
            >ログイン</NuxtLink
          >してコメントする
        </p>
      </div>
    </template>
  </div>
</template>
