<?php
/**
 * S03 カリキュラム  /program/
 * 主要KW：オンライン秘書 講座 カリキュラム（サブ：オンライン秘書 講座 内容／オンライン秘書 勉強／オンライン秘書 学ぶこと）
 * 検索意図：講座で何が身につくのか、未経験でもついていけるのかを知りたい
 * title・description・H1・パンくず・Course構造化データは inc/pw-pages.php の 'program'
 * STEP1〜7は TOP（front-page.php #program）の details.wkitem と同じ見た目・同じ講座スライド画像
 */
if ( ! defined( 'ABSPATH' ) ) exit;
pw_page_start();

/** 要確認ブロックを文字列で返す（リスト項目の中などで使う） */
$tbc = function( $key, $html ) { ob_start(); pw_tbc_start( $key ); echo $html; pw_tbc_end(); return ob_get_clean(); };

/* STEP1〜7（本文は仕様書の確定原稿、画像とキャプションは TOP と同じ） */
$steps = array(
    array(
        't' => '自己理解・オンライン秘書の全体像',
        'd' => 'まず「オンライン秘書とはどんな仕事か」と「自分の強みは何か」を整理します。これまでの事務・接客・育児の経験を、仕事で使えるスキルとして言葉にするところから始めます。',
        'items' => array( '講座オリエンテーション＋オンライン秘書の仕事理解', 'スキルの言語化と強みの発見', '在宅ワークの現実とマネープラン', '理想の働き方を描く＋不安の言語化', 'フリーランスの働き方・マインドセット・不安解消', 'じぶんを守る法務基礎' ),
        'shots' => array( 'スキルの言語化と強みの発見', 'じぶんを守る法務基礎' ),
    ),
    array(
        't' => 'パソコン・コミュニケーション基礎',
        'd' => '在宅で働くための環境と、テキスト中心のやり取りの基本を固めます。',
        'items' => array( 'パソコン基礎の総点検', '在宅ワーク環境整備＋ビジネス用語マスター', '情報セキュリティとコンプライアンス基礎', 'ビジネスコミュニケーションの基礎＋インシビリティ理解', 'オンライン秘書の業務フロー＋要件定義の基礎' ),
        'shots' => array( 'パソコン知識の総点検', '在宅ワーク環境整備＋ビジネス用語マスター' ),
    ),
    array(
        't' => 'AI活用・Googleツール基礎',
        'd' => '今の現場で欠かせないAIとGoogleツールを、実務の使い方で学びます。',
        'items' => array( 'プロの作業品質（スピード×正確性×ユーザビリティ）', 'Chrome完全活用＋Web基礎知識', 'AI活用マスター（ChatGPT・Gemini実践）', 'スプレッドシート実践マスター', 'Google Workspace完全理解' ),
        'shots' => array( 'AI活用マスター - ChatGPT & Gemini実践', 'Google Workspace完全理解' ),
    ),
    array(
        't' => 'バックオフィス業務の基礎',
        'd' => 'オンライン秘書に依頼される業務を分野ごとに学び、自分が得意な領域を見つけます。',
        'items' => array( 'オンライン秘書の業務全体像', '秘書業務＋経理業務の実践フロー', '労務・総務・採用業務の実践フロー', '営業事務・デザイン業務の実践フロー', 'Web業務の実践フロー' ),
        'shots' => array( 'オンライン秘書の業務全体像', 'Web業務の実践フロー' ),
    ),
    array(
        't' => 'マニュアル作成・ツール実践',
        'd' => 'チームで働くためのツールと、仕事を引き継げる形にする力を身につけます。',
        'items' => array( 'Notion基礎', 'Chatwork＋Slack／LINE WORKS実践', 'マニュアル作成実践', 'Zoom／Google Meet＋議事録作成', '総合演習（複合業務シミュレーション）' ),
        'shots' => array( 'Zoom/Google Meet ＋ 議事録作成', '総合演習 - 複合業務シミュレーション' ),
    ),
    array(
        't' => '実務シミュレーション強化',
        'd' => 'デザインや提案、トラブル対応など、任される仕事の幅を広げます。',
        'items' => array( 'Canva実践（デザインの基礎からSNS投稿・資料作成まで）', '要件定義力と提案力', 'トラブル対応実践（ミス・クレーム・予期せぬ事態）', 'チーム協業（他ワーカーへの指示出し）', '総合実務シミュレーション' ),
        'shots' => array( 'Canva実践 - デザインの基礎からSNS投稿・資料作成まで', '総合実務シミュレーション' ),
    ),
    array(
        't' => 'クライアントコミュニケーション実践＋案件獲得',
        'd' => 'クライアントに選ばれ、信頼されて続けるための力を身につけ、実際の面接に臨みます。URAKATAの面接だけでなく、修了後に自分で案件を獲得するときにも使う内容です。',
        'items' => array( 'クライアント理解（経営者の視点を知る）', '報連相の深化＋期待値調整の技術', 'チーム協業の強化＋ディレクション実践', '案件獲得講座（選ばれるための戦略）', 'エキスパート講座＋面接対策（面接シミュレーション）' ),
        'shots' => array( '案件獲得講座 - 選ばれるための戦略', 'エキスパート講座＋面接対策' ),
        'tags' => array( '動画講座', '実践ワーク', '面接シミュレーション' ),
    ),
);
?>

