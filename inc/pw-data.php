<?php
/**
 * preworks サイト共通データ（唯一の正）
 * ------------------------------------------------------------------
 * 料金・実績数値・メンター・修了生などを全ページで共有する。
 * 価格改定や実績更新のときは「このファイルだけ」を直せば、
 * TOP・料金ページ・各SEOページ・構造化データにすべて反映される。
 *
 * ■ 要確認（confirm）の使い方
 *   ページ本文で pw_tbc_start('キー') 〜 pw_tbc_end() で囲んだ部分は、
 *   下の 'confirm' でそのキーの 'ok' が true になるまで一般公開されない。
 *   （管理者ログイン中だけ、赤枠＋【要確認：◯◯】付きで表示される）
 *   事実確認が取れたら、値を入れて 'ok' => true にする。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function pw_data() {
    static $d = null;
    if ( $d !== null ) return $d;

    $d = array(

        /* ---------- 基本 ---------- */
        'brand'      => 'preworks',
        'brand_kana' => 'プレワークス',
        'course'     => 'オンライン秘書講座 preworks',
        'company'    => 'LISH株式会社',
        'company_url'=> 'https://preworks.jp/company/',
        'urakata_url'=> 'https://urakata.lishinc.co.jp/',

        /* ---------- 料金（税込・円） ---------- */
        'entry_fee'  => 55000,
        'installments_max' => 24,
        'plans' => array(
            array( 'months' => 2,  'name' => '学習定着プラン',       'price' => 165000, 'monthly' => 6900,  'sessions' => 4,  'support' => false, 'reco' => false ),
            array( 'months' => 3,  'name' => '初案件チャレンジプラン', 'price' => 220000, 'monthly' => 9200,  'sessions' => 6,  'support' => true,  'reco' => false ),
            array( 'months' => 6,  'name' => '自走化サポートプラン',   'price' => 385000, 'monthly' => 16100, 'sessions' => 12, 'support' => true,  'reco' => true  ),
            array( 'months' => 12, 'name' => '完全自立サポートプラン', 'price' => 660000, 'monthly' => 27500, 'sessions' => 24, 'support' => true,  'reco' => false ),
        ),
        'pay_note' => 'お支払い方法：銀行振込＝一括払いのみ／クレジットカード＝分割払い可（分割手数料はお客様のご負担）。',

        /* ---------- 実績（数値は必ず時点・母数とセットで使う） ---------- */
        'stats' => array(
            'urakata_companies' => array( 'value' => 140, 'unit' => '社', 'label' => 'URAKATA 導入企業数',     'as_of' => '2026年7月時点', 'plus' => true ),
            'secretaries'       => array( 'value' => 114, 'unit' => '名', 'label' => '在籍オンライン秘書数',   'as_of' => '2026年3月時点', 'plus' => false ),
            'job_rate'          => array( 'value' => 100, 'unit' => '%',  'label' => 'preworks 修了生のお仕事獲得率', 'as_of' => '2026年7月時点・修了生6名中6名がURAKATAで稼働', 'plus' => false ),
        ),
        'graduates_total' => 6,   // 修了生数（job_rate の母数）
        'graduates_hired' => 6,   // うちお仕事獲得
        'stats_as_of'     => '2026年7月時点',
        // 「お仕事獲得」の定義（獲得率の注記に使う）。2026-10-01 吉田さん確認
        'job_definition'  => '「お仕事獲得」は、URAKATAで業務を担当した実績があることを指します（修了生6名全員。稼働量は人によって異なります）。',

        /* ---------- 学習時間（FAQとTOPで記載が食い違っていたため要確認） ---------- */
        'study_time' => array(
            'weekly' => '週2.5〜6時間程度',               // 既存FAQの記載
            'two_month' => '1日2時間×週5日×7週間',        // 既存TOP FAQの記載（2ヶ月プラン）
        ),

        /* ---------- URAKATAでの稼働（未確定値は null） ---------- */
        'urakata' => array(
            // 参考情報として掲載する時給の目安（2026-10-01 吉田さん確認）。業務内容・経験により異なる
            'rate_min'    => 1200,
            'rate_max'    => 1400,
            // About ページ（OUR STORY / URAKATA TRACK RECORD）の数値。出典：2026-10 納品HTML（preworks-about-assets）。
            // stats.secretaries（在籍オンライン秘書数）とは別の指標（累計）。数値の更新はここだけ直す
            'applicants_total' => 1000,   // 累計応募件数（「1,000件超」）
            'pass_rate'        => 10,     // 選考通過率（％、「約10％」）
            'workers_total'    => 206,    // 累計稼働ワーカー数（名）
        ),

        /* ---------- 無料オンライン勉強会（全ページ末尾の共通CTA pw_final_cta() と About ページで使用） ----------
         * url が空のあいだは、ボタンはクリックできない表示（受付準備中）になる。
         * 申込URLが確定したら 'url' に入れるだけで全ページのボタンが有効になる。
         * ⚠️ 既存の無料相談URL（preworks_line_url / line_urls）を代用しないこと。 */
        'seminar' => array(
            'url'           => '',            // 申込URL（未確定）
            'label'         => '無料勉強会に申し込む',
            'pending_label' => '受付準備中',  // url 未設定時にボタンに添える文言
            'minutes'       => 60,            // 所要時間（分）。出典：納品HTML
        ),

        /* ---------- 無料相談 ---------- */
        'consult' => array(
            'minutes' => null,       // 所要時間（分） 例：45
            'tool'    => 'Zoom',      // 実施ツール【要確認】
        ),

        /* ---------- メンター（slug は /mentor/{slug}/ の個別ページ。無い人は空） ---------- */
        'mentors' => array(
            'tamaki'    => array( 'name' => '玉木 達子',   'slug' => 'tamaki-michiko', 'years' => '2年半', 'age' => '30代', 'kids' => 3, 'bg' => '約9年のブランクを経て、2023年に未経験からオンライン秘書をスタート。現在はURAKATAのディレクター' ),
            'toda'      => array( 'name' => '戸田 あかね', 'slug' => 'toda-akane',     'years' => '2年',   'age' => '30代', 'kids' => 3, 'bg' => '住宅メーカーで約10年間、受付や営業事務を経験。子どもの小学校入学を見据えて在宅ワークへ' ),
            'takamatsu' => array( 'name' => '髙松 由衣',   'slug' => 'takamatsu-yui',  'years' => '2年',   'age' => '30代', 'kids' => 1, 'bg' => '元客室乗務員。出産と転居をきっかけに在宅ワークを始める' ),
            'nakura'    => array( 'name' => '名倉 結',     'slug' => 'nakura-yui',     'years' => '1年',   'age' => '30代', 'kids' => 2, 'bg' => 'インテリア関係の接客業を経て、出産を機に8年間専業主婦に。子どもの小学校入学をきっかけにオンライン秘書へ' ),
            'zaitsu'    => array( 'name' => '財津 愛',     'slug' => '',               'years' => '1年',   'age' => '40代', 'kids' => 2, 'bg' => '' ),
            'nakasone'  => array( 'name' => '仲宗根 偉子', 'slug' => '',               'years' => '3年',   'age' => '40代', 'kids' => 3, 'bg' => '' ),
        ),

        /* ---------- 修了生（/voice/{slug}/） ---------- */
        'voices' => array(
            'oshiro' => array( 'name' => '大城さん', 'slug' => '01', 'attr' => '30代・2児ママ｜前職：コールセンター', 'hl' => 'メンターの支えで最後までやり切れました！' ),
            'y'      => array( 'name' => 'Yさん',    'slug' => '02', 'attr' => '50代・1児ママ｜前職：営業事務',       'hl' => '現役秘書に相談できる環境で安心して進められました！' ),
            'k'      => array( 'name' => 'Kさん',    'slug' => '03', 'attr' => '40代・2児ママ｜前職：専業主婦',       'hl' => '受講後に仕事が決まるようになりました！' ),
        ),

        /* ---------- 3大特典 ---------- */
        'gifts' => array( '在宅ワークを始める準備チェックリスト', '在宅ワーク開始までの実践ロードマップ', '在宅ママのための時間管理術' ),

        /* ---------- 要確認フラグ ----------
         * 'ok' => true にすると、そのキーで囲まれた本文が一般公開される。 */
        'confirm' => array(
            /* --- 料金・規約 --- */
            'refund'            => array( 'ok' => false, 'label' => '返金・クーリングオフ・受講期間延長の規定（利用規約 第7条「サポート期間6ヶ月」と現行プランの食い違いも確認）' ),
            'plan_common'       => array( 'ok' => false, 'label' => '面接シミュレーション・URAKATAプレ稼働が全プラン共通か' ),
            'plan_change'       => array( 'ok' => false, 'label' => '受講中のプラン変更（アップグレード）の可否と差額の扱い' ),
            'bank_fee'          => array( 'ok' => false, 'label' => '銀行振込手数料の負担者' ),
            'paid_tools'        => array( 'ok' => false, 'label' => '受講に有料ツールが不要か（無料で使える範囲で学ぶ前提でよいか）' ),
            'kyufukin'          => array( 'ok' => false, 'label' => '教育訓練給付制度など公的給付の対象可否（対象外なら「対象講座ではありません」と明記）' ),
            /* --- 学習・カリキュラム --- */
            'study_time'        => array( 'ok' => false, 'label' => '学習時間の目安（FAQ「週2.5〜6時間」とTOP「2ヶ月プランで1日2時間×週5日×7週間」の統一）' ),
            'study_slots'       => array( 'ok' => false, 'label' => '受講生の学習時間帯の実例（お昼寝中・寝かしつけ後・送迎の待ち時間など）' ),
            'video_length'      => array( 'ok' => false, 'label' => '動画講座1本あたりの長さ' ),
            'pc_spec'           => array( 'ok' => false, 'label' => 'パソコンの推奨スペック（発売からの年数・メモリ）' ),
            'mentor_session_time' => array( 'ok' => false, 'label' => 'メンター面談の実施時間帯（平日夜・土日に対応できるか）' ),
            'support_detail'    => array( 'ok' => false, 'label' => '案件獲得伴走サポートの具体的な内容' ),
            /* --- 無料相談・特典 --- */
            'consult_time'      => array( 'ok' => false, 'label' => '無料相談の所要時間・実施ツール（所要時間は consult.minutes に数値を入れる）' ),
            'consult_style'     => array( 'ok' => false, 'label' => '無料相談の担当者（メンターか営業担当か）・カメラオフ可否・子ども同席可否' ),
            'consult_honest'    => array( 'ok' => false, 'label' => '無料相談FAQ「合わない場合は正直に伝える」の運用可否' ),
            'contact_consult'   => array( 'ok' => false, 'label' => '/contact/ フォームで無料相談の受付が可能か' ),
            'gift_delivery'     => array( 'ok' => false, 'label' => '特典の受け取り条件（LINE登録のみ／無料相談参加者のみ）。gift_condition に line / consult を入れる' ),
            'gift_contents'     => array( 'ok' => false, 'label' => '3大特典の中身（形式・ボリューム・目次・作成者）' ),
            'utage_url'         => array( 'ok' => false, 'label' => 'UTAGEでページ別の流入経路URLを発行できるか（発行したら line_urls に登録）' ),
            /* --- URAKATA・仕事 --- */
            'urakata_rate'      => array( 'ok' => true,  'label' => 'URAKATAの時給の目安（参考：1,200〜1,400円。2026-10-01確認）' ),
            'urakata_hours'     => array( 'ok' => false, 'label' => 'URAKATAでの平均的な週稼働時間と月額報酬の例' ),
            'urakata_interview' => array( 'ok' => false, 'label' => 'URAKATA面接を受けられる時期・選考内容・不通過時のフォロー' ),
            'urakata_recruit'   => array( 'ok' => false, 'label' => 'URAKATAの一般募集の有無・応募条件・最低稼働時間' ),
            'urakata_sidejob'   => array( 'ok' => false, 'label' => 'URAKATAで会社員が副業として稼働できる条件（平日夜・土日のみの可否、最低稼働時間）' ),
            'job_breakdown'     => array( 'ok' => true,  'label' => '「お仕事獲得」の定義と獲得先の内訳（2026-10-01確認：修了生6名全員がURAKATAで稼働）' ),
            /* --- 人物・発言 --- */
            'mentor_quotes'     => array( 'ok' => false, 'label' => 'メンター・修了生への取材内容（1日の時間割・体験談など）と掲載の本人了承' ),
            'voice_card_info'   => array( 'ok' => false, 'label' => '修了生カードの「受講プラン」「現在の仕事」の掲載了承' ),
            /* --- その他 --- */
        ),

        /* ---------- 特典の受け取り条件：'line'＝LINE登録だけ／'consult'＝無料相談参加者のみ／null＝未確定 ---------- */
        'gift_condition' => null,


        /* ---------- ページ別のLINE（UTAGE）URL。発行されたら 'price' => 'https://…' のように追加 ---------- */
        'line_urls' => array(),

        /* ---------- カリキュラム（STEP名。program ページの構造化データに使用） ---------- */
        'curriculum' => array( '自己理解・オンライン秘書の全体像', 'パソコン、コミュニケーション基礎', 'AI活用、Googleツール基礎', 'バックオフィス業務の基礎', 'マニュアル作成、ツール実践', '実務シミュレーション強化', 'クライアントコミュニケーション実践＋案件獲得' ),
    );

    $d = apply_filters( 'pw_data', $d );
    return $d;
}
