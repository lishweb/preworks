# preworks テーマ v2.0.0 実装手順書

このテーマで、preworks.jp は次の構成になります。

| 種類 | 中身 | 書く・直す人 | 置き場所 |
|---|---|---|---|
| **固定ページ**（サービスのページ） | TOP・カリキュラム・料金・無料相談・修了後の働き方・修了生の声・メンター・FAQ・特典・属性別3本・診断 | エンジニア（Claudeが直したファイルを反映でも可） | テーマ内 `seo-pages/` ほか |
| **オンライン秘書ガイド**（/guide/） | コラムのうち「ガイドに掲載」にチェックした記事を、セクション別に並べる目次ページ | 目次ページはテーマ。載せる記事の選定はSEOチーム | 固定ページ `guide` |
| **コラム**（/blog/） | SEO記事チームが制作する記事。主要KWの柱記事はガイドにも掲載する | SEO記事チーム（管理画面） | 通常の投稿 |

固定ページの本文・title・description・パンくず・構造化データはテーマに組み込み済みです。ガイドは記事を持たず、コラムを「まとめ直して見せる」だけなので、記事のURLは変わりません。

**「preworksについて」（/about/）は作成済みの既存ページをそのまま使います。** このテーマには About ページを含めていません（監修体制などの重複を避けるため）。ヘッダー・フッター・TOPの「preworksについて詳しく見る」・FAQのリンクは、スラッグ `about` の固定ページが公開されていれば自動で /about/ に向き、未公開の間はTOPの #about へ戻ります。既存のAboutページのスラッグが `about` 以外の場合は、スラッグを `about` に合わせてください。


---

## 1. 事前準備（必ず）

1. Xserver のバックアップ、または All-in-One WP Migration 等でサイト全体を保存する。
2. 現在の `wp-content/themes/preworks/` を FTP でダウンロードして保管する（切り戻し用）。

## 2. テーマの差し替え

1. 同梱の `preworks/` フォルダで、サーバーの `wp-content/themes/preworks/` を上書きアップロードする。
   - **`page-faq.php` はサーバーから削除する**（/faq/ は `seo-pages/faq.php` に置き換え済み）。
   - テーマを「有効化し直す」ことはしない（有効化時の初期処理でパーマリンク設定が変わるため）。
   - 2026-10-01 以降にテーマを独自に変更している場合は、「8. 既存ファイルの変更点」の11ファイルだけ差分を合わせる。それ以外は追加ファイルなので衝突しない。
2. TOP・/faq/・/company/ が表示されることを確認する。

## 3. 固定ページの作成（ボタン1つ）

1. 管理画面 → **ツール → SEOページ管理** を開く。
2. 「**未作成のページを『下書き』で一括作成**」を押す。登録表どおりのスラッグで、本文空の固定ページが下書きで作られる（既にある faq などはスキップ。既存の /about/ には触れない）。
3. 表の「表示」から下書きをプレビューし、問題なければ各ページの編集画面で「公開」を押す。**親ページは設定しない**（URLは全て1階層）。
4. /blog/（コラム一覧）は「blog」固定ページを 設定 → 表示設定 →「投稿ページ」に指定（設定済みなら不要）。/news/ は作業なし。

公開順は自由です。未公開ページへのリンクは自動で文字だけになり、公開した時点でヘッダー・フッター・本文のリンクが切り替わります。

| # | ページ | URL | 本文ファイル |
|---|---|---|---|
| S01 | TOP（改修） | `/` | front-page.php |
| S02 | 料金・プラン | `/price/` | seo-pages/price.php |
| S03 | カリキュラム | `/program/` | seo-pages/program.php |
| S04 | 無料オンライン相談 | `/consultation/` | seo-pages/consultation.php |
| S09 | 修了後の働き方とサポート（URAKATAの説明を含む） | `/career/` | seo-pages/career.php |
| S11 | 修了生の声 | `/voice/` | archive-voice.php |
| S12 | メンター紹介 | `/mentor/` | archive-mentor.php |
| A01 | 子育て中のママへ | `/for-mama/` | seo-pages/for-mama.php |
| A02 | 40代・50代・ブランクのある方へ | `/for-40s-50s/` | seo-pages/for-40s-50s.php |
| A03 | 会社員・副業の方へ | `/for-side-job/` | seo-pages/for-side-job.php |
| A10 | 向いている人診断 | `/check/` | seo-pages/check.php |
| A12 | よくある質問 | `/faq/` | seo-pages/faq.php |
| B05 | 特典 | `/gift/` | seo-pages/gift.php |
| G00 | オンライン秘書ガイド（目次） | `/guide/` | seo-pages/guide.php |
| B06 | コラム一覧 | `/blog/` | home.php |
| B07 | お知らせ | `/news/` | archive-news.php |