<p>preworksのカリキュラムは、運営会社のオンライン秘書サービス「URAKATA」で実際に依頼される業務をもとに、7つのSTEPで構成しています。「動画で学ぶ → 実際の案件と同じ流れで課題を提出する → メンターからフィードバックを受ける」を繰り返し、修了時には案件をひとりで回すための手順と、自分で案件を獲得する準備を身につけることを目指します。</p>

<h2>学び方の流れ</h2>
<?php pw_box( '', array(
    '<strong>動画講座で学ぶ</strong>：' . $tbc( 'video_length', '1講座15〜30分程度。' ) . 'スキマ時間に視聴でき、受講期間後も無期限で見返せます。',
    '<strong>実践課題を提出する</strong>：講座ごとに、実際の依頼に近い形式の課題を提出します。',
    '<strong>メンターに相談する</strong>：月2回の1on1面談と、期間中は質問し放題のチャットで、分からないことをその場で解消します。',
    '<strong>URAKATAで実務に挑戦する</strong>：受講中から、報酬のある実際の案件に挑戦できます（面接通過後）。本番の実務経験は、修了後に自分で案件を獲得するときの実績にもなります。',
), 'num' ); ?>

<h2>7つのSTEPと学ぶ内容</h2>
<div class="steplist">
<?php foreach ( $steps as $i => $s ) :
    $n    = $i + 1;
    $tags = isset( $s['tags'] ) ? $s['tags'] : array( '動画講座', '実践ワーク' );
    ?>
  <details class="wkitem"<?php echo $n === 1 ? ' open' : ''; ?>>
    <summary class="wkhead"><span class="wkno">STEP<b><?php echo $n; ?></b></span><h3 class="wktitle" style="margin:0;font-family:inherit;font-size:clamp(15px,1.8vw,18px);font-weight:800;line-height:1.5;"><?php echo esc_html( $s['t'] ); ?></h3><span class="wkchev" aria-hidden="true"></span></summary>
    <div class="wkbody">
      <div class="steptags"><?php foreach ( $tags as $tg ) echo '<span class="stag">' . esc_html( $tg ) . '</span>'; ?></div>
      <p><?php echo wp_kses_post( $s['d'] ); ?></p>
      <ul class="wkdays"><?php foreach ( $s['items'] as $it ) echo '<li>' . esc_html( $it ) . '</li>'; ?></ul>
      <div class="wkslides">
        <span class="wkslide-label">講座内容を一部紹介</span>
        <div class="wkshots">
          <?php foreach ( $s['shots'] as $k => $cap ) : ?>
          <figure><img src="<?php echo esc_url( PREWORKS_IMG . '/wk' . $n . '-' . ( $k + 1 ) . '.jpg' ); ?>" alt="<?php echo esc_attr( 'STEP' . $n . ' ' . $cap . ' の講座スライド' ); ?>" loading="lazy" decoding="async" width="1280" height="720"><figcaption><?php echo esc_html( $cap ); ?></figcaption></figure>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </details>
<?php endforeach; ?>
</div>

