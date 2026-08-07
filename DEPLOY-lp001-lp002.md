# 広告用LP(lp001/lp002) デプロイ手順

対象: https://preworks.jp/ (テーマ v1.0.4 稼働中) ／ 作成日: 2026-08-07

## 0. 事前バックアップ(必須)

上書きする既存3ファイルを、FTP等で必ずローカルにダウンロードして退避する:

- `wp-content/themes/preworks/functions.php`
- `wp-content/themes/preworks/header.php`
- `wp-content/themes/preworks/footer.php`

※万一表示が崩れた場合は、この3ファイルを書き戻せば元に戻ります(新規追加ファイルは残っていても無害)。

## 1. ファイルアップロード(⚠️ 必ずこの順序で)

このフォルダ内の `preworks/` 以下を、本番の `wp-content/themes/preworks/` に同じパス構成でアップロードする。

**⚠️ 重要: header.php / footer.php は functions.php に新設した関数を呼ぶため、順序を誤ると全ページが一時的にエラー(真っ白)になります。必ず以下の順で1つずつ:**

| 順序 | ファイル | 種別 |
|---|---|---|
| 1 | `functions.php` | 上書き(LP用ヘルパー・スクリプト出力・noindexを追加。先行アップロードは無害) |
| 2 | `template-parts/lp-body.php` | 新規(`template-parts` フォルダごと作成) |
| 3 | `page-lp001.php` | 新規 |
| 4 | `page-lp002.php` | 新規 |
| 5 | `header.php` | 上書き(wp_body_open追加・ナビLP出し分け・LINEボタン動的化) |
| 6 | `footer.php` | 上書き(ナビLP出し分け・LINEボタン動的化) |

アップロード完了直後にTOP(https://preworks.jp/)を1回開き、正常表示を確認してから次の手順へ。

## 2. WordPress管理画面で固定ページを作成(2ページ)

固定ページ > 新規追加 で以下を作成し、**公開**する。

| 項目 | ① | ② |
|---|---|---|
| タイトル | オンライン秘書育成プログラム preworks｜未経験から最短2ヵ月でオンライン秘書デビュー | 同左 |
| **スラッグ(URL)** | **lp001** (厳守。lp001-2等になったら既存スラッグと重複していないか確認) | **lp002** |
| 本文 | 空でOK(テンプレート page-lp001.php / page-lp002.php が自動適用される) | 空でOK |

※タイトルは `<title>` タグと検索結果に出るため、TOPと同等の訴求文言を推奨(自由に変更可)。
※親ページは設定しない(URLが /親/lp001/ になってしまうため)。

## 3. 動作確認チェックリスト

### lp001 (https://preworks.jp/lp001/)
- [ ] 200で表示され、TOPと同じ見た目(PC/スマホ)
- [ ] LINEボタン**5箇所すべて**(ヘッダー右上・ヒーロー・3大特典・最終CTA・フッター)のリンク先が `trackingId=7f9a24e4-c8b4-4c2a-a979-4a20a0742fef` を含む liff.line.me のURL
- [ ] ページのHTMLソースの `<body>` 直後に `append-ad-params.js` のscriptタグがある
- [ ] ヘッダー/フッターのナビ(preworksについて 等)をクリックすると**同一ページ内**でスクロールする(TOPへ移動しない)
- [ ] HTMLソースに `<meta name="robots" content="noindex,nofollow">` がある

### lp002 (https://preworks.jp/lp002/)
- [ ] 上記と同様(trackingIdは `146fdd6d-acd4-401e-82d9-309ea95db53b`)

### デグレ確認(重要)
- [ ] TOP(https://preworks.jp/)のLINEボタンが**従来どおり** sub.online-assistant.biz のURLのまま
- [ ] TOPのナビが従来どおり動作する
- [ ] TOPのHTMLソースに append-ad-params.js が**入っていない**こと、noindexが**入っていない**こと
- [ ] /privacy/ /terms/ /legal/ が正常表示
- [ ] TOPの `<body>` 直後にGTMの `<noscript>` ブロックが新たに出る場合があるが**正常**(これまでテーマがwp_body_open未対応でGTM4WPプラグインのnoscript補完が出ていなかっただけ。GTM推奨構成が完成する良性の変化)

### 計測の通し確認(推奨)
- [ ] スマホ実機で `https://preworks.jp/lp001/?gclid=test123` にアクセス → LINEボタンをタップ → LIFFが開き友だち追加(@034mfpdk)まで進めることを確認
- [ ] 計測ツール側のダッシュボードで trackingId 別に記録されることを確認(①と②を区別できるか)

## 4. 広告入稿時の注意

- 最終ページURLは末尾スラッシュ付きを推奨: `https://preworks.jp/lp001/` `https://preworks.jp/lp002/`
  (スラッシュ無しでもWPが301リダイレクトし、gclid等のパラメータは引き継がれるが、1ホップ無駄になるため)

## 5. 運用メモ

- **「lp001」「lp002」というタイトルの固定ページを他に作らないこと**(WPの is_page() 判定はスラッグだけでなくタイトルにも一致するため、同名タイトルの別ページが誤ってLP扱い=計測URL化・noindex化される)
- LP判定はスラッグ基準のため、lp001/lp002ページのスラッグは今後も変更しないこと

## 6. 補足(任意・後日でOK)

- All in One SEO のページ設定でも lp001/lp002 を noindex にしておくと二重に安全(コード側で既にnoindex出力済みのため必須ではない)
- sitemap.xml に lp001/lp002 が載る場合は AIOSEO のサイトマップ設定から除外
- **v1.0.6(GitHubのzip版)への反映**: main修復担当がv1.0.6をデプロイする際、本変更(上記6ファイル)を必ず移植すること。v1.0.6はheader/footerのナビが下層ページリンクに変わっているため、ナビ出し分け部分は「LP時はアンカー、通常時は下層URL」に読み替えて適用する
