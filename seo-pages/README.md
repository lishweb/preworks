# SEOページの書き方（seo-pages/）

preworks のSEO固定ページは、**ブロックエディタではなくテーマ内のPHPファイルで本文を管理**します。
WordPressの固定ページは「URL（スラッグ）を作るための空の箱」で、本文は空のままで構いません。

## しくみ

| 役割 | ファイル |
|---|---|
| 料金・実績数値・メンター・要確認フラグ（唯一の正） | `inc/pw-data.php` |
| 31ページの登録表（title / description / H1 / パンくず / 構造化データ） | `inc/pw-pages.php` |
| 表示部品・SEO出力・ルーター・管理画面 | `inc/pw-core.php` |
| 各ページの本文 | `seo-pages/{スラッグ}.php` |
| 追加CSS / JS | `assets/css/pw-pages.css` / `assets/js/pw-pages.js` |

- 固定ページのスラッグが登録表にあり、`seo-pages/{スラッグ}.php` が存在すれば、自動でそのファイルが表示に使われる（`template_include`）。
- `<title>`・meta description・OGP・パンくず・BreadcrumbList・FAQPage・Article・Course の構造化データは登録表から自動出力。テンプレートに書かない。
- 内部リンクは `pw_link()` で張る。**リンク先が未公開なら自動で文字だけになる**ので、公開順を気にせず全ページを先に入れておける。
- 事実確認が必要な箇所は `pw_tbc_start('キー')`〜`pw_tbc_end()` で囲む。`pw-data.php` の `confirm` でそのキーを `'ok' => true` にするまで一般公開されない（管理者には赤枠で見える）。

## ページファイルの基本形

```php
<?php
/**
 * S07 収入・単価の目安  /income/
 * 主要KW：オンライン秘書 月収
 */
if ( ! defined( 'ABSPATH' ) ) exit;
pw_page_start( array( 'toc' => true ) );   // ヘッダー＋H1（登録表から）＋本文開始
?>

<?php pw_summary( '<p>結論…</p>' ); ?>
<p>導入文…</p>
<?php pw_toc(); ?>

<h2>見出し</h2>
<p>本文…<?php pw_link( 'skills', '必要なスキル' ); ?>…</p>

<?php pw_cta(); ?>
<?php pw_faq( array( array( 'q' => '質問', 'a' => '回答' ) ) ); ?>
<?php pw_related( array( 'online-secretary', 'skills' ) ); ?>

<?php pw_page_end(); ?>   // 末尾Contact＋フッター
```

- **H1は書かない**（`pw_page_start()` が登録表の h1 を1回だけ出す）。本文は `<h2>` から。
- `pw_page_start()` の引数：`'toc' => true`（目次）、`'layout' => 'wide'`（カード中心で横幅を広く使う）、`'lead' => '…'`（H1下の導入文）。
- 完成見本：`seo-pages/income.php`

## 部品一覧

| 関数 | 用途 |
|---|---|
| `pw_summary( $html, $label )` | 冒頭の結論ボックス |
| `pw_toc()` | 目次（H2が3つ以上で表示） |
| `pw_cta( array( 'h','p','btn','sub' ) )` | 本文中のLINE無料相談CTA |
| `pw_line_btn( $label, $pos )` | LINEボタン単体（計測属性つき） |
| `pw_faq( $items, $title )` | FAQ（FAQPage構造化データに自動登録）。`$title=''` で見出しなし |
| `pw_table( $head, $rows, array( 'first_col_th' => true ) )` | 表（スマホは横スクロール） |
| `pw_steps( array( array( 't','d' ) ) )` | STEP1〜の手順 |
| `pw_cards( array( array( 't','d','tag','link' ) ), $cols )` | カード一覧（link に登録スラッグでリンク化） |
| `pw_box( $title, $items, 'list'|'num' )` | 箇条書きボックス |
| `pw_callout( $html )` | 補足ボックス |
| `pw_quote( $text, $who, 'mentor/tamaki-michiko' )` | 発言の引用 |
| `pw_plans()` / `pw_plan_table()` / `pw_price_note()` | 料金カード／プラン比較表／入会金注記（pw-data.php から） |
| `pw_stats( array( 'urakata_companies','secretaries','job_rate' ) )` | 実績数値 |
| `pw_mentors( array( 'tamaki','toda' ) )` / `pw_voices( array( 'k' ) )` | メンター・修了生カード |
| `pw_related( array( slug… ) )` | 関連ページ（公開済みのみ） |
| `pw_related_posts( 'カテゴリslug' )` | 関連コラム（記事が公開されたら自動表示） |
| `pw_operator_note()` | 運営元の明記（比較・推奨箇所のステマ規制対応） |
| `pw_note( $html )` | 小さな注記 |
| `pw_term( $term, $html )` | 用語集の1項目（`<dl class="pw-terms">` 内で使う） |
| `pw_link( $slug, $text )` / `pw_a()` | 内部リンク（echo / 文字列で返す） |
| `pw_ext( $url, $text )` | 外部リンク |
| `pw_tbc_start( $key )` / `pw_tbc_end()` | 要確認ブロック |
| `pw( 'plans.2.price' )` / `pw_yen()` / `pw_stat( 'urakata_companies' )` / `pw_job_rate_text()` / `pw_v()` | 共通データの取り出し |

## 書くときのルール

1. 料金・実績数値・人数は**本文に直書きしない**。`pw_yen( pw( 'plans.2.price' ) )`、`pw_stat( 'urakata_companies' )`、`pw_job_rate_text()` を使う（数字の更新は pw-data.php だけで済む）。
2. 数字は時点と母数を併記（`pw_stat()` / `pw_job_rate_text()` は自動で付く）。
3. 「必ず稼げる」「誰でも」「絶対」などの断定・誇大表現は使わない。収入は条件付きで書く。
4. 自社講座を比較・推奨する箇所には `pw_operator_note()` を置く。
5. 確認が取れていない事実・数値・本人発言は `pw_tbc_start()` で囲む。新しいキーを使ったら `pw-data.php` の `confirm` に追加する。
6. メンター・修了生の発言は、本人了承済みのもの（既存サイト・仕様書に掲載済みのもの）だけを使い、作らない。