<h2>自分で案件を獲得するための学び</h2>
<p>preworksは、URAKATAで働くためだけの講座ではありません。修了後は、URAKATAで稼働する、URAKATAと並行して自分で案件を獲得する、URAKATAを使わず自分で獲得する、のどれを選んでもかまいません。どの働き方でも必要になる「自分で仕事を選び、獲得し、続ける」ための内容として、カリキュラムには次の講座とサポートがあります。</p>
<?php
$support_plans = implode( '・', array_map( function( $pl ) { return (int) $pl['months'] . 'ヶ月'; }, array_filter( pw( 'plans' ), function( $pl ) { return $pl['support']; } ) ) );
pw_table(
    array( '時期', '内容' ),
    array(
        array( 'STEP1', '「フリーランスの働き方・マインドセット・不安解消」「じぶんを守る法務基礎」' ),
        array( 'STEP7', '「クライアント理解（経営者の視点を知る）」「報連相の深化＋期待値調整の技術」「案件獲得講座（選ばれるための戦略）」「エキスパート講座＋面接対策」' ),
        array( '面接シミュレーション', '面接に向けて、受け答えを練習します' ),
        array( '案件獲得伴走サポート', esc_html( $support_plans ) . 'プランに含まれます。' . $tbc( 'support_detail', '【案件獲得伴走サポートの具体的な内容（回数・期間・対応範囲）を掲載】' ) ),
    ),
    array( 'first_col_th' => true )
);
?>
<p>修了後に選べる3つの働き方と、案件獲得のサポートは<?php pw_link( 'career', '修了後の働き方とサポート' ); ?>で紹介しています。</p>

<h2>修了生の声：学んだことが仕事でどう役立ったか</h2>
<?php pw_quote( '仕事で使うツールを学ぶ内容がすごく楽しかったです。今の仕事でもNotionのマニュアルを読んだり、内容を修正したりするので、本当に学んでおいてよかったと思っています。', 'Kさん／' . preg_replace( '/｜.*$/u', '', pw( 'voices.k.attr' ) ) ); ?>
<?php pw_quote( '講座でテキストコミュニケーションについて学んでいたので、仕事が始まってからもやり取りで特に困ることはありませんでした。', 'Kさん' ); ?>
<p><?php pw_link( 'voice/' . pw( 'voices.k.slug' ), 'Kさんのインタビュー全文' ); ?>もあわせてご覧ください。</p>

<?php pw_tbc_start( 'study_time' ); ?>
<h2>学習時間の目安</h2>
<?php pw_table(
    array( 'プラン', '学習時間の目安' ),
    array(
        array( '2ヶ月', '1日2時間×週5日（約7週間でカリキュラムを修了）' ),
        array( '3ヶ月・6ヶ月・12ヶ月', '週2.5〜6時間程度。生活に合わせてペースを調整' ),
    ),
    array( 'first_col_th' => true )
); ?>
<?php pw_tbc_start( 'study_slots' ); ?>
<p>育児中の方は、お子さんのお昼寝中や寝かしつけ後、送迎の待ち時間などに動画を視聴している方が多いです。</p>
<?php pw_tbc_end(); ?>
<p>プランごとの受講期間とサポート内容は、<?php pw_link( 'price', '料金・プランを見る' ); ?>からご確認いただけます。</p>
<?php pw_tbc_end(); ?>

<h2>受講に必要なもの</h2>
<ul>
  <li>パソコン（Windows／Mac）。スマートフォンやタブレットだけでは課題に取り組めません</li>
  <li>インターネット環境（オンライン面談ができる程度）</li>
  <li>Googleアカウント（無料で作成できます）</li>
  <li>メールの送受信やWebサイトの閲覧ができる程度のパソコン操作</li>
</ul>
<?php pw_tbc_start( 'pc_spec' ); ?>
<p>パソコンの推奨スペック：【ここに掲載：OSのバージョン・メモリ容量など、講座で確認している条件】</p>
<?php pw_tbc_end(); ?>

<?php pw_cta(); ?>

<?php pw_faq( array(
    array( 'q' => 'パソコンが苦手でもついていけますか？', 'a' => 'メールの送受信やWebサイトの閲覧ができれば大丈夫です。STEP2でパソコン基礎を総点検してから実務的な内容に進みます。' ),
    array( 'q' => '動画はいつまで見られますか？', 'a' => '受講期間が終わったあとも、無期限で視聴できます。' ),
) ); ?>
<?php pw_tbc_start( 'refund' ); ?>
<?php pw_faq( array(
    array( 'q' => '課題が終わらなかった場合はどうなりますか？', 'a' => '【ここに掲載：期間内に修了できなかった場合の扱い（受講期間の延長可否・費用）】' ),
), '' ); ?>
<?php pw_tbc_end(); ?>

<?php pw_related( array( 'price', 'career', 'mentor', 'consultation', 'faq' ) ); ?>

<?php pw_page_end(); ?>