## 4. オンライン秘書ガイドの運用（SEO記事チーム）

- /guide/ は手順3で作られる固定ページ（スラッグ guide）です。ガイドに掲載した記事が1本もない間は「準備中」と表示され、フッターにも出ません。
- コラムの編集画面の右側「**オンライン秘書ガイド**」欄で設定します。
  - **ガイドに掲載する**（チェック）
  - **セクション**：オンライン秘書を知る／オンライン秘書になるには／お金と働き方／業務別の仕事内容／用語集
  - **並び順**（小さい順）
- 掲載した記事は、URLはそのままで次のように表示が変わります。
  - パンくず「トップ ／ オンライン秘書ガイド ／ 記事」（パンくずの構造化データも同じ）
  - 見出し上に「Guide ／ セクション名」、最終更新日
  - 本文の後に、同じセクションのガイド記事一覧と、目次ページへのリンク
  - 記事内の [LINE] ボタンの一文がガイド用の文言になる
- /blog/（コラム一覧）では、ガイドに掲載した記事に「ガイド」の目印が付きます。投稿一覧の管理画面にも「ガイド」列が出ます。
- セクションを増やすときは `inc/pw-guide.php` の `pw_guide_sections()` に1行追加します。
- ガイドに載せる柱記事の主要KW（例：オンライン秘書とは／なるには／副業／資格／収入）は、ほかのコラムで重ねて狙わないようにします。

## 5. 要確認事項（固定ページ）

`inc/pw-data.php` の `confirm` で、確認が取れたキーを `'ok' => true` にすると、その部分が一般公開される（ツール → SEOページ管理 に一覧あり）。

| キー | 状態 | 確認する内容 | 使っているページ |
|---|---|---|---|
| `refund` | 未確認 | 返金・クーリングオフ・受講期間延長の規定（利用規約 第7条「サポート期間6ヶ月」と現行プランの食い違いも確認） | /price/, /program/ |
| `plan_common` | 未確認 | 面接シミュレーション・URAKATAプレ稼働が全プラン共通か | /price/ |
| `plan_change` | 未確認 | 受講中のプラン変更（アップグレード）の可否と差額の扱い | /price/ |
| `bank_fee` | 未確認 | 銀行振込手数料の負担者 | /price/ |
| `paid_tools` | 未確認 | 受講に有料ツールが不要か（無料で使える範囲で学ぶ前提でよいか） | /price/ |
| `kyufukin` | 未確認 | 教育訓練給付制度など公的給付の対象可否（対象外なら「対象講座ではありません」と明記） | /price/ |
| `study_time` | 未確認 | 学習時間の目安（FAQ「週2.5〜6時間」とTOP「2ヶ月プランで1日2時間×週5日×7週間」の統一） | /for-40s-50s/, /for-mama/, /for-side-job/, /program/ |
| `study_slots` | 未確認 | 受講生の学習時間帯の実例（お昼寝中・寝かしつけ後・送迎の待ち時間など） | /program/ |
| `video_length` | 未確認 | 動画講座1本あたりの長さ | /program/ |
| `pc_spec` | 未確認 | パソコンの推奨スペック（発売からの年数・メモリ） | /program/ |
| `mentor_session_time` | 未確認 | メンター面談の実施時間帯（平日夜・土日に対応できるか） | /for-side-job/ |
| `support_detail` | 未確認 | 案件獲得伴走サポートの具体的な内容 | /career/, /faq/, /price/, /program/ |
| `consult_time` | 未確認 | 無料相談の所要時間・実施ツール（所要時間は consult.minutes に数値を入れる） | /consultation/ |
| `consult_style` | 未確認 | 無料相談の担当者（メンターか営業担当か）・カメラオフ可否・子ども同席可否 | /consultation/ |
| `consult_honest` | 未確認 | 無料相談FAQ「合わない場合は正直に伝える」の運用可否 | /consultation/ |
| `contact_consult` | 未確認 | /contact/ フォームで無料相談の受付が可能か | /consultation/ |
| `gift_delivery` | 未確認 | 特典の受け取り条件（LINE登録のみ／無料相談参加者のみ）。gift_condition に line / consult を入れる | /consultation/, /gift/ |
| `gift_contents` | 未確認 | 3大特典の中身（形式・ボリューム・目次・作成者） | /gift/ |
| `urakata_rate` | 確認済み | URAKATAの時給の目安（参考：1,200〜1,400円。2026-10-01確認） | /career/ |
| `urakata_hours` | 未確認 | URAKATAでの平均的な週稼働時間と月額報酬の例 | /career/ |
| `urakata_interview` | 未確認 | URAKATA面接を受けられる時期・選考内容・不通過時のフォロー | /career/ |
| `urakata_recruit` | 未確認 | URAKATAの一般募集の有無・応募条件・最低稼働時間 | /career/ |
| `urakata_sidejob` | 未確認 | URAKATAで会社員が副業として稼働できる条件（平日夜・土日のみの可否、最低稼働時間） | /for-side-job/ |
| `mentor_quotes` | 未確認 | メンター・修了生への取材内容（1日の時間割・体験談など）と掲載の本人了承 | /for-40s-50s/, /for-mama/, /for-side-job/ |
| `voice_card_info` | 未確認 | 修了生カードの「受講プラン」「現在の仕事」の掲載了承 | /voice/ |

