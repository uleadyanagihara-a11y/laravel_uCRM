# uCRM 開発ログ（2026-05-13 以降）

> **対象ブランチ:** `sec02_inertia_vuejs` → `sec03_items`
> **期間:** 2026-05-13 〜 2026-05-26
> **作成者:** エンジニア内部ドキュメント（Claude Code との作業ログより自動抽出）

---

## 目次

1. [開発タイムライン](#開発タイムライン)
2. [セッション詳細](#セッション詳細)
   - [2026-05-19 — Breeze バージョン競合](#2026-05-19--breeze-バージョン競合)
   - [2026-05-19〜20 — Laravel Mix → Vite 移行](#2026-05-1920--laravel-mix--vite-移行)
   - [2026-05-20 — 起動手順の確認](#2026-05-20--起動手順の確認)
   - [2026-05-21 — ルートタイポ・リダイレクトループ修正](#2026-05-21--ルートタイポリダイレクトループ修正)
   - [2026-05-22 — Vue 未定義変数エラー・Udemy 互換性対応](#2026-05-22--vue-未定義変数エラーudemy-互換性対応)
   - [2026-05-25〜26 — 商品管理機能・shadcn-vue 導入](#2026-05-2526--商品管理機能shadcn-vue-導入)
3. [実装済みファイル一覧](#実装済みファイル一覧)
4. [解決済み技術トラブル一覧](#解決済み技術トラブル一覧)
5. [現状の未完了箇所・注意点](#現状の未完了箇所注意点)

---

## 開発タイムライン

| 日付 | ブランチ | セッション概要 |
|------|---------|--------------|
| 2026-05-18 | `sec02_inertia_vuejs` | Laravel 初期構築（git 初回コミット） |
| 2026-05-19 | `sec02_inertia_vuejs` | Breeze バージョン競合解決、Inertia.js セットアップ開始 |
| 2026-05-19〜20 | `sec02_inertia_vuejs` | Laravel Mix → Vite 完全移行、Inertia v1 構成確立 |
| 2026-05-20 | `sec02_inertia_vuejs` | 起動手順確認、ルート構成確認 |
| 2026-05-21 | `sec02_inertia_vuejs` | ルートタイポ修正、リダイレクトループ調査 |
| 2026-05-22 | `sec02_inertia_vuejs` | Vue 未定義変数バグ修正、Udemy コース互換性対応 |
| 2026-05-25 | `sec03_items` | 認証基盤フルコミット（119 ファイル）、商品管理 CRUD 実装開始 |
| 2026-05-26 | `sec03_items` | shadcn-vue 導入、商品一覧ページ実装、開発ドキュメント作成 |

---

## セッション詳細

---

### 2026-05-19 — Breeze バージョン競合

**作業ブランチ:** `sec02_inertia_vuejs`

#### 背景
Udemy 講座の手順に従って `composer require laravel/breeze:^1 --dev` を実行したところ、依存解決エラーが発生。

#### エラー内容
```
Your requirements could not be resolved to an installable set of packages.
  Problem 1
    - laravel/breeze v1.0.0 requires illuminate/filesystem ^8.0
      → このプロジェクトは Laravel 9 (illuminate/filesystem ^9) を使用
```

#### 原因
`^1` という制約が Composer によって `v1.0.0` 厳密指定として解釈された。  
Breeze v1.0.0 は Laravel 8 向けであり、Laravel 9 と非互換。

#### 修正
```bash
composer require laravel/breeze:"^1.9" --dev
```
Breeze 1.9 以上が Laravel 9 に対応。インストール後の手順：
```bash
php artisan breeze:install
npm install && npm run dev
php artisan migrate
```

#### 変更ファイル
| ファイル | 変更内容 |
|---------|---------|
| [composer.json](composer.json) | `require-dev` に `laravel/breeze: "^1.9"` を追加 |

---

### 2026-05-19〜20 — Laravel Mix → Vite 移行

**作業ブランチ:** `sec02_inertia_vuejs`

#### 背景
Udemy 講座（旧バージョン前提）の手順では Inertia.js の旧 v0 系パッケージ（`@inertiajs/inertia`, `@inertiajs/inertia-vue3`）を `package.json` に追記する指示があった。しかし、Breeze 1.9 が生成した Vue ファイルはすでに Inertia v1（`@inertiajs/vue3`）を使用していた。

#### 発生したエラー（連鎖的）

**エラー 1 — Inertia v0/v1 混在**
```
Module not found: @inertiajs/inertia
```
- **原因:** v0 系と v1 系の両方が `package.json` に存在し、Vue ファイルは v1 を使用しているが v0 が競合
- **修正:** `@inertiajs/inertia` と `@inertiajs/inertia-vue3`（v0 系）を `package.json` から削除して `npm install`

**エラー 2 — vue-loader 未インストール**
```
Module not found: vue-loader
```
- **原因:** `webpack.mix.js` に `.vue()` を追加したが `vue-loader` が未インストール
- **対応:** Mix が `vue-loader@^16.2.0` を自動インストール → 新たなエラーへ

**エラー 3 — webpack / vue-loader バージョン不整合**
```
Cannot find module 'webpack/lib/rules/DescriptionDataMatcherRulePlugin'
```
- **原因:** `vue-loader v16` が要求する webpack の内部 API がインストール済み webpack のバージョンに存在しない
- **根本原因:** Laravel Mix + webpack 構成全体が Vite 前提の構成（Inertia v1 + Vue 3）と整合しない

#### 根本解決 — Laravel Mix を廃止して Vite に完全移行

| 作業 | 詳細 |
|-----|------|
| `webpack.mix.js` 削除 | Laravel Mix 設定ファイルを完全削除 |
| `package.json` 整理 | `laravel-mix`, `vue-loader`, `webpack`, `webpack-cli` を削除。`laravel-vite-plugin`, `vite`, `@vitejs/plugin-vue` を追加 |
| `vite.config.js` 新規作成 | `laravel-vite-plugin` + `@vitejs/plugin-vue` の設定 |
| `resources/js/app.js` 書き直し | `createInertiaApp` を使った Inertia v1 初期化に変更 |
| `resources/views/app.blade.php` | `@vite(['resources/css/app.css', 'resources/js/app.js'])` に変更 |
| `package.json` scripts 変更 | `"dev": "mix"` → `"dev": "vite"`, `"build": "vite build"` |

#### 変更ファイル
| ファイル | 変更内容 |
|---------|---------|
| [package.json](package.json) | Vite 系構成に全面刷新 |
| `webpack.mix.js` | 削除 |
| [vite.config.js](vite.config.js) | 新規作成 |
| [resources/js/app.js](resources/js/app.js) | Inertia v1 初期化に書き直し |
| [resources/views/app.blade.php](resources/views/app.blade.php) | `@vite` ディレクティブに変更 |
| [jsconfig.json](jsconfig.json) | `@` エイリアスのパス設定更新 |

---

### 2026-05-20 — 起動手順の確認

**作業ブランチ:** `sec02_inertia_vuejs`

#### 背景
開発環境を起動する際のコマンドを確認。

#### エラー内容
```
Could not open input file: arisan
```
- **原因:** `php artisan serve` を `php arisan serve` とタイポ
- **修正:** `php artisan serve` と正しく入力

#### 確認された起動手順
```bash
# ターミナル 1（Laravel）
php artisan serve   → http://127.0.0.1:8000

# ターミナル 2（Vite ホットリロード）
npm run dev
```

#### 毎回の起動前コマンド（クローン後・初回のみ）
```bash
composer install
npm install
php artisan key:generate
php artisan migrate
```

---

### 2026-05-21 — ルートタイポ・リダイレクトループ修正

**作業ブランチ:** `sec02_inertia_vuejs`

#### エラー 1 — /inertia/create が 404

**エラー内容:**
```
404 Not Found — /inertia/create
```

**原因:** `routes/web.php` 内のルート定義にタイポ
```php
// 誤り（creste）
Route::get('/inertia/creste', [InertiaTestController::class, 'create']);

// 正しい（create）
Route::get('/inertia/create', [InertiaTestController::class, 'create']);
```

**修正:** [routes/web.php](routes/web.php) の `creste` → `create` に修正

#### エラー 2 — リダイレクトループ

**エラー内容:**
```
ページが何度もリダイレクトされています
```

**調査ファイル:**
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Controllers/InertiaTestController.php`
- `resources/js/Pages/Inertia/index.vue`, `Create.vue`, `show.vue`

**原因の特定と修正:** Inertia ミドルウェアの設定と `app.blade.php` の `@inertiaHead` / `@routes` ディレクティブの不足が組み合わさり発生していた。

#### 変更ファイル
| ファイル | 変更内容 |
|---------|---------|
| [routes/web.php](routes/web.php) | `/inertia/creste` → `/inertia/create` |

---

### 2026-05-22 — Vue 未定義変数エラー・Udemy 互換性対応

**作業ブランチ:** `sec02_inertia_vuejs`

#### エラー 1 — InertiaTest is not defined（+ URL タイポ）

**エラー内容:**
```
[Vue warn]: Uncaught ReferenceError: InertiaTest is not defined
  at show.vue:11
```

**原因（2点複合）:**
1. `show.vue` 内で削除処理に `InertiaTest.delete(...)` と書いていたが、`InertiaTest` は定義されていない。正しくは `import { router } from '@inertiajs/vue3'` でインポートした `router` を使用すべき。
2. URL にタイポ: `/intia/${id}` → `/inertia/${id}`

**修正前（show.vue）:**
```js
InertiaTest.delete(`/intia/${id}`, {
    onSuccess: () => { ... }
})
```

**修正後（show.vue）:**
```js
router.delete(`/inertia/${id}`, {
    onSuccess: () => { ... }
})
```

**変更ファイル:** [resources/js/Pages/Inertia/show.vue](resources/js/Pages/Inertia/show.vue)

---

#### エラー 2 — Udemy 講座とのコンポーネント名の差異

**背景:**
Udemy 講座（旧 Breeze 版）と現在のプロジェクト（Breeze 1.9 生成）でコンポーネントのファイル名が異なる。講座の `import` 文をそのままコピーすると `Module not found` エラーになる。

**対応表:**

| Udemy 講座のインポート | このプロジェクトの正しいパス |
|---------------------|--------------------------|
| `@/Components/Label.vue` | `@/Components/InputLabel.vue` |
| `@/Components/Input.vue` | `@/Components/TextInput.vue` |
| `@/Components/Button.vue` | `@/Components/PrimaryButton.vue` |
| `@/Components/ValidationErrors.vue` | `@/Components/InputError.vue` |

**修正方針:** インポートのファイル名のみ変更する。インポート変数名はそのままでよい。

```js
// 例: Udemy の記述
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'

// このプロジェクトでの正しい記述
import Label from '@/Components/InputLabel.vue'
import Input from '@/Components/TextInput.vue'
```

---

### 2026-05-25〜26 — 商品管理機能・shadcn-vue 導入

**作業ブランチ:** `sec03_items`

#### 概要
美容室向け CRM の「商品（メニュー）管理」機能を新規実装。UI コンポーネントライブラリとして shadcn-vue を導入し、商品一覧表示ページを実装した。

#### エラー 1 — @vitejs/plugin-vue のメジャーバージョン不一致

**エラー内容:**
```
npm ERR! Could not resolve dependency:
  peer vite@"^4.0.0 || ^5.0.0" from @vitejs/plugin-vue@4.x.x
```

**原因:** `package.json` の `@vitejs/plugin-vue: "^4.0.0"` は Vite 4/5 向け。`vite: "^8.0.13"` と組み合わせると互換性エラー。

**修正:**
```json
// 変更前
"@vitejs/plugin-vue": "^4.0.0"

// 変更後
"@vitejs/plugin-vue": "^6.0.7"
```

#### エラー 2 — shadcn-vue の CSS 変数と Tailwind の競合

**エラー内容:**
`bg-background`, `text-foreground`, `text-muted-foreground` 等の Tailwind ユーティリティクラスが機能しない（色が適用されない）。

**原因:** shadcn-vue は CSS カスタムプロパティ（`--background` 等）でカラーシステムを管理するが、デフォルトの Tailwind 設定にはそれらのマッピングが存在しない。

**修正（2 ファイル）:**

[tailwind.config.js](tailwind.config.js) に CSS 変数参照を追加：
```js
module.exports = {
    darkMode: ['class'],  // ← 追加
    theme: {
        extend: {
            colors: {
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                // ... 全カラートークン
            },
            borderRadius: {
                lg: 'var(--radius)',
                // ...
            }
        }
    }
}
```

[resources/css/app.css](resources/css/app.css) に CSS カスタムプロパティを定義：
```css
@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 0 0% 3.9%;
    /* ... 全カラートークン */
  }
  .dark {
    --background: 0 0% 3.9%;
    /* ... ダークテーマ */
  }
}
```

#### エラー 3 — GuestLayout のロゴ表示崩れ

**原因:** `ApplicationLogo.vue` を SVG インラインから `<img>` タグに差し替えた際、`GuestLayout.vue` で `class="w-20 h-20 fill-current"` を渡していたが、`fill-current` は SVG 専用プロパティのため `<img>` タグには無効。

**修正:** `GuestLayout.vue` を `style="width: 100px; height: 100px;"` に変更。

```vue
<!-- 変更前 -->
<ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />

<!-- 変更後 -->
<ApplicationLogo
    class="fill-current text-gray-500"
    style="width: 100px; height: 100px;"
/>
```

**変更ファイル:** [resources/js/Layouts/GuestLayout.vue](resources/js/Layouts/GuestLayout.vue)

#### 実装内容

**バックエンド（新規作成）:**

| ファイル | 内容 |
|---------|------|
| [app/Models/Item.php](app/Models/Item.php) | Item モデル。`fillable`: `name`, `memo`, `price`, `is_selling` |
| [app/Http/Controllers/ItemController.php](app/Http/Controllers/ItemController.php) | Resource コントローラ。`index()` のみ実装済み（最新順全件取得→Inertia レンダリング） |
| [app/Http/Requests/StoreItemRequest.php](app/Http/Requests/StoreItemRequest.php) | 商品作成バリデーション（未実装） |
| [app/Http/Requests/UpdateItemRequest.php](app/Http/Requests/UpdateItemRequest.php) | 商品更新バリデーション（未実装） |
| [app/Policies/ItemPolicy.php](app/Policies/ItemPolicy.php) | Item ポリシー（全メソッド空実装） |
| [database/migrations/2026_05_25_121451_create_items_table.php](database/migrations/2026_05_25_121451_create_items_table.php) | `items` テーブル: `id`, `name`, `memo(nullable)`, `price(integer)`, `is_selling(boolean, default:true)`, `timestamps` |
| [database/factories/ItemFactory.php](database/factories/ItemFactory.php) | Item ファクトリ（未実装） |
| [database/seeders/ItemSeeder.php](database/seeders/ItemSeeder.php) | サンプルデータ: カット 6,000 円、カラー 8,000 円、パーマ 13,000 円 |
| [database/seeders/UserSeeder.php](database/seeders/UserSeeder.php) | テストユーザー: `test@test.com` / `password123` |

**バックエンド（変更）:**

| ファイル | 変更内容 |
|---------|---------|
| [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) | `UserSeeder`, `ItemSeeder` を `call()` で実行するよう追加 |
| [routes/web.php](routes/web.php) | `Route::resource('items', ItemController::class)->middleware(['auth', 'verified'])` を追加 |
| [resources/js/Layouts/AuthenticatedLayout.vue](resources/js/Layouts/AuthenticatedLayout.vue) | ナビゲーションに「商品管理」リンク（`items.index`）を追加（PC・モバイル両対応） |

**フロントエンド（shadcn-vue 関連、新規）:**

| ファイル | 内容 |
|---------|------|
| [components.json](components.json) | shadcn-vue 設定（スタイル: reka-vega、フォント: Inter、CSS 変数方式） |
| [resources/js/lib/utils.js](resources/js/lib/utils.js) | `cn()` ユーティリティ（`clsx` + `tailwind-merge` の合成関数） |
| [resources/js/Components/ui/table/](resources/js/Components/ui/table/) | shadcn Table コンポーネント群（Table, Header, Body, Row, Head, Cell, Caption, Footer）＋ `index.js` |

**フロントエンド（商品ページ、新規）:**

| ファイル | 内容 |
|---------|------|
| [resources/js/Pages/Items/index.vue](resources/js/Pages/Items/index.vue) | 商品一覧ページ。shadcn Table 使用。`is_selling` で「販売中（緑）」/「停止中（グレー）」バッジ表示。`price` を `toLocaleString()` でカンマ区切り。商品なし時の空状態表示あり |

---

## 実装済みファイル一覧

### コミット `8c67185`（2026-05-18）— Laravel 初期構築

86 ファイル追加。Laravel 9 の標準ディレクトリ構成、User モデル、基本ルート、標準マイグレーション等。

---

### コミット `49ba9ab`（2026-05-25）— Inertia.js + Vue 3 + 認証基盤

119 ファイル変更、71,800 行追加。主要ファイルは以下の通り。

#### バックエンド

| ファイル | 内容 |
|---------|------|
| [app/Http/Middleware/HandleInertiaRequests.php](app/Http/Middleware/HandleInertiaRequests.php) | Inertia ミドルウェア。`auth.user` と Ziggy ルート情報を全ページに共有 |
| [app/Http/Kernel.php](app/Http/Kernel.php) | `HandleInertiaRequests` を `web` ミドルウェアグループに登録 |
| `app/Http/Controllers/Auth/` | 認証コントローラ 7 本（Login, Register, PasswordReset, EmailVerification, PasswordConfirmation, NewPassword, PasswordResetLink） |
| [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php) | プロフィール表示・更新・削除 |
| [app/Http/Controllers/InertiaTestController.php](app/Http/Controllers/InertiaTestController.php) | Inertia 動作確認用 CRUD（開発専用） |
| [app/Http/Requests/Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php) | ログインバリデーション・レート制限 |
| [app/Http/Requests/ProfileUpdateRequest.php](app/Http/Requests/ProfileUpdateRequest.php) | プロフィール更新バリデーション |
| [app/Models/InertiaTest.php](app/Models/InertiaTest.php) | Inertia 動作確認用モデル（開発専用） |
| [app/View/Components/AppLayout.php](app/View/Components/AppLayout.php) / [GuestLayout.php](app/View/Components/GuestLayout.php) | Blade レイアウトコンポーネント |
| [app/Providers/RouteServiceProvider.php](app/Providers/RouteServiceProvider.php) | `HOME` 定数を `/dashboard` に変更 |
| [routes/web.php](routes/web.php) | dashboard, profile, InertiaTest CRUD ルートを追加 |
| [routes/auth.php](routes/auth.php) | 認証関連ルート一式（新規） |
| `database/migrations/2026_05_20_134148_create_inertia_tests_table.php` | Inertia テスト用テーブル（開発専用） |
| [lang/ja.json](lang/ja.json) / `lang/ja/` | 日本語言語ファイル新規追加 |

#### フロントエンド

| ファイル | 内容 |
|---------|------|
| [vite.config.js](vite.config.js) | `laravel-vite-plugin` + `@vitejs/plugin-vue` |
| [tailwind.config.js](tailwind.config.js) | Tailwind 初期設定（`@tailwindcss/forms` プラグイン） |
| [postcss.config.js](postcss.config.js) | PostCSS 設定 |
| [resources/css/app.css](resources/css/app.css) | Tailwind ディレクティブ |
| [resources/js/app.js](resources/js/app.js) | `createInertiaApp` による Inertia v1 初期化 |
| [resources/views/app.blade.php](resources/views/app.blade.php) | Inertia ルートテンプレート |
| `resources/views/auth/` | 認証 Blade テンプレート 6 本 |
| `resources/views/layouts/` | app / guest / navigation レイアウト |
| `resources/views/components/` | 汎用 Blade コンポーネント 12 本 |
| `resources/js/Components/` | Vue UI コンポーネント 13 本（Button, Input, Modal, Dropdown, NavLink 等） |
| [resources/js/Layouts/AuthenticatedLayout.vue](resources/js/Layouts/AuthenticatedLayout.vue) | 認証後共通レイアウト（ナビゲーションバー） |
| [resources/js/Layouts/GuestLayout.vue](resources/js/Layouts/GuestLayout.vue) | 非認証時レイアウト |
| `resources/js/Pages/Auth/` | 認証ページ 7 本（Login, Register, ForgotPassword 等） |
| [resources/js/Pages/Dashboard.vue](resources/js/Pages/Dashboard.vue) | ダッシュボードページ |
| `resources/js/Pages/Profile/` | プロフィール編集（パスワード変更・アカウント削除含む） |
| `resources/js/Pages/Inertia/` | Inertia 動作確認ページ 3 本（開発専用） |
| [jsconfig.json](jsconfig.json) | `@` エイリアスパス設定 |
| `tests/Feature/Auth/` | 認証テスト 6 本 |
| [tests/Feature/ProfileTest.php](tests/Feature/ProfileTest.php) | プロフィールテスト |

---

### 未コミット作業（`sec03_items` ブランチ）

上記「[2026-05-25〜26 — 商品管理機能・shadcn-vue 導入](#2026-05-2526--商品管理機能shadcn-vue-導入)」セクションを参照。

---

## 解決済み技術トラブル一覧

| # | 日付 | エラー概要 | 原因 | 修正方法 |
|---|------|-----------|------|---------|
| T-01 | 2026-05-19 | `laravel/breeze` インストール失敗 | `^1` が `v1.0.0`（Laravel 8 専用）に解決 | `^1.9` を指定 |
| T-02 | 2026-05-19 | Inertia v0/v1 パッケージ混在 | Udemy 手順の旧パッケージを追加したことで競合 | v0 系パッケージを削除 |
| T-03 | 2026-05-19 | `vue-loader` で webpack 内部 API エラー | vue-loader v16 と webpack バージョン不整合 | Laravel Mix 廃止・Vite に完全移行 |
| T-04 | 2026-05-20 | `Could not open input file: arisan` | `php artisan serve` のタイポ（arisan） | `artisan` と正しく入力 |
| T-05 | 2026-05-21 | `/inertia/create` が 404 | `routes/web.php` の `creste` タイポ | `create` に修正 |
| T-06 | 2026-05-21 | 認証後にリダイレクトループ | Inertia ミドルウェア設定と `app.blade.php` の不備 | ミドルウェア設定・blade テンプレートを修正 |
| T-07 | 2026-05-22 | `InertiaTest is not defined` + URL タイポ | `router` を `InertiaTest` という誤名で使用、`/intia/` タイポ | `router.delete('/inertia/...')` に修正 |
| T-08 | 2026-05-22 | Udemy 講座のコンポーネント名と不一致 | Breeze 新版でファイル名が変わっている | `Label` → `InputLabel`、`Input` → `TextInput` 等に置換 |
| T-09 | 2026-05-25 | `@vitejs/plugin-vue` v4 と Vite v8 の互換エラー | v4 は Vite 4/5 向け | `@vitejs/plugin-vue` を v6 に更新 |
| T-10 | 2026-05-26 | shadcn-vue の Tailwind ユーティリティが無効 | Tailwind に CSS 変数マッピングが未定義 | `tailwind.config.js` にカラートークンを追加・`app.css` に CSS 変数を定義 |
| T-11 | 2026-05-26 | GuestLayout のロゴサイズが崩れる | SVG の `fill-current` が `<img>` タグに無効 | `style` でサイズをインライン指定 |

---

## 現状の未完了箇所・注意点

| 項目 | ファイル | 状況 |
|-----|---------|------|
| 商品 CRUD（create/store/edit/update/destroy） | [app/Http/Controllers/ItemController.php](app/Http/Controllers/ItemController.php) | `index()` のみ実装済み、残りは空 |
| バリデーションルール未定義 | [app/Http/Requests/StoreItemRequest.php](app/Http/Requests/StoreItemRequest.php) | `rules()` が空、`authorize()` が `false` のまま → **本番では 403 になる** |
| 更新バリデーション未定義 | [app/Http/Requests/UpdateItemRequest.php](app/Http/Requests/UpdateItemRequest.php) | 同上 |
| 認可ロジック未実装 | [app/Policies/ItemPolicy.php](app/Policies/ItemPolicy.php) | 全メソッド空実装 |
| Factory 未実装 | [database/factories/ItemFactory.php](database/factories/ItemFactory.php) | `definition()` が空（テスト作成時に要実装） |
| ロゴ画像ファイル | `public/images/logo.png` | ファイルが存在する前提でコードが書かれている（ない場合 alt テキスト表示） |
| `fill-current` の残骸 | [resources/js/Layouts/GuestLayout.vue](resources/js/Layouts/GuestLayout.vue) | `<img>` タグに無効な SVG 用クラスが残存。整理推奨 |
| 開発専用コード | `InertiaTestController`, `InertiaTest` モデル, `/inertia/*` ルート | 本番環境への持ち込み前に削除検討 |
