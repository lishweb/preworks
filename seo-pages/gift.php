<?php
/**
 * B05 特典ダウンロード  /gift/
 * 主要KW：在宅ワーク 始め方 チェックリスト
 * title・description・H1・パンくずは inc/pw-pages.php の 'gift'
 *
 * 方針（SITE_PAGES_BRIEF.md 2026-10-01）：特典の紹介と受け取り方に絞る（一般論のチェックポイント等は削除）。
 *
 * ■ 受け取り条件が未確定（gift_delivery）
 *   現行サイト（TOP・LP）：「無料相談に参加した方限定」／サイト設計プラン：LINE登録だけで受け取れる
 *   → 条件を書く箇所はすべて pw_tbc_start('gift_delivery') の中。ゲート外は両立する表現だけにしている。
 *   → pw-data.php の 'gift_condition' に 'line' | 'consult' を入れると、ゲート内の文面が自動で切り替わる
 *     （未設定なら管理者には両案が並んで見える）。
 * ■ 特典の中身（形式・ページ数・目次）は未確認（gift_contents）。ゲート外はTOPの記載（特典名）の範囲だけ。
 */
if ( ! defined( 'ABSPATH' ) ) exit;
pw_page_start( array(
    'lead' => 'preworksでは、これから在宅ワークを始めたい方に向けて3つの特典をご用意しています。特典の内容と受け取り方をご紹介します。',
) );

$gifts = pw( 'gifts', array() );
$cond  = pw( 'gift_condition' );   // 'line' | 'consult' | null（未確定）
$img   = array( 'benefit-1.jpg', 'benefit-2.jpg', 'benefit-3.jpg' );
$for   = array(
    '在宅ワークに興味はあるけれど、何から準備すればよいかわからない方',
    '在宅ワークを始めるまでの道のりを、順番に整理しておきたい方',
    '家事や育児と両立しながら、働く時間をどう確保するか悩んでいる方',
);
$btn = function( $pos ) {
    echo '<p style="text-align:center;margin:28px 0;">' . pw_line_btn( 'LINEで3大特典を受け取る', $pos ) . '</p>';
};
?>

<h2>在宅ワークに役立つ3大特典</h2>
<div class="bgrid">
<?php foreach ( $gifts as $i => $g ) : ?>
  <div class="bitem"><span class="ribbon">特典<small><?php echo sprintf( '%02d', $i + 1 ); ?></small></span><div class="bic" style="background-image:url('<?php echo esc_url( PREWORKS_IMG . '/' . $img[ $i ] ); ?>')" role="img" aria-label="<?php echo esc_attr( $g ); ?>"></div><p><?php echo esc_html( $g ); ?></p></div>
<?php endforeach; ?>
</div>

<?php foreach ( $gifts as $i => $g ) : ?>
<h3>特典<?php echo sprintf( '%02d', $i + 1 ); ?>　<?php echo esc_html( $g ); ?></h3>
<p><strong>こんな方におすすめ：</strong><?php echo esc_html( $for[ $i ] ); ?></p>
<?php pw_tbc_start( 'gift_contents' ); ?>
<?php pw_table(
    array( '項目', '内容' ),
    array(
        array( '形式', '【PDF／スプレッドシート／動画などの形式を記載：特典ファイルで確認する項目】' ),
        array( 'ボリューム', '【ページ数・項目数・動画の長さなどを記載：特典ファイルで確認する項目】' ),
        array( '主な内容', '【目次（見出し）を3〜5項目で掲載：特典ファイルで確認する項目】' ),
        array( '作成・監修', '【作成者・監修メンター名を記載：社内で確認する項目】' ),
    ),
    array( 'first_col_th' => true )
); ?>
<?php pw_tbc_end(); ?>
<?php endforeach; ?>

<?php $btn( 'gift-list' ); ?>

