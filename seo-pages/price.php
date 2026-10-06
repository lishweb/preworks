<?php
/**
 * S02 料金・プラン  /price/
 * 主要KW：オンライン秘書 講座 料金（サブ：オンライン秘書 スクール 費用／オンライン秘書 講座 分割／preworks 料金）
 * 検索意図：総額と支払い方法を知りたい。高いか安いか判断したい
 * title・description・H1・パンくず・Course構造化データは inc/pw-pages.php の 'price'
 */
if ( ! defined( 'ABSPATH' ) ) exit;
pw_page_start();

$plans  = pw( 'plans' );
$fee    = (int) pw( 'entry_fee' );
$inst   = (int) pw( 'installments_max' );
$prices = wp_list_pluck( $plans, 'price' );
$min    = min( $prices );
$max    = max( $prices );
/** 要確認ブロックを文字列で返す（表のセル・FAQの回答の中で使う） */
$tbc    = function( $key, $html ) { ob_start(); pw_tbc_start( $key ); echo $html; pw_tbc_end(); return ob_get_clean(); };
/** 案件獲得伴走サポートがあるプラン（例：3ヶ月・6ヶ月・12ヶ月） */
$support_plans = implode( '・', array_map( function( $pl ) { return (int) $pl['months'] . 'ヶ月'; }, array_filter( $plans, function( $pl ) { return $pl['support']; } ) ) );
?>

<p>preworksの受講料は、メンターが伴走する期間に合わせて<?php echo esc_html( implode( '・', array_map( function( $pl ) { return (int) $pl['months'] . 'ヶ月'; }, $plans ) ) ); ?>の<?php echo count( $plans ); ?>プランからお選びいただけます。受講料は<?php echo pw_yen( $min ); ?>〜<?php echo pw_yen( $max ); ?>（税込）で、別途、入会金<?php echo pw_yen( $fee ); ?>（税込）が全プラン共通でかかります。クレジットカードなら最大<?php echo (int) $inst; ?>回の分割払いが可能です。</p>

<h2>プランと料金一覧</h2>
<?php pw_plans( array( 'note' => false ) ); ?>
<?php
$rows = array();
foreach ( $plans as $pl ) {
    $rows[] = array(
        esc_html( $pl['name'] ) . ( $pl['reco'] ? '（おすすめ）' : '' ),
        (int) $pl['months'] . 'ヶ月',
        pw_yen( $pl['price'] ),
        pw_yen( $pl['price'] + $fee ),
        '月々約' . pw_yen( $pl['monthly'] ),
    );
}
pw_table(
    array( 'プラン', 'メンター伴走期間', '受講料（税込）', '入会金込みの総額（税込）', $inst . '回分割の目安（受講料分）' ),
    $rows,
    array( 'first_col_th' => true )
);
?>
<?php pw_note( '※分割の目安は受講料を' . $inst . 'で割った金額です。分割手数料（お客様負担）と入会金は含みません。' ); ?>

<h2>プラン別のサポート内容</h2>
<?php pw_plan_table(); ?>
<?php pw_tbc_start( 'plan_common' ); ?>
<?php pw_note( '※面接シミュレーションとURAKATAプレ稼働（報酬あり）は、全プラン共通でご利用いただけます。' ); ?>
<?php pw_tbc_end(); ?>
<p>サポート内容のうち、修了後の仕事につながるものは次の3つです。URAKATAで働くことが前提ではなく、自分で案件を獲得する力を身につけることもpreworksのゴールです。</p>
<?php pw_box( '', array(
    '<strong>面接シミュレーション</strong>：面接に向けて、受け答えを練習します。',
    '<strong>URAKATAプレ稼働</strong>：面接に通過すると、受講中から報酬ありで本番の実務経験と実績をつくれます。修了後も、希望すればURAKATAで続けられます。報酬の目安（参考）は' . pw_urakata_rate_text() . 'で、業務内容や経験によって異なります。',
    '<strong>案件獲得伴走サポート</strong>（' . esc_html( $support_plans ) . 'プラン）：修了後の案件獲得に向けたサポートです。' . $tbc( 'support_detail', '【案件獲得伴走サポートの具体的な内容（回数・期間・対応範囲）を掲載】' ),
), 'list' ); ?>
<p>あわせてカリキュラムのSTEP7では、「案件獲得講座（選ばれるための戦略）」や「報連相の深化＋期待値調整の技術」を学びます。修了後は、URAKATAで稼働する、URAKATAと並行して自分で案件を獲得する、URAKATAを使わず自分で獲得する、のどれを選んでもかまいません。働き方と案件の探し方は<?php pw_link( 'career', '修了後の働き方' ); ?>、各STEPの内容は<?php pw_link( 'program', 'カリキュラム' ); ?>をご覧ください。</p>

