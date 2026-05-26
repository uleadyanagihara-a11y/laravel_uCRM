# uCRM 開発ログ — git 履歴ベース（2026-05-13 以降）

> **情報源:** `git log` / `git show` / `git diff HEAD` による現在のリポジトリ状態の分析
> **期間:** 2026-05-13 〜 2026-05-26
> **対象ブランチ:** `master` / `sec03_items`

---

## 目次

1. [フェーズ別実装サマリ](#フェーズ別実装サマリ)
2. [フェーズ 1 — Laravel プロジェクト初期構築（2026-05-18）](#フェーズ-1--laravel-プロジェクト初期構築2026-05-18)
3. [フェーズ 2 — Inertia.js + Vue 3 + 認証基盤（2026-05-25）](#フェーズ-2--inertiajs--vue-3--認証基盤2026-05-25)
4. [フェーズ 3 — 商品管理機能・shadcn-vue 導入（sec03_items ブランチ、作業中）](#フェーズ-3--商品管理機能shadcn-vue-導入sec03_items-ブランチ作業中)
5. [解決済み技術トラブル](#解決済み技術トラブル)
6. [現状の未完了箇所・注意点](#現状の未完了箇所注意点)

---

## フェーズ別実装サマリ

| フェーズ | コミット | 日付 | 内容 |
|--------|--------|------|------|
| 1 | `8c67185` | 2026-05-18 | Laravel 9 プロジェクト初期作成 |
| 2 | `49ba9ab` | 2026-05-25 | Inertia.js + Vue 3 + Vite + 認証 |
| 3 | 未コミット | 〜2026-05-26 | shadcn-vue 導入 + 商品管理 CRUD 骨格 |

---

## フェーズ 1 — Laravel プロジェクト初期構築（2026-05-18）

### 実装内容

Laravel 9 の素の状態でプロジェクトをリポジトリに登録。

### 追加・変更ファイル

| ファイル / ディレクトリ | 内容 |
|----------------------|------|
| `app/`, `bootstrap/`, `config/` | Laravel 標準ディレクトリ構成 |
| [database/factories/UserFactory.php](database/factories/UserFactory.php) | User ファクトリ（デフォルト） |
| `database/migrations/` | users, password_resets, failed_jobs, personal_access_tokens テーブル |
| [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) | 空のシーダー（初期状態） |
| [routes/web.php](routes/web.php) | ウェルカムページのみ |
| [composer.json](composer.json) | Laravel 9 依存関係 |
| [package.json](package.json) | Node 依存（Laravel Mix 構成） |
| `webpack.mix.js` | Laravel Mix 設定（後に Vite へ移行） |

---

## フェーズ 2 — Inertia.js + Vue 3 + 認証基盤（2026-05-25）

### 実装内容

SPA 化のため Inertia.js + Vue 3 を導入し、Laravel Breeze 相当の認証フローを実装。同時に Vite によるフロントエンドビルド環境に移行した。

### バックエンド — 追加・変更ファイル

| ファイル | 変更内容 |
|---------|---------|
| [app/Http/Middleware/HandleInertiaRequests.php](app/Http/Middleware/HandleInertiaRequests.php) | Inertia ミドルウェア追加。共有データ（auth.user 等）を定義 |
| [app/Http/Kernel.php](app/Http/Kernel.php) | `HandleInertiaRequests` を `web` ミドルウェアグループに登録 |
| `app/Http/Controllers/Auth/` | 認証コントローラ群を新規作成（Login, Register, PasswordReset, EmailVerification, PasswordConfirmation 等 7 ファイル） |
| [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php) | プロフィール表示・更新・削除 |
| [app/Http/Controllers/InertiaTestController.php](app/Http/Controllers/InertiaTestController.php) | Inertia 動作確認用（開発専用） |
| [app/Http/Requests/Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php) | ログインバリデーション・レート制限 |
| [app/Http/Requests/ProfileUpdateRequest.php](app/Http/Requests/ProfileUpdateRequest.php) | プロフィール更新バリデーション |
| [app/Models/InertiaTest.php](app/Models/InertiaTest.php) | Inertia 動作確認用モデル（開発専用） |
| [app/View/Components/AppLayout.php](app/View/Components/AppLayout.php) / [GuestLayout.php](app/View/Components/GuestLayout.php) | Blade コンポーネントレイアウト |
| [app/Providers/RouteServiceProvider.php](app/Providers/RouteServiceProvider.php) | `HOME` 定数を `/dashboard` に変更 |
| [routes/web.php](routes/web.php) | dashboard, profile, Inertia テストルートを追加 |
| [routes/auth.php](routes/auth.php) | 認証関連ルート定義（新規） |
| `database/migrations/2026_05_20_134148_create_inertia_tests_table.php` | Inertia テスト用テーブル（開発専用） |
| [lang/ja.json](lang/ja.json) / `lang/ja/` | 日本語言語ファイル追加 |
| `lang/en/validation.php` | バリデーションメッセージ拡充 |

### フロントエンド — 追加・変更ファイル

| ファイル | 変更内容 |
|---------|---------|
| [vite.config.js](vite.config.js) | Vite 設定（新規）。`laravel-vite-plugin` + `@vitejs/plugin-vue` |
| `webpack.mix.js` | 削除（Vite へ移行） |
| [package.json](package.json) | Vite / Vue 3 / Inertia 等の依存関係に更新 |
| [tailwind.config.js](tailwind.config.js) | Tailwind CSS 初期設定（`@tailwindcss/forms` プラグイン） |
| [postcss.config.js](postcss.config.js) | PostCSS 設定（新規） |
| [resources/css/app.css](resources/css/app.css) | Tailwind ディレクティブ追加 |
| [resources/js/app.js](resources/js/app.js) | Inertia アプリ初期化（`createInertiaApp`） |
| [resources/js/bootstrap.js](resources/js/bootstrap.js) | Axios 設定 |
| [resources/views/app.blade.php](resources/views/app.blade.php) | Inertia ルートテンプレート（新規） |
| `resources/views/auth/` | 認証 Blade テンプレート群（Inertia にリダイレクト） |
| `resources/views/layouts/` | app / guest / navigation レイアウト |
| `resources/views/components/` | 汎用 Blade コンポーネント群 |
| `resources/js/Components/` | Vue UI コンポーネント群（Button, Input, Modal, Dropdown, NavLink 等） |
| [resources/js/Layouts/AuthenticatedLayout.vue](resources/js/Layouts/AuthenticatedLayout.vue) | 認証後共通レイアウト（ナビゲーションバー含む） |
| [resources/js/Layouts/GuestLayout.vue](resources/js/Layouts/GuestLayout.vue) | 非認証時レイアウト |
| `resources/js/Pages/Auth/` | 認証ページ群（Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword） |
| [resources/js/Pages/Dashboard.vue](resources/js/Pages/Dashboard.vue) | ダッシュボードページ |
| `resources/js/Pages/Profile/` | プロフィール編集ページ（パスワード変更・アカウント削除含む） |
| `resources/js/Pages/Inertia/` | Inertia 動作確認ページ群（開発専用） |
| [resources/js/Pages/Welcome.vue](resources/js/Pages/Welcome.vue) | ウェルカムページ（Vue 版） |
| [jsconfig.json](jsconfig.json) | `@` エイリアスパス設定 |
| `tests/Feature/Auth/` | 認証テスト群（AuthenticationTest, RegistrationTest, PasswordResetTest 等） |
| [tests/Feature/ProfileTest.php](tests/Feature/ProfileTest.php) | プロフィールテスト |

---

## フェーズ 3 — 商品管理機能・shadcn-vue 導入（sec03_items ブランチ、作業中）

### 実装内容

美容室向け CRM の「商品（メニュー）管理」機能を追加。UI コンポーネントライブラリとして shadcn-vue を導入し、一覧表示ページを実装した。

### バックエンド — 追加・変更ファイル

#### 新規追加

| ファイル | 内容 |
|---------|------|
| [app/Models/Item.php](app/Models/Item.php) | Item モデル。`fillable`: `name`, `memo`, `price`, `is_selling` |
| [app/Http/Controllers/ItemController.php](app/Http/Controllers/ItemController.php) | Resource コントローラ。現時点で `index()` のみ実装済み（`Items/index` を Inertia レンダリング、最新順全件取得） |
| [app/Http/Requests/StoreItemRequest.php](app/Http/Requests/StoreItemRequest.php) | 商品作成バリデーション（ルール未定義、`authorize` は `false` のまま — 未完了） |
| [app/Http/Requests/UpdateItemRequest.php](app/Http/Requests/UpdateItemRequest.php) | 商品更新バリデーション（同上 — 未完了） |
| [app/Policies/ItemPolicy.php](app/Policies/ItemPolicy.php) | Item ポリシー（全メソッド空実装 — 未完了） |
| [database/migrations/2026_05_25_121451_create_items_table.php](database/migrations/2026_05_25_121451_create_items_table.php) | items テーブル作成。カラム: `id`, `name(string)`, `memo(string, nullable)`, `price(integer)`, `is_selling(boolean, default:true)`, `timestamps` |
| [database/factories/ItemFactory.php](database/factories/ItemFactory.php) | Item ファクトリ（`definition` 未実装 — 未完了） |
| [database/seeders/ItemSeeder.php](database/seeders/ItemSeeder.php) | 商品サンプルデータ投入（カット 6,000 円、カラー 8,000 円、パーマ 13,000 円） |
| [database/seeders/UserSeeder.php](database/seeders/UserSeeder.php) | テストユーザー作成（`test@test.com` / `password123`） |

#### 変更

| ファイル | 変更内容 |
|---------|---------|
| [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) | `UserSeeder` と `ItemSeeder` を `call()` で実行するよう追加 |
| [routes/web.php](routes/web.php) | `Route::resource('items', ItemController::class)->middleware(['auth', 'verified'])` を追加 |
| [resources/js/Layouts/AuthenticatedLayout.vue](resources/js/Layouts/AuthenticatedLayout.vue) | ナビゲーションバーに「商品管理」リンク（`items.index`）を追加（PC・モバイル両対応） |

### フロントエンド — 追加・変更ファイル

#### shadcn-vue 導入

| ファイル | 内容 |
|---------|------|
| [components.json](components.json) | shadcn-vue 設定ファイル。スタイル: `reka-vega`、フォント: Inter、CSS 変数方式 |
| [resources/js/lib/utils.js](resources/js/lib/utils.js) | `cn()` ユーティリティ（`clsx` + `tailwind-merge` の合成関数） |
| [resources/js/Components/ui/table/](resources/js/Components/ui/table/) | shadcn Table コンポーネント群（`Table`, `TableHeader`, `TableBody`, `TableRow`, `TableHead`, `TableCell`, `TableCaption`, `TableFooter`）と `index.js` エクスポート |

#### UI 設定変更

| ファイル | 変更内容 |
|---------|---------|
| [package.json](package.json) | shadcn 依存パッケージ追加: `radix-vue`, `class-variance-authority`, `clsx`, `tailwind-merge`, `shadcn-vue`, `@lucide/vue`, `tailwindcss-animate`, `tw-animate-css`。`axios` を `^0.25` → `^1.16.1`、`@vitejs/plugin-vue` を `^4` → `^6` にメジャーバージョンアップ |
| [tailwind.config.js](tailwind.config.js) | `darkMode: ['class']` を追加。`borderRadius` / `colors` に CSS 変数トークンを全面追加（shadcn-vue 対応） |
| [resources/css/app.css](resources/css/app.css) | Google Fonts（Inter）インポート追加。CSS カスタムプロパティでライト/ダークテーマのカラートークンを定義。`@layer base` で `border-border` / `bg-background` / `text-foreground` をグローバル適用 |
| [resources/js/Components/ApplicationLogo.vue](resources/js/Components/ApplicationLogo.vue) | SVG インライン → `<img src="/images/logo.png">` に差し替え |
| [resources/js/Layouts/GuestLayout.vue](resources/js/Layouts/GuestLayout.vue) | ロゴのサイズ指定を `class` の `w-20 h-20` から `style` の `width: 100px; height: 100px;` に変更（画像タグ対応のため） |

#### 商品一覧ページ

| ファイル | 内容 |
|---------|------|
| [resources/js/Pages/Items/index.vue](resources/js/Pages/Items/index.vue) | 商品一覧ページ。shadcn Table を使用。`items` prop を受け取り一覧表示。`is_selling` の値で「販売中（緑バッジ）」/「停止中（グレーバッジ）」を切り替え。`price` は `toLocaleString()` でカンマ区切り表示。商品なし時は空状態メッセージを表示 |

---

## 解決済み技術トラブル

### T-01: webpack.mix.js から Vite へのビルドツール移行

**発生フェーズ:** フェーズ 2

**問題:**
Laravel 9 の初期状態は Laravel Mix（webpack）を使用していたが、Inertia.js + Vue 3 のセットアップには `laravel-vite-plugin` が必要だった。

**解決策:**
- `webpack.mix.js` を削除
- `vite.config.js` を新規作成し `laravel-vite-plugin` と `@vitejs/plugin-vue` を設定
- `resources/views/app.blade.php` で `@vite(['resources/css/app.css', 'resources/js/app.js'])` に切り替え
- `package.json` の `scripts` を `mix` → `vite` コマンドに変更

**変更ファイル:** [vite.config.js](vite.config.js)（新規）、`webpack.mix.js`（削除）、[package.json](package.json)、[resources/views/app.blade.php](resources/views/app.blade.php)

---

### T-02: GuestLayout のロゴ表示崩れ

**発生フェーズ:** フェーズ 3

**問題:**
`ApplicationLogo.vue` を SVG インラインから `<img>` タグに差し替えた際、`GuestLayout.vue` で `class="w-20 h-20 fill-current"` を渡していたが、`fill-current` は SVG 専用プロパティのため `<img>` タグに効かず、サイズも想定通りに機能しなかった。

**解決策:**
`GuestLayout.vue` のロゴ部分を `style="width: 100px; height: 100px;"` でインラインスタイルに変更。`fill-current` クラスは残しているが実質無効（今後クリーンアップ推奨）。

**変更ファイル:** [resources/js/Layouts/GuestLayout.vue](resources/js/Layouts/GuestLayout.vue)

---

### T-03: shadcn-vue の CSS 変数と Tailwind の競合

**発生フェーズ:** フェーズ 3

**問題:**
shadcn-vue は CSS カスタムプロパティ（`--background`, `--foreground` 等）を使ってカラーシステムを管理するが、デフォルトの Tailwind 設定にはそれらのマッピングが存在しない。`bg-background` のようなユーティリティクラスが機能しなかった。

**解決策:**
1. `tailwind.config.js` の `theme.extend.colors` に CSS 変数参照（`hsl(var(--background))` 等）を全カラートークン分追加
2. `resources/css/app.css` に `:root` / `.dark` ブロックで変数値を定義
3. `tailwind.config.js` に `darkMode: ['class']` を追加してクラスベースのダークモードを有効化

**変更ファイル:** [tailwind.config.js](tailwind.config.js)、[resources/css/app.css](resources/css/app.css)

---

### T-04: @vitejs/plugin-vue メジャーバージョン不一致

**発生フェーズ:** フェーズ 3

**問題:**
`package.json` に記述していた `@vitejs/plugin-vue: "^4.0.0"` は Vite 4/5 系向けのバージョンであり、`vite: "^8.0.13"` と組み合わせると依存解決でエラーが発生した。

**解決策:**
`@vitejs/plugin-vue` を `^6.0.7` にメジャーバージョンアップ（Vite 8 対応版）。

**変更ファイル:** [package.json](package.json)

---

## 現状の未完了箇所・注意点

| 項目 | 場所 | 状態 |
|-----|------|------|
| 商品 CRUD の create/store/edit/update/destroy | [app/Http/Controllers/ItemController.php](app/Http/Controllers/ItemController.php) | 空実装。一覧以外は未実装 |
| StoreItemRequest / UpdateItemRequest のバリデーションルール | `app/Http/Requests/` | `rules()` が空、`authorize()` が `false` のまま（本番では 403 になる） |
| ItemPolicy の認可ロジック | [app/Policies/ItemPolicy.php](app/Policies/ItemPolicy.php) | 全メソッド空実装 |
| ItemFactory の `definition()` | [database/factories/ItemFactory.php](database/factories/ItemFactory.php) | 空実装（テストで Factory を使う場合は要実装） |
| ロゴ画像ファイル | `public/images/logo.png` | ファイルが存在する前提でコードが書かれているが、画像がない場合は alt テキスト表示になる |
| `fill-current` クラスの残骸 | [resources/js/Layouts/GuestLayout.vue](resources/js/Layouts/GuestLayout.vue) | `<img>` に無効な SVG 用クラスが残っているため整理推奨 |
| InertiaTestController / InertiaTest モデル | `app/Http/Controllers/`, `app/Models/` | 開発確認用。本番環境への持ち込み前に要削除検討 |