<h2>特典の受け取り方</h2>
<p>3大特典は、preworks公式LINEからお受け取りいただけます。LINEの友だち追加は無料です。</p>
<?php pw_steps( array(
    array( 't' => 'preworks公式LINEを友だち追加する', 'd' => '<p>このページの「LINEで3大特典を受け取る」ボタンから、preworks公式LINEを友だち追加してください。</p>' ),
    array( 't' => 'LINEのご案内に沿って進む', 'd' => '<p>友だち追加後に届くメッセージで、特典の受け取り方と無料オンライン相談についてご案内します。</p>' ),
    array( 't' => '特典を受け取る', 'd' => '<p>ご案内に沿って、3大特典をお受け取りください。</p>' ),
) ); ?>

<?php pw_tbc_start( 'gift_delivery' ); ?>
<?php if ( $cond !== 'consult' ) : ?>
<?php if ( ! $cond ) pw_note( '【案A：LINE登録だけで受け取れる場合の文面】（pw-data.php の \'gift_condition\' を \'line\' にすると案Aだけが残ります）' ); ?>
<?php pw_callout( '<p><strong>受け取り条件</strong>：preworks公式LINEに友だち追加いただいた方全員にお届けしています。友だち追加後、【自動メッセージで受け取りURLが届く／キーワードを送信する など、受け取り手順を記載：UTAGEの設定で確認する項目】。受講や無料相談へのお申し込みは必要ありません。</p>' ); ?>
<?php endif; ?>
<?php if ( $cond !== 'line' ) : ?>
<?php if ( ! $cond ) pw_note( '【案B：無料相談に参加した方限定の場合の文面（現行TOP・LPの表記）】（\'gift_condition\' を \'consult\' にすると案Bだけが残ります）' ); ?>
<?php pw_callout( '<p><strong>受け取り条件</strong>：3大特典は、無料オンライン相談に参加した方限定のプレゼントです。LINEから無料相談をご予約いただき、相談にご参加いただいた方に【相談の終了後にLINEでお送りする など、受け取り手順を記載：運用で確認する項目】。無料相談は受講を前提としない相談だけでも大丈夫です。</p>' ); ?>
<?php endif; ?>
<?php pw_tbc_end(); ?>

<h2>あわせて、無料オンライン相談もご利用いただけます</h2>
<p>preworksの無料オンライン相談では、未経験から在宅ワークを始めた方の事例をもとに、あなたの状況に合った始め方を一緒に整理します。講座の内容やプランの違いについてもお答えします。受講を前提としない相談だけでも大丈夫です。相談の流れは<?php pw_link( 'consultation', '無料オンライン相談のご案内' ); ?>をご覧ください。</p>

<?php pw_faq( array(
    array( 'q' => '特典を受け取るのにお金はかかりますか？', 'a' => 'いいえ、費用はかかりません。LINEの友だち追加も無料です。' ),
    array( 'q' => '無料相談を受けたら、講座に申し込まないといけませんか？', 'a' => 'いいえ、受講を前提としない相談だけでも大丈夫です。今の状況や不安をうかがいながら、あなたに合った始め方を一緒に整理します。' ),
    array( 'q' => '特典はどんな人向けの内容ですか？', 'a' => 'これから在宅ワークを始めたい方に向けた内容です。準備の確認、始めるまでの道のり、家事や育児と両立するための時間の使い方を、3つの特典でまとめています。' ),
) ); ?>

<?php pw_tbc_start( 'gift_delivery' ); ?>
<?php pw_faq( array(
    array( 'q' => 'LINEに登録するだけで特典を受け取れますか？', 'a' => '【受け取り条件の確定後に記載：案A「はい、友だち追加いただいた方全員にお届けしています」／案B「3大特典は無料相談に参加した方限定です。LINEから無料相談をご予約ください」】' ),
), '' ); ?>
<?php pw_tbc_end(); ?>

<?php pw_cta( array(
    'h'   => '3大特典は、preworks公式LINEからお受け取りいただけます',
    'p'   => '在宅ワークの始め方に迷っている方は、まずはLINEで気軽にご相談ください。受講を前提としない相談だけでも大丈夫です。',
    'btn' => 'LINEで3大特典を受け取る',
    'pos' => 'gift-bottom',
) ); ?>

<?php pw_related( array( 'consultation', 'check', 'program', 'for-mama' ) ); ?>

<?php pw_page_end(); ?>