<h2>プランの選び方</h2>
<p>学習に使える時間と、「修了後に自分で案件を取りに行くところまで伴走してほしいか」で選ぶのが目安です。</p>
<?php
$plan_label = function( $i ) use ( $plans ) { return (int) $plans[ $i ]['months'] . 'ヶ月 ' . esc_html( $plans[ $i ]['name'] ); };
pw_table(
    array( 'こんな方に', 'おすすめ' ),
    array(
        array( '事務経験があり、1日2時間×週5日程度の学習時間を確保できる', $plan_label( 0 ) ),
        array( '学んだあと、初めての案件獲得までサポートを受けたい', $plan_label( 1 ) ),
        array( '育児や仕事と両立しながら無理なく進め、複数案件を回せるようになりたい', $plan_label( 2 ) ),
        array( '将来は独立して、クライアントと直接取引できるところまで伴走してほしい', $plan_label( 3 ) ),
    )
);
?>

<?php pw_cta( array(
    'h'   => '迷ったら、無料相談で現在の生活リズムと目標を伺い、最適なプランをご提案します。',
    'p'   => '',
    'btn' => 'LINEで無料相談してプランを相談する',
    'pos' => 'mid-plan',
) ); ?>

<h2>受講料に含まれるもの・含まれないもの</h2>
<p><strong>含まれるもの</strong>：STEP1〜7の動画講座と実践課題（STEP7の案件獲得講座を含む）、課題へのフィードバック、メンター面談とチャット相談、面接シミュレーション、URAKATA案件への挑戦機会、案件獲得伴走サポート（<?php echo esc_html( $support_plans ); ?>プラン）。</p>
<p><strong>含まれないもの</strong>：パソコン・インターネット環境、分割払いの手数料。</p>
<?php pw_tbc_start( 'paid_tools' ); ?>
<p>有料ツールの利用料も受講料には含まれません（講座では無料で使える範囲で学びます）。</p>
<?php pw_tbc_end(); ?>

<h2>お支払い方法</h2>
<?php pw_table(
    array( '方法', '支払回数', '備考' ),
    array(
        array( 'クレジットカード', '一括・分割（最大' . $inst . '回）', '分割手数料はお客様のご負担' ),
        array( '銀行振込', '一括のみ', $tbc( 'bank_fee', '振込手数料はお客様のご負担' ) ),
    ),
    array( 'first_col_th' => true )
); ?>
<p>契約・お支払いに関する詳細は<?php pw_link( 'legal', '特定商取引法に基づく表記' ); ?>をご確認ください。</p>

<h2>受講料に含まれるpreworksのサポート</h2>
<p>preworksの受講料は、動画で学ぶだけでなく、学んだことを仕事につなげるところまでを含めた料金です。</p>
<?php pw_cards( array(
    array( 'tag' => '01', 't' => '現役オンライン秘書のメンター', 'd' => '在宅で働く現役のオンライン秘書が、面談とチャットで伴走します。分からないことをその場で相談できます。', 'link' => 'mentor' ),
    array( 'tag' => '02', 't' => '受講中の実務経験', 'd' => '面接に通過すると、受講中からURAKATAの実案件に報酬ありで取り組み、実務経験と実績をつくれます。', 'link' => 'career' ),
    array( 'tag' => '03', 't' => '自分で案件を獲得する準備', 'd' => 'STEP7の案件獲得講座と面接シミュレーションで、URAKATAに限らず自分で仕事を獲得するための準備をします。', 'link' => 'program' ),
    array( 'tag' => '04', 't' => '無期限で見返せる動画講座', 'd' => 'STEP1〜7の動画講座は、受講期間が終わったあとも見返せます。働き始めてからの復習にも使えます。' ),
), 2 ); ?>
<p>他の講座と比べて検討したい方や、オンライン秘書の仕事・収入について一般的な情報を知りたい方は、<?php pw_link( 'guide', 'オンライン秘書ガイド' ); ?>もあわせてご覧ください。</p>

<?php pw_faq( array(
    array( 'q' => '入会金はどのプランでもかかりますか？', 'a' => 'はい。入会金' . pw_yen( $fee ) . '（税込）が全プラン一律でかかります。' ),
    array( 'q' => '分割払いはできますか？', 'a' => 'クレジットカード決済なら最大' . $inst . '回まで分割できます。分割手数料はお客様のご負担です。銀行振込は一括払いのみです。' ),
) ); ?>
<?php pw_tbc_start( 'plan_change' ); ?>
<?php pw_faq( array(
    array( 'q' => '途中でプランを変更できますか？', 'a' => '【ここに掲載：受講中のアップグレード可否と差額の扱い】' ),
), '' ); ?>
<?php pw_tbc_end(); ?>
<?php pw_tbc_start( 'refund' ); ?>
<?php pw_faq( array(
    array( 'q' => '解約・返金はできますか？', 'a' => '【ここに掲載：特定商取引法に基づく表記（/legal/）と利用規約の内容に合わせた解約・返金の規定。クーリングオフ対象の有無も記載】' ),
), '' ); ?>
<?php pw_tbc_end(); ?>
<?php pw_tbc_start( 'kyufukin' ); ?>
<?php pw_faq( array(
    array( 'q' => '教育訓練給付金の対象ですか？', 'a' => '現在、preworksは教育訓練給付制度の対象講座ではありません。' ),
), '' ); ?>
<?php pw_tbc_end(); ?>

<?php pw_related( array( 'program', 'career', 'consultation', 'faq' ) ); ?>

<?php pw_page_end(); ?>
