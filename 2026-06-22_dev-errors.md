# 開発エラーログ & Codex やりとり抽出まとめ
**期間: 2026-06-13 ～ 2026-06-22**
**対象ブランチ: sec07_analysis**

---

## 目次
1. [2026-06-16 sec06_purchaseHistory](#2026-06-16)
2. [2026-06-17 vue-chart](#2026-06-17)
3. [2026-06-19 decile 分析](#2026-06-19)
4. [2026-06-22 RFM 分析（作業中）](#2026-06-22)

---

## 2026-06-16 {#2026-06-16}
**コミット:** `ad41db7`, `c421943` — `sec06_purchaseHistory`

### 実装内容
- `Order` モデル・`Subtotal` グローバルスコープ新規作成
- `Purchases/Index.vue`, `Edit.vue`, `Show.vue` 実装
- `scopeBetweenDate` スコープ追加（`Order.php`）

### sample.md / sample2.md（Codex へのプロンプト素材）
購入履歴の SQL を段階的に組み立てる過程を Codex に質問するために下書きされた SQL。

```sql
-- sample.md（Step 1: 購入明細の結合）
select purchases.id as id, item_purchase.id as pivot_id,
  customers.name as customer_name,
  items.price * item_purchase.quantity as subtotal,
  items.name as item_name, items.price as item_price,
  item_purchase.quantity, purchases.status,
  purchases.created_at, purchases.updated_at
from purchases
left join item_purchase on purchases.id = item_purchase.purchase_id
left join items on item_purchase.item_id = items.id
left join customers on purchases.customer_id = customers.id
```

```sql
-- sample2.md（Step 2: 購入IDでグループ化して合計金額取得）
select history.id, history.customer_name, sum(history.subtotal) as total,
  history.status, history.created_at
from (...step1 の SQL...) as history
group by history.id
```

---

### エラー 1: `scopeBetweenDate` の endDate 境界バグ

**ファイル:** `app/Models/Order.php`

**エラーの原因:**
```php
// バグあり
if(is_null($startDate) && !is_null($endDate)){
    $endDate1 = Carbon::parse($endDate)->addDays(1); // ← 計算するが使っていない
    return $query->where('created_at', "<=", $endDate); // ← $endDate1 を使うべき
}
```
`$endDate` が `'2022-08-31'` の場合、MySQL は `<= '2022-08-31 00:00:00'` と解釈する。
→ 8/31 の注文データがほぼすべて漏れる（終日が 00:00:00 より後のため）。

**対策:**
```php
if(is_null($startDate) && !is_null($endDate)){
    $endDate1 = Carbon::parse($endDate)->addDays(1);
    return $query->where('created_at', "<", $endDate1); // $endDate1 を使う
}
```
`endDate + 1日` に `<`（未満）で比較することで終日を含められる。

---

## 2026-06-17 {#2026-06-17}
**コミット:** `882d722`, `d3827b3` — `vue-chart`

### Codex とのやりとり（bun.md 抽出）

**質問内容（要約）:**
> vue-chartjs の Getting Started ドキュメントに載っている `App.vue` は何をしていますか？

**参照 URL:** https://vue-chartjs.org/guide/

**Codex の回答（要旨）:**
ドキュメントの `App.vue` は「`BarChart` コンポーネントをインポートして画面に表示するだけのルートコンポーネント」の例示。
Inertia.js + Laravel 環境では `App.vue` は存在せず、代わりに各ページコンポーネント（`Analysis.vue` など）に直接 `BarChart` をインポートして使えばよい。

```vue
<!-- Analysis.vue での正しい使い方 -->
<script setup>
import BarChart from '@/Components/BarChart.vue'
</script>
<template>
  <BarChart :data="data" />
</template>
```

---

### エラー 2: `$data` 未定義変数エラー（API コントローラ）

**ファイル:** `app/Http/Controllers/Api/AnalysisController.php`

**エラーの原因:**
```php
// バグあり（882d722 時点）
if($request->type === 'perDay'){
    // ...
    $data = DB::table($subQuery)->...->get(); // if の中でのみ定義
}
// type が 'perDay' 以外だと $data は未定義
$labels = $data->pluck('date'); // ← ErrorException: Undefined variable $data
$totals = $data->pluck('total');
```

**対策:**
各分析タイプをサービスクラスに切り出し、`if` ごとに早期リターンするパターンに変更。

```php
// 修正後（d3827b3）
if($request->type === 'perDay'){
    list($data, $labels, $totals) = AnalysisServices::perDay($subQuery);
}
if($request->type === 'perMonth'){
    list($data, $labels, $totals) = AnalysisServices::perMonth($subQuery);
}
// ...
```

`$data` が必ず定義されることが保証され、Undefined variable エラーが解消。

---

### エラー 3: `v-model` のバインド先ミス

**ファイル:** `resources/js/Pages/Analysis.vue`

**エラーの原因:**
```html
<!-- バグあり（882d722 時点） -->
To: <input type="date" name="endDate" v-model="form.startDate">
<!--                                              ^^^^^^^^^^^ endDate にすべき -->
```
endDate 入力欄の変更が `form.startDate` に書き込まれるため、
終了日を変更すると開始日が書き換わり、正しい期間でリクエストが飛ばない。

**対策:**
```html
<!-- 修正後（d3827b3） -->
To: <input type="date" name="endDate" v-model="form.endDate">
```

---

## 2026-06-19 {#2026-06-19}
**コミット:** `16a772b` — `decile`

### 実装内容
- デシル分析ロジックを `DecileServices.php` に切り出し
- 日別・月別・年別売上を `AnalysisServices.php` に集約
- `ResultTable.vue` コンポーネント新規作成
- `Analysis.vue` に分析タイプ選択 UI を追加

---

### エラー 4: MySQL セッション変数 `@row_num` の接続共有リスク

**ファイル:** `app/Services/DecileServices.php`

**エラーの原因:**
```php
DB::statement('set @row_num = 0;'); // 接続 A でセッション変数をセット
$subQuery = DB::table($subQuery)
    ->selectRaw('@row_num:= @row_num+1 as row_num, ...'); // 接続 B になる可能性
```
Laravel のデフォルト接続プールでは `DB::statement()` と直後の `DB::table()` が
**別の DB 接続**で実行される場合がある。
その場合、`@row_num` が未定義のまま `NULL` を返し、全行の `row_num` が `NULL` になる。

さらに、前のリクエストの残存値が混入するリスクもある（セッション変数は接続単位で保持）。

**対策（MySQL 8.0 以降）:**
```sql
-- ROW_NUMBER() ウィンドウ関数を使う（接続に依存しない）
SELECT ROW_NUMBER() OVER (ORDER BY total DESC) AS row_num,
       customer_id, customer_name, total
FROM (...)
```

Laravel での書き方:
```php
$subQuery = DB::table($subQuery)
    ->selectRaw('ROW_NUMBER() OVER (ORDER BY total DESC) as row_num,
                 customer_id, customer_name, total');
```

MySQL 5.7 以下の場合は `DB::transaction()` 内で同一接続に固定する方法もある。

---

## 2026-06-22 {#2026-06-22}
**ブランチ:** `sec07_analysis` — **作業中（未コミット）**

### 実装内容
- RFM 分析（Recency / Frequency / Monetary）を `AnalysisController::index()` に実装中

---

### エラー 5: `DB::table()` に Collection を渡すエラー（現在進行形）

**ファイル:** `app/Http/Controllers/AnalysisController.php` L77–82

**エラーの原因:**
```php
// 77行目: .get() でクエリを実行 → $subQuery が Collection になる
$subQuery = DB::table($subQuery)
    ->selectRaw('...', $rfmPrms)->get(); // ← ここで実行済み!

// 82行目以降: Collection を DB::table() に渡す → 型エラー
$total = DB::table($subQuery)->count(); // ← $subQuery は Collection, 文字列でもクエリビルダーでもない
```

`DB::table()` が受け付けるのは **文字列（テーブル名）** または **クロージャ/クエリビルダー（サブクエリ）** のみ。
`Collection` を渡すと `InvalidArgumentException` が発生する。

**対策:**
`.get()` を呼ぶのは**最後に一度だけ**。サブクエリとして使う間はクエリビルダーのまま保持する。

```php
// 修正方針
$rfmQuery = DB::table($subQuery)
    ->selectRaw('...rfm case when...', $rfmPrms);
// ↑ .get() を呼ばない → クエリビルダーのまま

// 件数取得など途中の集計は別の変数で
$count = DB::table($rfmQuery)->count();

// 最後のクエリだけ .get() で実行
$data = DB::table(DB::table($rfmQuery)->groupBy('r')
    ->selectRaw('concat("r_", r) as rRank, ...')
)->get();
```

> **補足:** `dd($subQuery)` を途中に置いてデバッグしている場合、
> `dd()` は Laravel Collection も正常に表示するが、その後のコードが動かなくなることに注意。
> `dd()` はあくまでデバッグ用の一時手段であり、サービスクラスに切り出すタイミングで除去する。

---

## まとめ表

| 日付       | エラー種別                     | 原因                                              | 対策                                        |
|------------|-------------------------------|--------------------------------------------------|---------------------------------------------|
| 2026-06-16 | Carbon 日付境界バグ            | `$endDate1` を計算したが `$endDate` を使用         | `$endDate1`（+1日）を `<` で比較             |
| 2026-06-17 | PHP Undefined variable        | `if` ブロック内で定義した `$data` を外で参照        | サービスクラスへ切り出し、常に定義を保証       |
| 2026-06-17 | Vue v-model バインド先ミス     | `form.endDate` に `form.startDate` をバインド      | 正しいフォームフィールド名に修正              |
| 2026-06-19 | MySQL セッション変数の接続分離  | `@row_num` が別接続で参照不能になる                 | `ROW_NUMBER()` ウィンドウ関数で代替           |
| 2026-06-22 | DB::table() に Collection 渡し | `.get()` 実行後に subquery として再利用しようとした | `.get()` は最終クエリのみ呼ぶ               |