値の入力場所（`inc/pw-data.php`）：
- URAKATAの時給の目安：`'urakata' => array( 'rate_min' => 1200, 'rate_max' => 1400 )`（参考値として career・faq・price などに表示）
- 無料相談の所要時間：`'consult' => array( 'minutes' => 45, … )`
- 特典の条件：`'gift_condition' => 'line'`（LINE登録のみ）または `'consult'`（無料相談参加者のみ）
- ページ別のLINE URL（UTAGEで発行できた場合）：`'line_urls' => array( 'price' => 'https://sub.preworks.jp/line/open/…' )`
- 実績数値・料金：`'stats'` / `'plans'` / `'entry_fee'` / `'job_definition'`

## 6. 計測（GTM：GTM-WKGV5R3N）

LINE ボタンのクリック時に dataLayer へ `event: 'line_click'`（`page_path`、`cta_position`）を送る（全ページ・ガイド・コラム含む）。診断は `check_complete`（`check_score`、`check_result`）。GTM で「カスタムイベント line_click」をトリガーに GA4 イベントを作り、キーイベントに設定する。

## 7. 公開後チェック

- [ ] ログアウト状態で表示確認（PC・スマホ）。【要確認】や赤枠が出ていない
- [ ] `<title>` と description（固定ページは `inc/pw-pages.php`、ガイドは「ガイド設定」）
- [ ] リッチリザルトテストで FAQ・パンくずにエラーがない
- [ ] GTM プレビューで `line_click` が発火
- [ ] Search Console で URL 検査 → インデックス登録をリクエスト

## 8. 既存ファイルの変更点（v1.0.27 → v2.0.0）

| ファイル | 変更内容 |
|---|---|
| `functions.php` | 新モジュールの読み込み（先頭4行）／description・OGPを登録表から出す／Organization の構造化データを「LISH株式会社」に統一／修了生の声の入力欄に「受講プラン」「現在の仕事」を追加／かんたん入力：`/` で始まるサイト内リンク、`【要確認】` 行の非表示、ガイド用のLINEボタン文言 |
| `header.php` | ヘッダーナビを「preworksについて／プログラム／料金／修了生の声／よくある質問」に（ページ公開までは従来のアンカーに自動フォールバック。広告LPは従来のまま） |
| `footer.php` | フッターを「講座について」「こんな方へ（＋オンライン秘書ガイド）」の2列に（公開済みのものだけ並ぶ。広告LPは従来のまま） |
| `front-page.php` | 各セクションに下層ページへの「詳しく見る」リンク（公開後に表示）／料金・実績数値を `pw-data.php` から出力／実績数値が0と表示される問題を修正／URAKATAの位置づけ・獲得率の表記・FAQの断定表現を修正／料金ブロックに `#plans` |
| `assets/js/main.js` | 実績数値のカウントアップを「最初から実数をHTMLに出し、画面に入ったときだけ0から数える」方式に変更 |
| `archive-voice.php` | 一覧ページの改修（H1・パンくず・導入文・CTA） |
| `archive-mentor.php` | 一覧ページの改修（H1・パンくず・導入文・CTA）／記事0件の間はTOPと同じ4名を表示 |
| `home.php` | コラム一覧（/blog/）の整備、ガイド掲載記事に「ガイド」の目印 |
| `single.php` | ガイドに掲載した記事のパンくず・ラベル・最終更新日・同じセクションの記事一覧 |
| `page-faq.php` | 削除（`seo-pages/faq.php` に置き換え） |
| `style.css` | バージョン表記のみ |

追加ファイル：`inc/`（pw-data / pw-pages / pw-core / pw-guide）、`seo-pages/`、`archive-news.php`、`assets/css/pw-pages.css`、`assets/js/pw-pages.js`、本書。

広告用LP（page-lp001.php / page-lp002.php / template-parts/lp-body.php）は変更していない。TOPで修正した文言（URAKATA面接 → 仕事開始の流れ、FAQ「確実に結果を出してもらいます」、獲得率の表記）がLPには残っているので、必要なら別途修正する。

## 9. 切り戻し

旧テーマフォルダで上書きし、追加ファイルを削除する。作成した固定ページは下書きに戻せば非公開になる。
