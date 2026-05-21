<script setup lang="ts">
const { register } = useAuth();
const router = useRouter();

// フォームの入力値
const name = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");

const error = ref<string | null>(null);
const loading = ref(false);

// フォーム送信処理
const onSubmit = async () => {
  // パスワードと確認パスワードが一致しているかフロントでチェック
  if (password.value !== passwordConfirmation.value) {
    error.value = "パスワードが一致しません";
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    // POST /api/register
    await register(
      name.value,
      email.value,
      password.value,
      passwordConfirmation.value,
    );
    router.push("/");
  } catch (e: any) {
    // エラーメッセージをサーバーのレスポンスから取得、なければデフォルトメッセージ
    error.value = e?.data?.message || "登録に失敗しました";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <h1 class="text-2xl font-bold text-sea-800 text-center mb-8">新規登録</h1>
    <form
      class="bg-white rounded-xl shadow p-8 space-y-5"
      @submit.prevent="onSubmit"
    >
      <div
        v-if="error"
        class="bg-red-50 border border-red-200 text-red-600 rounded-lg p-3 text-sm"
      >
        {{ error }}
      </div>

      <!-- 名前 -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1"
          >名前</label
        >
        <input
          id="name"
          v-model="name"
          type="text"
          required
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400"
        />
      </div>

      <!-- メールアドレス -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1"
          >メールアドレス</label
        >
        <input
          v-model="email"
          id="email"
          type="email"
          required
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400"
        />
      </div>

      <!-- パスワード -->
      <div>
        <label
          for="password"
          class="block text-sm font-medium text-gray-700 mb-1"
          >パスワード（8文字以上）</label
        >
        <input
          id="password"
          v-model="password"
          type="password"
          required
          minlength="8"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400"
        />
      </div>

      <!-- パスワード確認 -->
      <div>
        <label
          for="passwordConfirmation"
          class="block text-sm font-medium text-gray-700 mb-1"
          >パスワード（確認）</label
        >
        <input
          id="passwordConfirmation"
          v-model="passwordConfirmation"
          type="password"
          required
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400"
        />
      </div>

      <!-- 登録ボタン -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-sea-600 hover:bg-sea-700 disabled:bg-gray-300 text-white font-bold py-2.5 rounded-lg transition"
      >
        {{ loading ? "登録中..." : "登録する" }}
      </button>

      <!-- ログインへのリンク -->
      <p class="text-center text-sm text-gray-500">
        既にアカウントをお持ちの方は
        <NuxtLink to="/auth/login" class="text-sea-600 underline"
          >ログイン</NuxtLink
        >
      </p>
    </form>
  </div>
</template>
