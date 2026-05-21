<script setup lang="ts">
const { login } = useAuth();
const router = useRouter();

// フォームの入力値
const email = ref("");
const password = ref("");

// エラーメッセージ(ログイン失敗時に表示)
const error = ref<string | null>(null);

// 送信中フラグ
const loading = ref(false);

// フォーム送信処理
const onSubmit = async () => {
  loading.value = true;
  error.value = null;
  try {
    // POST /api/login → 成功するとuserとtokenが返る
    await login(email.value, password.value);
    router.push("/");
  } catch {
    error.value = "メールアドレスまたはパスワードが正しくありません";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <h1 class="text-2xl font-bold text-sea-800 text-center mb-8">ログイン</h1>

    <form
      class="bg-white rounded-xl shadow p-8 space-y-5"
      @submit.prevent="onSubmit"
    >
      <!-- エラーメッセージ -->
      <div
        v-if="error"
        class="bg-red-50 border border-red-200 text-red-600 rounded-lg p-3 text-sm"
      >
        {{ error }}
      </div>

      <!-- メールアドレス -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1"
          >メールアドレス</label
        >
        <input
          id="email"
          v-model="email"
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
          >パスワード</label
        >
        <input
          id="password"
          v-model="password"
          type="password"
          required
          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sea-400"
        />
      </div>

      <!-- ログインボタン -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-sea-600 hover:bg-sea-700 disabled:bg-gray-300 text-white font-bold py-2.5 rounded-lg transition"
      >
        {{ loading ? "ログイン中..." : "ログイン" }}
      </button>

      <!-- 新規登録へのリンク-->
      <p class="text-center text-sm text-gray-500">
        アカウントをお持ちでない方は
        <NuxtLink to="/auth/register" class="text-sea-600 underline"
          >新規登録</NuxtLink
        >
      </p>
    </form>
  </div>
</template>
