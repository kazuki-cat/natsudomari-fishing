// 釣果投稿のCRUD操作をまとめたComposable
import type {
  CatchReport,
  CatchReportForm,
  Comment,
} from "~/types/catchReport";

export const useCatchReports = () => {
  // タイムラインの釣果リスト
  const reports = ref<CatchReport[]>([]);

  // 読み込み中フラグ
  const loading = ref(false);

  // 現在のページ番号
  const currentPage = ref(1);

  // 最後のページ番号(ページネーションの総ページ数)
  const lastPage = ref(1);

  // useAuthから認証付きfetch関数を取得
  const { apiFetch } = useAuth();

  // フィルター・ソートのパラメータ型
  interface FetchParams {
    page?: number;
    sort?: "newest" | "oldest";
    fish_name?: string;
    location_name?: string;
    tackle?: string;
  }

  // 釣果一覧取得(タイムライン用)
  const fetchReports = async (params: FetchParams = {}) => {
    loading.value = true;
    try {
      // クエリパラメータを組み立てる(空文字は除外)
      const query = new URLSearchParams();
      query.set("page", String(params.page ?? 1));
      if (params.sort) query.set("sort", params.sort);
      if (params.fish_name) query.set("fish_name", params.fish_name);
      if (params.location_name)
        query.set("location_name", params.location_name);
      if (params.tackle) query.set("tackle", params.tackle);

      // 例: GET /api/reports?page=1&sort=newest&fish_name=アジ
      const data = await $fetch<{ data: CatchReport[]; last_page: number }>(
        `/api/reports?${query}`,
      );
      reports.value = data.data;
      lastPage.value = data.last_page;
      currentPage.value = params.page ?? 1;
    } finally {
      loading.value = false;
    }
  };

  // 釣果詳細情報取得
  const fetchReport = async (id: number) => {
    // 例: GET /api/reports/1
    return await $fetch<{ data: CatchReport }>(`/api/reports/${id}`);
  };

  // 釣果投稿
  const createReport = async (form: CatchReportForm) => {
    // 画像ファイルを含むためFormDataを使う(multipart/form-data形式データを送るためのブラウザAPI)
    const formData = new FormData();

    // フォームの必須である各フィールドをFormDataに追加
    formData.append("caught_at", form.caught_at);
    formData.append("fish_name", form.fish_name);
    formData.append("tackle", form.tackle);
    formData.append("location_name", form.location_name);

    // 座標は0が有効なため !== null で厳密にチェック
    if (form.latitude !== null)
      formData.append("latitude", String(form.latitude));
    if (form.longitude !== null)
      formData.append("longitude", String(form.longitude));

    // メモと画像は任意項目なので空文字やnullチェック
    if (form.memo) formData.append("memo", form.memo);
    if (form.image) formData.append("image", form.image); // FormDataだからFileオブジェクトをそのまま送れる

    // 認証付きでPOSTリクエスト(apiFetchがAuthorizationヘッダーを自動で付与)
    // 例: POST /api/reports(multipart/form-data)
    return await apiFetch("/api/reports", { method: "POST", body: formData });
  };

  // 釣果削除
  const deleteReport = async (id: number) => {
    // 認証付きでPOSTリクエスト(apiFetchがAuthorizationヘッダーを自動で付与)
    // 例: DELETE /api/reports/1
    await apiFetch(`/api/reports/${id}`, { method: "DELETE" });
  };

  // マップ表示用(座標付き釣果の全権取得)
  const fetchMapReports = async () => {
    // GET /api/reports/map
    return await $fetch<{ data: CatchReport[] }>("/api/reports/map");
  };

  // コメント一覧取得
  const fetchComments = async (reportId: number) => {
    // 例: GET /api/reports/1/comments
    return await $fetch<{ data: Comment[] }>(
      `/api/reports/${reportId}/comments`,
    );
  };

  // コメント投稿
  const createComment = async (reportId: number, body: string) => {
    // 例: POST /api/reports/1/comments
    return await apiFetch(`/api/reports/${reportId}/comments`, {
      method: "POST",
      body: { body },
    });
  };

  // 外部から使える値と関数を返す
  return {
    reports,
    loading,
    currentPage,
    lastPage,
    fetchReports,
    fetchReport,
    fetchMapReports,
    createReport,
    deleteReport,
    fetchComments,
    createComment,
  };
};
