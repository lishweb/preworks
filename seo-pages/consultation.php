<?php
/**
 * S04 無料オンライン相談  /consultation/
 * 主要KW：オンライン秘書 講座 無料相談（サブ：オンライン秘書 説明会／preworks 無料相談／在宅ワーク 相談）
 * 検索意図：相談で何を聞かれるか、勧誘されないかを確認したい
 * 全ページのCTAの着地点。LINEボタンは data-pw-cta（top / mid / bottom / final）で計測（line_click、page_path=/consultation/）
 * title・description・H1・パンくずは inc/pw-pages.php の 'consultation'
 */
if ( ! defined( 'ABSPATH' ) ) exit;
pw_page_start();

/** 要確認ブロックを文字列で返す（ステップ説明・FAQの回答の中で使う） */
$tbc = function( $key, $html ) { ob_start(); pw_tbc_start( $key ); echo $html; pw_tbc_end(); return ob_get_clean(); };
$minutes = pw( 'consult.minutes' );
?>

<p>preworksの無料オンライン相談では、未経験からオンライン秘書を始めた方の事例をもとに、今のあなたが何から始めればよいのかを一緒に整理します。受講を決めていない段階や、在宅ワークに興味を持ち始めたばかりの方も、お気軽にご相談ください。</p>
<p style="text-align:center;margin:0 0 40px;"><?php echo pw_line_btn( 'LINEで無料相談を予約する', 'top' ); ?></p>

<h2>無料相談でお話しすること</h2>
<?php pw_box( '', array(
    '<strong>今の状況とこれからの希望</strong>：お子さんの年齢、今のお仕事、在宅で働きたい理由、目標の収入など。',
    '<strong>オンライン秘書という仕事の実際</strong>：どんな業務があるか、どれくらいの時間で、どれくらいの報酬になるか。修了後の働き方は' . pw_a( 'career', '修了後の働き方とサポート' ) . 'でも紹介しています。',
    '<strong>あなたの経験の活かし方</strong>：これまでの事務・接客・育児の経験が、どの業務で強みになるか。',
    '<strong>学習の進め方とプラン</strong>：生活リズムに合わせた学習ペースと、合うプランのご提案。各プランの内容は' . pw_a( 'price', '料金・プラン' ) . 'でご確認いただけます。',
    '<strong>気になることへの回答</strong>：費用、両立、パソコンスキルなど、何でもご質問ください。',
), 'num' ); ?>

<h2>当日の流れ</h2>
<?php pw_steps( array(
    array( 't' => 'LINE登録（目安：1分）', 'd' => '<p>公式LINEに友だち追加</p>' ),
    array( 't' => '日程の予約（目安：3分）', 'd' => '<p>LINEのメニューから希望の日時を選択</p>' ),
    array( 't' => 'オンライン相談', 'd' => '<p>オンラインでお話しします。</p>'
        . $tbc( 'consult_time', '<p>Zoom（またはGoogle Meet）でお話しします。所要時間の目安：' . ( $minutes ? esc_html( pw_v( 'consult.minutes' ) ) . '分程度' : '【ここに掲載：所要時間（例：45〜60分）】' ) . '</p>' )
        . $tbc( 'consult_style', '<p>カメラオフでも参加OKです。</p>' ) ),
    array( 't' => 'ご検討', 'd' => '<p>その場で決める必要はありません。持ち帰ってご家族と相談してください。</p>' ),
) ); ?>
<?php pw_tbc_start( 'consult_style' ); ?>
<p>相談の担当：【ここに掲載：担当者（現役オンライン秘書のメンター／スタッフなど）】</p>
<?php pw_tbc_end(); ?>

<?php pw_tbc_start( 'gift_delivery' ); ?>
<h2>無料相談に参加した方への特典</h2>
<p>無料相談に参加した方限定で、「在宅ワークに役立つ3大特典」をプレゼントしています。</p>
<?php
$rows = array();
foreach ( pw( 'gifts' ) as $i => $g ) $rows[] = array( sprintf( '特典%02d', $i + 1 ), esc_html( $g ) );
pw_table( array( '特典', '内容' ), $rows, array( 'first_col_th' => true ) );
?>
<?php pw_tbc_end(); ?>

<h2>こんな方におすすめです</h2>
<?php pw_box( '', array(
    '在宅ワークに興味はあるけれど、何から始めればいいか分からない',
    '自分の経歴でもオンライン秘書になれるのか知りたい',
    '子育てや今の仕事と両立できるか不安',
    'ほかの講座や在宅ワークの方法と比べてから決めたい',
), 'list' ); ?>

<?php pw_faq( array(
    array( 'q' => '相談したら必ず申し込まないといけませんか？', 'a' => 'いいえ。相談だけでも大丈夫です。' . $tbc( 'consult_honest', 'お話を伺ったうえで、preworksが合わないと判断した場合は正直にお伝えします。' ) ),
), '無料相談のよくある質問' ); ?>
<?php pw_tbc_start( 'consult_style' ); ?>
<?php pw_faq( array(
    array( 'q' => '子どもが近くにいても参加できますか？', 'a' => 'はい。お子さんの声が入っても問題ありません。カメラオフでの参加も可能です。' ),
), '' ); ?>
<?php pw_tbc_end(); ?>
<?php pw_faq( array(
    array( 'q' => 'パソコンがなくても相談できますか？', 'a' => 'はい。スマートフォンからでもご参加いただけます。' ),
    array( 'q' => '相談の日程を変更したいです。', 'a' => 'LINEのメッセージからご連絡ください。' ),
), '' ); ?>

<p style="text-align:center;margin:36px 0 40px;"><?php echo pw_line_btn( 'LINEで無料相談を予約する', 'bottom' ); ?></p>

<?php pw_tbc_start( 'contact_consult' ); ?>
<h2>LINEを使わずに相談したい方へ</h2>
<p>LINEをお使いでない方は、<?php pw_link( 'contact', 'お問い合わせフォーム' ); ?>から「無料相談希望」とご連絡ください。</p>
<?php pw_tbc_end(); ?>

<?php pw_related( array( 'program', 'price', 'career', 'voice', 'faq' ) ); ?>

<?php pw_page_end(); ?>
