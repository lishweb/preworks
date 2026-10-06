<?php
/**
 * preworks SEOページ基盤
 * ------------------------------------------------------------------
 * 1. ルーティング   … 登録表（pw-pages.php）にあるスラッグの固定ページは seo-pages/{slug}.php で描画
 * 2. SEO           … title / description / robots / 構造化データ（JSON-LD）を登録表から自動出力
 * 3. 表示部品       … CTA・FAQ・料金・実績・メンター・関連ページなど（pw_ で始まる関数）
 * 4. 要確認ゲート   … pw_tbc_start()〜pw_tbc_end() で囲んだ箇所は確認済みになるまで非公開
 * 5. 管理画面       … ツール →「SEOページ管理」：作成状況の一覧・下書き一括作成・要確認の一覧
 *
 * ページ本文の書き方は seo-pages/README.md を参照。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PW_PAGES_VER', '1.0.0' );

/* =====================================================================
 * 0. データ取得ヘルパー
 * ===================================================================== */

/** pw('plans.2.price') のようにドット区切りで共通データを取り出す */
function pw( $path, $default = null ) {
    $v = pw_data();
    foreach ( explode( '.', $path ) as $k ) {
        if ( is_array( $v ) && array_key_exists( $k, $v ) ) { $v = $v[ $k ]; } else { return $default; }
    }
    return $v;
}

/** 165000 → 165,000円 */
function pw_yen( $n, $suffix = '円' ) {
    return number_format( (int) $n ) . $suffix;
}

/** 実績の表示用文字列（例：140社以上（2026年7月時点）） */
function pw_stat( $key, $with_note = true ) {
    $s = pw( 'stats.' . $key );
    if ( ! $s ) return '';
    $t = number_format( $s['value'] ) . $s['unit'] . ( ! empty( $s['plus'] ) ? '以上' : '' );
    return $with_note ? $t . '（' . $s['as_of'] . '）' : $t;
}

/** URAKATAの時給の目安（参考）：「時給1,200〜1,400円程度」 */
function pw_urakata_rate_text( $suffix = '程度' ) {
    return '時給' . number_format( pw( 'urakata.rate_min' ) ) . '〜' . number_format( pw( 'urakata.rate_max' ) ) . '円' . $suffix;
}

/** お仕事獲得率の定型表記：お仕事獲得率100%（修了生6名中6名／2026年7月時点） */
function pw_job_rate_text( $label = 'お仕事獲得率' ) {
    return $label . pw( 'stats.job_rate.value' ) . '%（修了生' . pw( 'graduates_total' ) . '名中' . pw( 'graduates_hired' ) . '名がURAKATAで稼働／' . pw( 'stats_as_of' ) . '）';
}

/* =====================================================================
 * 1. 現在ページの判定とルーティング
 * ===================================================================== */

/** 現在表示中の登録スラッグ（未登録なら ''） */
function pw_current_slug() {
    static $slug = null;
    if ( $slug !== null ) return $slug;
    $slug  = '';
    $pages = pw_pages();
    if ( is_front_page() ) {
        $slug = 'home';
    } elseif ( is_page() ) {
        $s = get_post_field( 'post_name', get_queried_object_id() );
        if ( isset( $pages[ $s ] ) ) $slug = $s;
    } elseif ( is_post_type_archive( 'voice' ) ) {
        $slug = 'voice';
    } elseif ( is_post_type_archive( 'mentor' ) ) {
        $slug = 'mentor';
    } elseif ( is_post_type_archive( 'news' ) ) {
        $slug = 'news';
    } elseif ( is_home() && ! is_front_page() && ! is_paged() ) {
        $slug = 'column';
    }
    return $slug;
}

/** 登録表の1ページ分（$slug 省略時は現在ページ） */
function pw_page( $slug = null ) {
    $slug  = ( $slug === null ) ? pw_current_slug() : $slug;
    $pages = pw_pages();
    return ( $slug !== '' && isset( $pages[ $slug ] ) ) ? $pages[ $slug ] : null;
}

/** 登録スラッグの固定ページを seo-pages/{slug}.php で描画する */
function pw_template_include( $template ) {
    if ( ! is_page() || is_front_page() ) return $template;
    $slug = pw_current_slug();
    $p    = pw_page( $slug );
    if ( ! $p || $p['kind'] !== 'page' ) return $template;
    $file = get_template_directory() . '/seo-pages/' . $slug . '.php';
    return file_exists( $file ) ? $file : $template;
}
add_filter( 'template_include', 'pw_template_include', 99 );

/* =====================================================================
 * 2. URL とリンク（未公開ページへのリンク切れを自動で防ぐ）
 * ===================================================================== */

/** 登録スラッグ → URL */
function pw_url( $slug, $hash = '' ) {
    switch ( $slug ) {
        case 'home':   $u = home_url( '/' ); break;
        case 'column':
            $pid = (int) get_option( 'page_for_posts' );
            $u   = $pid ? get_permalink( $pid ) : home_url( '/blog/' );
            break;
        case 'news':
            $u = get_post_type_archive_link( 'news' );
            if ( ! $u ) $u = home_url( '/news/' );
            break;
        default:
            if ( strpos( $slug, '/' ) !== false ) { $u = home_url( '/' . trim( $slug, '/' ) . '/' ); } // 例：mentor/tamaki-michiko
            else { $u = home_url( '/' . $slug . '/' ); }
    }
    return $u . ( $hash ? '#' . ltrim( $hash, '#' ) : '' );
}

/**
 * リンク先が公開済みか。
 *  - 登録表の固定ページ：同じスラッグの固定ページが「公開」になっているか
 *  - voice/xx・mentor/xx：該当の投稿が公開されているか
 *  - home・一覧系：常に true
 */
function pw_is_live( $slug ) {
    static $cache = array();
    if ( isset( $cache[ $slug ] ) ) return $cache[ $slug ];
    $live = true;
    if ( preg_match( '#^(voice|mentor)/(.+)$#', $slug, $m ) ) {
        $q = get_posts( array( 'post_type' => $m[1], 'name' => $m[2], 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids' ) );
        $live = ! empty( $q );
        if ( $live && function_exists( 'preworks_entry_is_ready' ) ) $live = preworks_entry_is_ready( $q[0] );
    } else {
        $p = pw_page( $slug );
        if ( $p && $p['kind'] === 'page' ) {
            $post = get_page_by_path( $slug );
            $live = ( $post && $post->post_status === 'publish' );
        } elseif ( ! $p && $slug !== 'home' ) {
            $post = get_page_by_path( $slug );   // 既存の固定ページ（contact 等）
            $live = ( $post && $post->post_status === 'publish' );
        }
    }
    return $cache[ $slug ] = $live;
}

/** 管理者（プレビュー権限あり）か */
function pw_is_editor() {
    return is_user_logged_in() && current_user_can( 'edit_pages' );
}

/**
 * 内部リンク。リンク先が未公開ならリンクにせず文字だけ出す（リンク切れ防止）。
 * 管理者には未公開リンクを点線で表示する。
 *   pw_a( 'price' )                    → <a href="/price/">料金・プラン</a>
 *   pw_a( 'price', '料金の詳細はこちら' ) → アンカーテキスト指定
 *   pw_a( 'mentor/tamaki-michiko', '玉木メンターのプロフィール' )
 */
function pw_a( $slug, $text = null, $class = '' ) {
    if ( $text === null ) {
        $p    = pw_page( $slug );
        $text = $p ? $p['crumb'] : $slug;
    }
    $cls = $class ? ' class="' . esc_attr( $class ) . '"' : '';
    if ( pw_is_live( $slug ) ) {
        return '<a href="' . esc_url( pw_url( $slug ) ) . '"' . $cls . '>' . esc_html( $text ) . '</a>';
    }
    if ( pw_is_editor() ) {
        return '<a href="' . esc_url( pw_url( $slug ) ) . '" class="pw-unpub ' . esc_attr( $class ) . '" title="未公開ページへのリンク（公開されるまで一般には文字だけ表示）">' . esc_html( $text ) . '</a>';
    }
    return '<span' . $cls . '>' . esc_html( $text ) . '</span>';
}
/** pw_a を echo する */
function pw_link( $slug, $text = null, $class = '' ) { echo pw_a( $slug, $text, $class ); }

/** 外部リンク（新しいタブ） */
function pw_ext( $url, $text ) {
    return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $text ) . ' <span aria-hidden="true">↗</span></a>';
}

/** LINE（UTAGE）URL。ページ別URLが発行されたら pw_data の 'line_urls' に slug => URL で登録 */
function pw_line_url() {
    $map  = pw( 'line_urls', array() );
    $slug = pw_current_slug();
    if ( $slug && ! empty( $map[ $slug ] ) ) return $map[ $slug ];
    return function_exists( 'preworks_line_url' ) ? preworks_line_url() : 'https://sub.preworks.jp/line/open/bQAtEmMQRwat?mtid=taFGWWNm6MRj';
}

/** ナビ用：ページが公開済みならそのURL、未公開なら $fallback（例：TOPのアンカー） */
function pw_nav_url( $slug, $fallback ) {
    return pw_is_live( $slug ) ? pw_url( $slug ) : $fallback;
}

/* =====================================================================
 * 3. SEO：title / description / robots
 * ===================================================================== */

function pw_document_title( $title ) {
    $p = pw_page();
    return ( $p && $p['title'] !== '' ) ? $p['title'] : $title;
}
add_filter( 'pre_get_document_title', 'pw_document_title', 20 );

/** SEO SIMPLE PACK を後から入れた場合も、登録表の値を優先する */
add_filter( 'ssp_output_title', function( $t ) { $p = pw_page(); return ( $p && $p['title'] ) ? $p['title'] : $t; } );
add_filter( 'ssp_output_description', function( $d ) { $p = pw_page(); return ( $p && $p['description'] ) ? $p['description'] : $d; } );

/** 現在ページの description（functions.php の preworks_fallback_description から呼ぶ） */
function pw_current_description() {
    $p = pw_page();
    return $p ? $p['description'] : '';
}

function pw_wp_robots( $robots ) {
    $p    = pw_page();
    $slug = pw_current_slug();
    $thin = ( $slug === 'column' && function_exists( 'preworks_has_blog_posts' ) && ! preworks_has_blog_posts() )
         || ( $slug === 'news' && function_exists( 'preworks_has_cpt_posts' ) && ! preworks_has_cpt_posts( 'news' ) );
    if ( ( $p && ! empty( $p['noindex'] ) ) || $thin ) { $robots['noindex'] = true; $robots['follow'] = true; unset( $robots['max-image-preview'] ); }
    return $robots;
}

/** 実態調査は調査データ公開（confirm.report_data = ok）まで noindex */
add_filter( 'pw_pages', function( $p ) {
    if ( isset( $p['report'] ) && ! pw_ok( 'report_data' ) ) $p['report']['noindex'] = true;
    return $p;
} );

/** noindex の登録ページを WordPress のサイトマップから除外 */
add_filter( 'wp_sitemaps_posts_query_args', function( $args, $post_type ) {
    if ( $post_type !== 'page' ) return $args;
    $ex = array();
    foreach ( pw_pages() as $slug => $pg ) {
        if ( ! empty( $pg['noindex'] ) && $pg['kind'] === 'page' ) {
            $post = get_page_by_path( $slug );
            if ( $post ) $ex[] = $post->ID;
        }
    }
    if ( $ex ) $args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $ex );
    return $args;
}, 10, 2 );
add_filter( 'wp_robots', 'pw_wp_robots' );

/** ユーザー一覧サイトマップ（/wp-sitemap-users-1.xml）は不要なので除外 */
add_filter( 'wp_sitemaps_add_provider', function( $provider, $name ) {
    return ( $name === 'users' ) ? false : $provider;
}, 10, 2 );

/* =====================================================================
 * 4. パンくず
 * ===================================================================== */

/** [ [label, url], ... ] トップ → 親 → 自分 */
function pw_breadcrumb_items( $slug = null ) {
    $slug  = ( $slug === null ) ? pw_current_slug() : $slug;
    $chain = array();
    $guard = 0;
    while ( $slug && $slug !== 'home' && $guard++ < 5 ) {
        $p = pw_page( $slug );
        if ( ! $p ) break;
        array_unshift( $chain, array( $p['crumb'], pw_url( $slug ), $slug ) );
        $slug = $p['parent'];
    }
    array_unshift( $chain, array( 'トップ', home_url( '/' ), 'home' ) );
    return $chain;
}

/** 既存デザインのパンくず（トップ ／ 親 ／ 子） */
function pw_breadcrumb_html( $slug = null ) {
    $items = pw_breadcrumb_items( $slug );
    $last  = count( $items ) - 1;
    $out   = array();
    foreach ( $items as $i => $it ) {
        if ( $i === $last ) { $out[] = '<span aria-current="page">' . esc_html( $it[0] ) . '</span>'; continue; }
        $out[] = ( $it[2] === 'home' || pw_is_live( $it[2] ) )
            ? '<a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a>'
            : esc_html( $it[0] );
    }
    return '<nav class="bc" aria-label="パンくずリスト">' . implode( ' ／ ', $out ) . '</nav>';
}

/* =====================================================================
 * 5. ページの外枠（ヘッダー＋ページヒーロー／Contact＋フッター）
 * ===================================================================== */

$GLOBALS['pw_faq_items']  = array();
$GLOBALS['pw_tbc_stack']  = array();
$GLOBALS['pw_tbc_used']   = array();
$GLOBALS['pw_glossary']   = array();

/** 下層ページの上部（H1はここで1回だけ出す） */
function pw_page_hero( $args = array() ) {
    $p     = pw_page();
    $h1    = isset( $args['h1'] ) ? $args['h1'] : ( $p ? $p['h1'] : get_the_title() );
    $eye   = isset( $args['eyebrow'] ) ? $args['eyebrow'] : ( $p ? $p['eyebrow'] : '' );
    $lead  = isset( $args['lead'] ) ? $args['lead'] : ( $p ? $p['lead'] : '' );
    ?>
  <section class="page-hero pw-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <?php echo pw_breadcrumb_html(); ?>
    <?php if ( $eye ) : ?><span class="eyebrow"><?php echo esc_html( $eye ); ?></span><?php endif; ?>
    <h1><?php echo esc_html( $h1 ); ?></h1>
    <?php if ( $lead ) : ?><p class="sub"><?php echo wp_kses_post( $lead ); ?></p><?php endif; ?>
  </div></section>
    <?php
}

/**
 * 本文の開始。
 *   $args['layout'] = 'article'（読み物・既定） | 'wide'（カードや表が中心のページ）
 *   $args['toc']    = true で H2 から目次を自動生成（本文中の pw_toc() の位置に出る）
 */
function pw_page_start( $args = array() ) {
    $args = wp_parse_args( $args, array( 'layout' => 'article', 'toc' => false ) );
    $GLOBALS['pw_page_args'] = $args;
    get_header();
    echo "<main class=\"pw-main pw-layout-" . esc_attr( $args['layout'] ) . "\">\n";
    pw_page_hero( $args );
    echo '<section class="pw-body sec-pad"><div class="wrap"><div class="' . ( $args['layout'] === 'wide' ? 'pw-wide-body' : 'article pw-article' ) . '">' . "\n";
    ob_start();
}

/** 本文の終了（目次の差し込み・末尾Contact・フッター） */
function pw_page_end( $args = array() ) {
    $html = ob_get_clean();
    $html = pw_process_headings( $html );
    echo $html;
    echo "\n</div></div></section>\n";
    pw_final_cta();
    echo "</main>\n";
    get_footer();
}

/** H2 に id を振り、<!--pw:toc--> の位置に目次を入れる */
function pw_process_headings( $html ) {
    $toc = array();
    $n   = 0;
    $html = preg_replace_callback( '#<h2(\s[^>]*)?>(.*?)</h2>#s', function( $m ) use ( &$toc, &$n ) {
        $attrs = isset( $m[1] ) ? $m[1] : '';
        $n++;
        if ( preg_match( '/\sid="([^"]+)"/', $attrs, $idm ) ) { $id = $idm[1]; }
        else { $id = 'sec' . $n; $attrs .= ' id="' . $id . '"'; }
        if ( strpos( $attrs, 'data-notoc' ) === false ) $toc[] = array( $id, wp_strip_all_tags( $m[2] ) );
        return '<h2' . $attrs . '>' . $m[2] . '</h2>';
    }, $html );
    if ( strpos( $html, '<!--pw:toc-->' ) !== false ) {
        $t = '';
        if ( count( $toc ) >= 3 ) {
            $t = '<nav class="toc pw-toc" aria-label="目次"><p class="toc-h">目次</p><ol>';
            foreach ( $toc as $it ) $t .= '<li><a href="#' . esc_attr( $it[0] ) . '">' . esc_html( $it[1] ) . '</a></li>';
            $t .= '</ol></nav>';
        }
        $html = str_replace( '<!--pw:toc-->', $t, $html );
    }
    return $html;
}

/** 目次を出したい位置で呼ぶ（H2が3つ以上あるときだけ表示） */
function pw_toc() { echo '<!--pw:toc-->'; }

/** 既存の共通Contactブロック（全ページ末尾） */
function pw_final_cta() {
    ?>
  <section class="finalcta sec-pad" id="contact"><div class="wrap">
    <span class="eyebrow" style="color:var(--gold)">Contact</span>
    <h2 class="mincho" data-notoc>「いつか在宅で働きたい」を、<br>今日から少しずつ形に。</h2>
    <p>無料相談では、未経験からオンライン秘書を始めた方の事例をもとに、<br>今のあなたが何から始めればよいのかを一緒に整理します。</p>
    <?php echo pw_line_btn( 'LINEで無料オンライン相談をする', 'final' ); ?>
  </div></section>
    <?php
}

/* =====================================================================
 * 6. 表示部品
 * ===================================================================== */

/** LINEボタン（GTM計測用に data-pw-cta を付与） */
function pw_line_btn( $label = 'LINEで無料オンライン相談をする', $pos = 'body' ) {
    return '<a class="btn btn-line" href="' . esc_url( pw_line_url() ) . '" target="_blank" rel="noopener" data-pw-cta="' . esc_attr( $pos ) . '"><img src="' . esc_url( PREWORKS_IMG . '/LINE_Brand_icon.png' ) . '" alt="LINE" class="liico">' . esc_html( $label ) . '</a>';
}

/**
 * 本文中のCTAボックス
 *   pw_cta( array( 'h' => '見出し', 'p' => '説明文（HTML可）', 'btn' => 'ボタン文言', 'sub' => 'ボタン下の補足' ) );
 */
function pw_cta( $args = array() ) {
    $a = wp_parse_args( $args, array(
        'h'   => 'まずは無料オンライン相談で、今の状況を整理しませんか？',
        'p'   => '未経験からオンライン秘書を始めた方の事例をもとに、あなたに合った始め方を一緒に考えます。受講を前提としない相談だけでも大丈夫です。',
        'btn' => 'LINEで無料オンライン相談をする',
        'sub' => '',
        'pos' => 'mid',
    ) );
    echo '<aside class="pw-cta" aria-label="無料相談のご案内"><p class="pw-cta-h">' . wp_kses_post( $a['h'] ) . '</p>';
    if ( $a['p'] ) echo '<p class="pw-cta-p">' . wp_kses_post( $a['p'] ) . '</p>';
    echo pw_line_btn( $a['btn'], $a['pos'] );
    if ( $a['sub'] ) echo '<p class="pw-cta-sub">' . wp_kses_post( $a['sub'] ) . '</p>';
    echo '</aside>';
}

/** 結論ボックス（記事冒頭の要約） */
function pw_summary( $html, $label = 'この記事の結論' ) {
    echo '<div class="pw-summary"><p class="pw-summary-h">' . esc_html( $label ) . '</p><div class="pw-summary-b">' . wp_kses_post( $html ) . '</div></div>';
}

/** 補足ボックス（既存 .callout） */
function pw_callout( $html ) {
    echo '<div class="callout">' . wp_kses_post( $html ) . '</div>';
}

/** 箇条書きボックス（既存 .box-list / .box-num）
 *   pw_box( '見出し', array( '項目1', '項目2' ), 'num' ) */
function pw_box( $title, $items, $type = 'list' ) {
    $cls = ( $type === 'num' ) ? 'box-num' : 'box-list';
    $tag = ( $type === 'num' ) ? 'ol' : 'ul';
    echo '<div class="' . $cls . '">';
    if ( $title ) echo '<p class="box-h">' . esc_html( $title ) . '</p>';
    echo '<' . $tag . '>';
    foreach ( $items as $it ) echo '<li>' . wp_kses_post( $it ) . '</li>';
    echo '</' . $tag . '></div>';
}

/** 表（横スクロール対応）
 *   pw_table( array( '見出し1', '見出し2' ), array( array( 'a', 'b' ), ... ), array( 'caption' => '', 'first_col_th' => true ) ) */
function pw_table( $head, $rows, $opts = array() ) {
    $o = wp_parse_args( $opts, array( 'caption' => '', 'first_col_th' => false, 'class' => '', 'stack' => null ) );
    $cols  = $head ? count( $head ) : ( $rows ? count( reset( $rows ) ) : 0 );
    // 3列以上で見出しがある表は、スマホでは1行ずつカード状に積む（列が潰れて縦書きになるのを防ぐ）
    $stack = ( $o['stack'] === null ) ? ( $head && $cols >= 3 ) : (bool) $o['stack'];
    $labels = array();
    if ( $head ) foreach ( $head as $h ) $labels[] = trim( wp_strip_all_tags( str_replace( array( '<br>', '<br/>', '<br />' ), ' ', $h ) ) );
    echo '<div class="pw-table pw-tc' . (int) $cols . ( $stack ? ' pw-stack' : '' ) . ' ' . esc_attr( $o['class'] ) . '"><div class="pw-table-in"><table class="atable">';
    if ( $o['caption'] ) echo '<caption>' . esc_html( $o['caption'] ) . '</caption>';
    if ( $head ) {
        echo '<thead><tr>';
        foreach ( $head as $h ) echo '<th scope="col">' . wp_kses_post( $h ) . '</th>';
        echo '</tr></thead>';
    }
    echo '<tbody>';
    foreach ( $rows as $r ) {
        echo '<tr>';
        foreach ( array_values( $r ) as $i => $c ) {
            $lab = ( $stack && ! empty( $labels[ $i ] ) ) ? ' data-label="' . esc_attr( $labels[ $i ] ) . '"' : '';
            if ( $i === 0 && $o['first_col_th'] ) echo '<th scope="row"' . $lab . '>' . wp_kses_post( $c ) . '</th>';
            else echo '<td' . $lab . '>' . wp_kses_post( $c ) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div><p class="pw-table-hint" hidden>← 横にスクロールできます →</p></div>';
}

/** 番号付きステップ
 *   pw_steps( array( array( 't' => 'タイトル', 'd' => '説明（HTML可）' ), ... ) ) */
function pw_steps( $items ) {
    echo '<ol class="pw-steps">';
    foreach ( $items as $i => $it ) {
        echo '<li class="pw-step"><span class="pw-step-n">STEP<b>' . ( $i + 1 ) . '</b></span><div class="pw-step-b"><p class="pw-step-t">' . wp_kses_post( $it['t'] ) . '</p>';
        if ( ! empty( $it['d'] ) ) echo '<div class="pw-step-d">' . wp_kses_post( $it['d'] ) . '</div>';
        echo '</div></li>';
    }
    echo '</ol>';
}

/** カード一覧（業務・特徴など）
 *   pw_cards( array( array( 't' => 'タイトル', 'd' => '説明', 'tag' => '小見出し', 'link' => 'slug' ), ... ), 3 ) */
function pw_cards( $items, $cols = 3 ) {
    echo '<div class="pw-cards pw-cols-' . (int) $cols . '">';
    foreach ( $items as $it ) {
        $live = ! empty( $it['link'] ) && pw_is_live( $it['link'] );
        $tag  = $live ? 'a' : 'div';
        $href = $live ? ' href="' . esc_url( pw_url( $it['link'] ) ) . '"' : '';
        echo '<' . $tag . ' class="pw-card' . ( $live ? ' is-link' : '' ) . '"' . $href . '>';
        if ( ! empty( $it['tag'] ) ) echo '<span class="pw-card-tag">' . esc_html( $it['tag'] ) . '</span>';
        echo '<p class="pw-card-t">' . wp_kses_post( $it['t'] ) . '</p>';
        if ( ! empty( $it['d'] ) ) echo '<p class="pw-card-d">' . wp_kses_post( $it['d'] ) . '</p>';
        if ( $live ) echo '<span class="pw-card-more">詳しく見る</span>';
        echo '</' . $tag . '>';
    }
    echo '</div>';
}

/** 引用（メンター・修了生の発言）
 *   pw_quote( '本文', '玉木メンター', 'mentor/tamaki-michiko' ) */
function pw_quote( $text, $who, $link = '' ) {
    echo '<blockquote class="pw-quote"><p>' . wp_kses_post( $text ) . '</p><cite>' . esc_html( $who );
    if ( $link && pw_is_live( $link ) ) echo ' <a href="' . esc_url( pw_url( $link ) ) . '">プロフィールを見る</a>';
    echo '</cite></blockquote>';
}

/** よくある質問（既存FAQと同じ見た目）＋ FAQPage 構造化データに自動登録
 *   pw_faq( array( array( 'q' => '質問', 'a' => '回答（HTML可）' ), ... ), 'よくある質問' )
 *   見出しを出したくない場合は $title に '' */
function pw_faq( $items, $title = 'よくある質問', $args = array() ) {
    $a = wp_parse_args( $args, array( 'schema' => true, 'id' => '' ) );
    if ( $title !== '' ) echo '<h2' . ( $a['id'] ? ' id="' . esc_attr( $a['id'] ) . '"' : '' ) . '>' . esc_html( $title ) . '</h2>';
    echo '<div class="faq pw-faq"><div class="qa">';
    foreach ( $items as $it ) {
        echo '<details><summary><span class="qi">Q</span>' . esc_html( $it['q'] ) . '</summary><div class="a"><span class="ai">A</span>' . wp_kses_post( $it['a'] ) . '</div></details>';
        if ( $a['schema'] && empty( $GLOBALS['pw_tbc_hidden'] ) ) $GLOBALS['pw_faq_items'][] = $it;
    }
    echo '</div></div>';
}

/** 実績数値（TOPと同じデザイン。数値はHTMLに直接出す＝0表示問題なし）
 *   pw_stats( array( 'urakata_companies', 'secretaries', 'job_rate' ) ) */
function pw_stats( $keys = array( 'urakata_companies', 'secretaries', 'job_rate' ) ) {
    echo '<div class="nums pw-nums">';
    foreach ( $keys as $k ) {
        $s = pw( 'stats.' . $k );
        if ( ! $s ) continue;
        echo '<div class="nstat"><div class="v">' . number_format( $s['value'] ) . '<span class="u">' . esc_html( $s['unit'] . ( ! empty( $s['plus'] ) ? '以上' : '' ) ) . '</span></div><div class="lbl">' . esc_html( $s['label'] ) . '</div><div class="note">※' . esc_html( $s['as_of'] ) . '</div></div>';
    }
    echo '</div>';
}

/** 料金プランカード（TOPと同じデザイン） */
function pw_plans( $args = array() ) {
    $a = wp_parse_args( $args, array( 'note' => true ) );
    echo '<div class="plans pw-plans">';
    foreach ( pw( 'plans' ) as $pl ) {
        echo '<div class="plan' . ( $pl['reco'] ? ' reco' : '' ) . '">';
        if ( $pl['reco'] ) echo '<span class="badge">おすすめ</span>';
        echo '<div class="term">' . (int) $pl['months'] . '<span class="u">ヶ月</span></div>';
        echo '<div class="model">' . esc_html( $pl['name'] ) . '</div>';
        echo '<div class="price"><span class="ps-label">' . (int) pw( 'installments_max' ) . '回分割</span>約' . number_format( $pl['monthly'] ) . '<small>円/月</small></div>';
        echo '<div class="price-lump">一括 ' . pw_yen( $pl['price'] ) . '（税込）</div>';
        echo '</div>';
    }
    echo '</div><p class="slider-hint">← 横にスワイプして他のプランを見る →</p>';
    if ( $a['note'] ) pw_price_note();
}

/** 入会金・支払い方法の注記 */
function pw_price_note() {
    echo '<p class="pw-note">※受講料とは別に、入会金 ' . pw_yen( pw( 'entry_fee' ) ) . '（税込）が全プラン一律でかかります。<br>' . esc_html( pw( 'pay_note' ) ) . '</p>';
}

/** プラン別サポート内容の比較表（TOPと同じデザイン） */
function pw_plan_table() {
    $plans = pw( 'plans' );
    echo '<div class="plantable pw-plantable"><div class="pt-scroll"><table><thead><tr><th></th>';
    foreach ( $plans as $pl ) echo '<th' . ( $pl['reco'] ? ' class="reco"' : '' ) . '>' . (int) $pl['months'] . 'ヶ月プラン' . ( $pl['reco'] ? '<span class="rb">おすすめ</span>' : '' ) . '</th>';
    echo '</tr></thead><tbody>';
    $row = function( $label, $cb ) use ( $plans ) {
        echo '<tr><th>' . $label . '</th>';
        foreach ( $plans as $pl ) echo '<td' . ( $pl['reco'] ? ' class="reco"' : '' ) . '>' . $cb( $pl ) . '</td>';
        echo '</tr>';
    };
    $row( '受講料（税込）', function( $pl ) { return pw_yen( $pl['price'] ); } );
    $row( 'メンター伴走期間<br>質問し放題', function( $pl ) { return (int) $pl['months'] . 'ヶ月'; } );
    $row( '動画視聴', function() { return '無期限'; } );
    $row( 'メンター面談', function( $pl ) { return (int) $pl['sessions'] . '回（月2回）'; } );
    echo '<tr><th>面接シミュレーション</th><td colspan="' . count( $plans ) . '">1回</td></tr>';
    echo '<tr><th>URAKATAプレ稼働</th><td colspan="' . count( $plans ) . '">収入を得ながら稼働可能<small>※希望者は面接通過後URAKATAで継続稼働可能</small></td></tr>';
    $row( '案件獲得伴走サポート', function( $pl ) { return $pl['support'] ? '○' : '×'; } );
    echo '</tbody></table></div></div>';
}

/** メンターカード
 *   pw_mentors( array( 'tamaki', 'toda' ) ) 省略時は全員 */
function pw_mentors( $keys = null, $args = array() ) {
    $all  = pw( 'mentors' );
    $keys = $keys ? $keys : array_keys( $all );
    echo '<div class="pw-people">';
    foreach ( $keys as $k ) {
        if ( empty( $all[ $k ] ) ) continue;
        $m    = $all[ $k ];
        $link = $m['slug'] ? 'mentor/' . $m['slug'] : '';
        $live = $link && pw_is_live( $link );
        $tag  = $live ? 'a' : 'div';
        echo '<' . $tag . ' class="pw-person' . ( $live ? ' is-link' : '' ) . '"' . ( $live ? ' href="' . esc_url( pw_url( $link ) ) . '"' : '' ) . '>';
        echo '<p class="pw-person-n">' . esc_html( $m['name'] ) . '</p>';
        echo '<p class="pw-person-a">オンライン秘書歴' . esc_html( $m['years'] ) . '｜' . esc_html( $m['age'] ) . '｜' . (int) $m['kids'] . '児ママ</p>';
        if ( ! empty( $m['bg'] ) ) echo '<p class="pw-person-d">' . esc_html( $m['bg'] ) . '</p>';
        if ( $live ) echo '<span class="pw-card-more">プロフィールを見る</span>';
        echo '</' . $tag . '>';
    }
    echo '</div>';
}

/** 修了生カード
 *   pw_voices( array( 'k', 'y' ) ) 省略時は全員 */
function pw_voices( $keys = null ) {
    $all  = pw( 'voices' );
    $keys = $keys ? $keys : array_keys( $all );
    echo '<div class="pw-people">';
    foreach ( $keys as $k ) {
        if ( empty( $all[ $k ] ) ) continue;
        $v    = $all[ $k ];
        $link = 'voice/' . $v['slug'];
        $live = pw_is_live( $link );
        $tag  = $live ? 'a' : 'div';
        echo '<' . $tag . ' class="pw-person' . ( $live ? ' is-link' : '' ) . '"' . ( $live ? ' href="' . esc_url( pw_url( $link ) ) . '"' : '' ) . '>';
        echo '<p class="pw-person-n">' . esc_html( $v['name'] ) . '</p><p class="pw-person-a">' . esc_html( $v['attr'] ) . '</p>';
        echo '<p class="pw-person-d">「' . esc_html( $v['hl'] ) . '」</p>';
        if ( $live ) echo '<span class="pw-card-more">体験談を読む</span>';
        echo '</' . $tag . '>';
    }
    echo '</div>';
}

/** 関連ページ（公開済みのものだけカードで表示）
 *   pw_related( array( 'price', 'program', 'career' ) ) */
function pw_related( $slugs, $title = '関連ページ' ) {
    $live = array();
    foreach ( $slugs as $s ) { if ( pw_page( $s ) && ( pw_is_live( $s ) || pw_is_editor() ) ) $live[] = $s; }
    if ( ! $live ) return;
    echo '<div class="pw-related"><p class="pw-related-h">' . esc_html( $title ) . '</p><div class="pw-related-list">';
    foreach ( $live as $s ) {
        $p  = pw_page( $s );
        $ok = pw_is_live( $s );
        echo '<a class="pw-related-item' . ( $ok ? '' : ' pw-unpub' ) . '" href="' . esc_url( pw_url( $s ) ) . '"><span class="pw-related-t">' . esc_html( $p['h1'] ? $p['h1'] : $p['crumb'] ) . '</span><span class="pw-related-d">' . esc_html( wp_html_excerpt( $p['description'], 60, '…' ) ) . '</span></a>';
    }
    echo '</div></div>';
}

/** 関連記事（次フェーズ用の枠）。指定カテゴリの公開記事があるときだけ表示 */
function pw_related_posts( $category_slug, $title = '関連コラム', $n = 3 ) {
    $q = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => $n, 'category_name' => $category_slug, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
    if ( ! $q->have_posts() ) return;
    echo '<div class="pw-related"><p class="pw-related-h">' . esc_html( $title ) . '</p><div class="pw-related-list">';
    while ( $q->have_posts() ) { $q->the_post(); echo '<a class="pw-related-item" href="' . esc_url( get_permalink() ) . '"><span class="pw-related-t">' . esc_html( get_the_title() ) . '</span></a>'; }
    wp_reset_postdata();
    echo '</div></div>';
}

/** 運営元の明記（自社講座を比較・推奨する箇所。ステマ規制対応） */
function pw_operator_note( $text = '' ) {
    if ( $text === '' ) $text = 'このページは、オンライン秘書講座「preworks」を運営する' . pw( 'company' ) . 'が作成しています。preworksに関する記載は自社の講座の紹介です。';
    echo '<p class="pw-operator">' . esc_html( $text ) . '</p>';
}

/** 注記（小さめのグレー文字） */
function pw_note( $html ) { echo '<p class="pw-note">' . wp_kses_post( $html ) . '</p>'; }

/** 用語集の1項目（DefinedTermSet 構造化データに自動登録） */
function pw_term( $term, $html, $id = '' ) {
    $id = $id ? $id : 'term-' . substr( md5( $term ), 0, 8 );
    echo '<div class="pw-term" id="' . esc_attr( $id ) . '"><dt>' . esc_html( $term ) . '</dt><dd>' . wp_kses_post( $html ) . '</dd></div>';
    $GLOBALS['pw_glossary'][] = array( 'term' => $term, 'desc' => wp_strip_all_tags( $html ), 'id' => $id );
}

/* =====================================================================
 * 7. 要確認ゲート
 * ===================================================================== */

/** そのキーが確認済みか */
function pw_ok( $key ) {
    $c = pw( 'confirm.' . $key );
    return is_array( $c ) && ! empty( $c['ok'] );
}

/**
 * 要確認ブロックの開始。
 *   確認済み              → そのまま表示
 *   未確認・管理者         → 赤枠＋【要確認：◯◯】付きで表示（公開前チェック用）
 *   未確認・一般の閲覧者   → 何も表示しない
 */
function pw_tbc_start( $key ) {
    $GLOBALS['pw_tbc_used'][ $key ] = true;
    if ( pw_ok( $key ) ) { $GLOBALS['pw_tbc_stack'][] = 'ok'; return; }
    $label = pw( 'confirm.' . $key . '.label', $key );
    if ( pw_is_editor() ) {
        $GLOBALS['pw_tbc_stack'][] = 'editor';
        echo '<div class="pw-tbc"><p class="pw-tbc-label">【要確認：' . esc_html( $label ) . '】確認が取れるまで一般には表示されません（pw-data.php の confirm.' . esc_html( $key ) . '）</p>';
        return;
    }
    $GLOBALS['pw_tbc_stack'][]  = 'hide';
    $GLOBALS['pw_tbc_hidden'] = ( isset( $GLOBALS['pw_tbc_hidden'] ) ? $GLOBALS['pw_tbc_hidden'] : 0 ) + 1;
    ob_start();
}
function pw_tbc_end() {
    $mode = array_pop( $GLOBALS['pw_tbc_stack'] );
    if ( $mode === 'editor' ) echo '</div>';
    if ( $mode === 'hide' ) { ob_end_clean(); $GLOBALS['pw_tbc_hidden']--; }
}

/** 表のセルやFAQの回答など、文字列の中で要確認部分を使うとき：pw_tbc_html( 'refund', '…' ) */
function pw_tbc_html( $key, $html ) {
    ob_start(); pw_tbc_start( $key ); echo $html; pw_tbc_end(); return ob_get_clean();
}

/** 値を出す。未確定（null）なら【◯◯】のプレースホルダー（pw_tbc で囲んだ中で使う） */
function pw_v( $path, $placeholder = '' ) {
    $v = pw( $path );
    if ( $v === null || $v === '' ) return '【' . ( $placeholder ? $placeholder : $path ) . '】';
    return is_numeric( $v ) ? number_format( $v ) : $v;
}

/* =====================================================================
 * 8. 構造化データ（JSON-LD）… body 末尾にまとめて出力
 * ===================================================================== */

function pw_org_node() {
    return array( '@type' => 'Organization', '@id' => pw( 'company_url' ) . '#organization', 'name' => pw( 'company' ), 'url' => pw( 'company_url' ) );
}

function pw_jsonld_output() {
    $slug = pw_current_slug();
    $p    = pw_page( $slug );
    if ( ! $p ) return;
    $url   = ( $slug === 'home' ) ? home_url( '/' ) : pw_url( $slug );
    $graph = array();
    $types = $p['schema'];

    if ( $slug !== 'home' ) {
        $list = array();
        foreach ( pw_breadcrumb_items( $slug ) as $i => $it ) {
            $list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $it[0], 'item' => $it[1] );
        }
        $graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
    }

    if ( in_array( 'FAQPage', $types, true ) && ! empty( $GLOBALS['pw_faq_items'] ) ) {
        $qs = array();
        foreach ( $GLOBALS['pw_faq_items'] as $it ) {
            $qs[] = array( '@type' => 'Question', 'name' => wp_strip_all_tags( $it['q'] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $it['a'] ) ) ) ) );
        }
        $graph[] = array( '@type' => 'FAQPage', 'mainEntity' => $qs );
    }

    if ( in_array( 'Article', $types, true ) && is_singular() ) {
        $id = get_queried_object_id();
        $graph[] = array(
            '@type' => 'Article', 'headline' => $p['h1'], 'description' => $p['description'],
            'mainEntityOfPage' => $url, 'image' => PREWORKS_IMG . '/og.jpg',
            'datePublished' => get_the_date( 'c', $id ), 'dateModified' => get_the_modified_date( 'c', $id ),
            'author' => pw_org_node(), 'publisher' => array_merge( pw_org_node(), array( 'logo' => array( '@type' => 'ImageObject', 'url' => PREWORKS_IMG . '/logo.png' ) ) ),
        );
    }

    if ( in_array( 'Course', $types, true ) ) {
        $offers = array();
        foreach ( pw( 'plans' ) as $pl ) {
            $offers[] = array( '@type' => 'Offer', 'name' => $pl['months'] . 'ヶ月 ' . $pl['name'], 'category' => 'Paid', 'price' => (string) $pl['price'], 'priceCurrency' => 'JPY', 'url' => pw_nav_url( 'price', home_url( '/#plans' ) ) );
        }
        $course = array(
            '@type' => 'Course', 'name' => pw( 'course' ),
            'description' => '未経験から最短2ヵ月でオンライン秘書デビューを目指す実践型講座。現役オンライン秘書による1on1サポートと、受講中からURAKATAの実案件に挑戦できる機会を用意しています。',
            'provider' => pw_org_node(), 'inLanguage' => 'ja', 'offers' => $offers,
            'hasCourseInstance' => array( '@type' => 'CourseInstance', 'courseMode' => 'Online', 'courseWorkload' => 'P2M' ),
        );
        if ( $slug === 'program' ) {
            $course['syllabusSections'] = array();
            foreach ( (array) pw( 'curriculum', array() ) as $i => $t ) {
                $course['syllabusSections'][] = array( '@type' => 'Syllabus', 'name' => 'STEP' . ( $i + 1 ) . ' ' . $t );
            }
        }
        $graph[] = $course;
    }

    if ( in_array( 'Organization', $types, true ) ) {
        $graph[] = array_merge( pw_org_node(), array(
            'legalName' => 'LISH株式会社', 'alternateName' => 'LISH Inc.', 'foundingDate' => '2019-04-11',
            'founder' => array( '@type' => 'Person', 'name' => '吉田 和史' ),
            'logo' => PREWORKS_IMG . '/logo.png',
            'address' => array( '@type' => 'PostalAddress', 'postalCode' => '810-0001', 'addressRegion' => '福岡県', 'addressLocality' => '福岡市中央区', 'streetAddress' => '天神2丁目2番12号 T&Jビルディング7F', 'addressCountry' => 'JP' ),
        ) );
    }

    if ( in_array( 'Dataset', $types, true ) && pw_ok( 'report_data' ) ) {
        $graph[] = array( '@type' => 'Dataset', 'name' => $p['h1'], 'description' => $p['description'], 'url' => $url,
            'creator' => pw_org_node(), 'isAccessibleForFree' => true, 'temporalCoverage' => (string) pw( 'report.period', '' ) );
    }

    foreach ( array( 'AboutPage', 'WebPage', 'CollectionPage' ) as $t ) {
        if ( in_array( $t, $types, true ) ) {
            $graph[] = array( '@type' => $t, 'name' => $p['h1'] ? $p['h1'] : $p['crumb'], 'description' => $p['description'], 'url' => $url, 'publisher' => pw_org_node() );
        }
    }

    if ( in_array( 'DefinedTermSet', $types, true ) && ! empty( $GLOBALS['pw_glossary'] ) ) {
        $terms = array();
        foreach ( $GLOBALS['pw_glossary'] as $g ) $terms[] = array( '@type' => 'DefinedTerm', 'name' => $g['term'], 'description' => wp_html_excerpt( $g['desc'], 300, '…' ), 'url' => $url . '#' . $g['id'] );
        $graph[] = array( '@type' => 'DefinedTermSet', 'name' => $p['h1'], 'url' => $url, 'hasDefinedTerm' => $terms );
    }

    if ( $slug === 'home' ) {
        $graph[] = array( '@type' => 'WebSite', 'name' => pw( 'course' ), 'url' => home_url( '/' ), 'inLanguage' => 'ja', 'publisher' => pw_org_node() );
    }

    if ( ! $graph ) return;
    echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'pw_jsonld_output', 5 );

/* =====================================================================
 * 9. CSS / JS
 * ===================================================================== */

function pw_assets() {
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    wp_enqueue_style( 'pw-pages', $uri . '/assets/css/pw-pages.css', array( 'preworks-main' ), filemtime( $dir . '/assets/css/pw-pages.css' ) );
    wp_enqueue_script( 'pw-pages', $uri . '/assets/js/pw-pages.js', array(), filemtime( $dir . '/assets/js/pw-pages.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'pw_assets', 20 );

/* =====================================================================
 * 10. 管理画面：ツール →「SEOページ管理」
 * ===================================================================== */

function pw_admin_menu() {
    add_management_page( 'SEOページ管理', 'SEOページ管理', 'edit_pages', 'pw-seo-pages', 'pw_admin_page' );
}
add_action( 'admin_menu', 'pw_admin_menu' );

/** 未作成の固定ページを下書きで作る（スラッグ・タイトルは登録表どおり。本文は空でよい） */
function pw_admin_create_pages( $status = 'draft' ) {
    $made = array();
    foreach ( pw_pages() as $slug => $p ) {
        if ( $p['kind'] !== 'page' ) continue;
        if ( get_page_by_path( $slug, OBJECT, 'page' ) ) continue;
        if ( ! file_exists( get_template_directory() . '/seo-pages/' . $slug . '.php' ) ) continue;
        $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => $status, 'post_name' => $slug, 'post_title' => $p['crumb'], 'post_content' => '' ) );
        if ( $id && ! is_wp_error( $id ) ) $made[] = $slug;
    }
    return $made;
}

function pw_admin_page() {
    if ( ! current_user_can( 'edit_pages' ) ) return;
    $msg = '';
    if ( isset( $_POST['pw_create'] ) && check_admin_referer( 'pw_create_pages' ) ) {
        $made = pw_admin_create_pages( 'draft' );
        $msg  = $made ? '下書きを作成しました：' . implode( ', ', $made ) : '作成が必要なページはありませんでした。';
    }
    echo '<div class="wrap"><h1>SEOページ管理</h1>';
    if ( $msg ) echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
    echo '<p>登録表：<code>inc/pw-pages.php</code>　共通データ・要確認：<code>inc/pw-data.php</code>　本文：<code>seo-pages/{スラッグ}.php</code></p>';
    echo '<form method="post">';
    wp_nonce_field( 'pw_create_pages' );
    echo '<p><button class="button button-primary" name="pw_create" value="1">未作成のページを「下書き」で一括作成</button>　公開は各ページの編集画面で「公開」を押すだけです（本文は空のままでOK）。</p>';
    echo '</form>';
    echo '<table class="widefat striped"><thead><tr><th>#</th><th>ページ</th><th>URL</th><th>title</th><th>状態</th></tr></thead><tbody>';
    foreach ( pw_pages() as $slug => $p ) {
        $state = '—';
        if ( $p['kind'] === 'page' ) {
            $post  = get_page_by_path( $slug, OBJECT, 'page' );
            $file  = file_exists( get_template_directory() . '/seo-pages/' . $slug . '.php' );
            $state = ! $file ? 'テンプレート未作成' : ( ! $post ? '未作成' : ( $post->post_status === 'publish' ? '<b style="color:#1a7f37">公開中</b>' : esc_html( $post->post_status ) ) );
            if ( $post ) $state .= ' <a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">編集</a> / <a href="' . esc_url( get_permalink( $post->ID ) ) . '" target="_blank">表示</a>';
        } else {
            $state = $p['kind'] === 'front' ? 'TOP（front-page.php）' : '一覧ページ（テーマで表示）';
        }
        echo '<tr><td>' . esc_html( $p['id'] ) . '</td><td>' . esc_html( $p['crumb'] ) . '</td><td><code>' . esc_html( $slug === 'home' ? '/' : '/' . $slug . '/' ) . '</code></td><td>' . esc_html( $p['title'] ) . '</td><td>' . $state . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '<h2>要確認事項</h2><p>確認が取れたら <code>inc/pw-data.php</code> の <code>confirm</code> で該当キーを <code>\'ok\' => true</code> にすると、その部分が一般公開されます。</p><table class="widefat striped"><thead><tr><th>キー</th><th>内容</th><th>状態</th></tr></thead><tbody>';
    foreach ( pw( 'confirm' ) as $k => $c ) {
        echo '<tr><td><code>' . esc_html( $k ) . '</code></td><td>' . esc_html( $c['label'] ) . '</td><td>' . ( $c['ok'] ? '<b style="color:#1a7f37">確認済み</b>' : '<b style="color:#b32d2e">未確認（非公開）</b>' ) . '</td></tr>';
    }
    echo '</tbody></table></div>';
}

/** 管理者が閲覧中のページに未確認箇所があれば、管理バーに件数を出す */
function pw_admin_bar( $bar ) {
    if ( is_admin() || ! pw_is_editor() || ! pw_current_slug() ) return;
    $bar->add_node( array( 'id' => 'pw-seo', 'title' => 'SEOページ管理', 'href' => admin_url( 'tools.php?page=pw-seo-pages' ) ) );
}
add_action( 'admin_bar_menu', 'pw_admin_bar', 90 );

/* =====================================================================
 * 11. ヘッダー・フッターのナビ（ページが公開されたら自動で単独ページへ切り替わる）
 * ===================================================================== */

/** ヘッダーナビ：オンライン秘書とは／プログラム／料金／修了生の声／よくある質問 */
function pw_header_nav( $voice_href ) {
    $home = home_url( '/' );
    $items = array();
    $items[] = array( 'preworksについて', pw_nav_url( 'about', $home . '#about' ) );
    $items[] = array( 'プログラム', pw_nav_url( 'program', $home . '#program' ) );
    $items[] = array( '料金', pw_nav_url( 'price', $home . '#plans' ) );
    $items[] = array( '修了生の声', $voice_href );
    $items[] = array( 'よくある質問', home_url( '/faq/' ) );
    foreach ( $items as $it ) echo '<a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a>' . "\n    ";
}

/** フッター：「講座について」「オンライン秘書を知る」の2列（公開済みページだけ並ぶ） */
function pw_footer_columns( $voice_href, $mentor_href ) {
    $home = home_url( '/' );
    $col1 = array(
        array( 'preworksについて', pw_nav_url( 'about', $home . '#about' ) ),
        array( 'プログラム・カリキュラム', pw_nav_url( 'program', $home . '#program' ) ),
        array( '料金・プラン', pw_nav_url( 'price', $home . '#plans' ) ),
        array( '修了後の働き方', pw_is_live( 'career' ) ? pw_url( 'career' ) : '' ),
        array( '無料オンライン相談', pw_is_live( 'consultation' ) ? pw_url( 'consultation' ) : '' ),
        array( '修了生の声', $voice_href ),
        array( 'メンター紹介', $mentor_href ),
        array( 'よくある質問', home_url( '/faq/' ) ),
        array( 'お問い合わせ', home_url( '/contact/' ) ),
    );
    if ( function_exists( 'preworks_has_blog_posts' ) && preworks_has_blog_posts() ) $col1[] = array( 'コラム', pw_url( 'column' ) );
    $col2 = array();
    if ( pw_is_live( 'guide' ) && function_exists( 'pw_guide_has_posts' ) && pw_guide_has_posts() ) $col2[] = array( 'オンライン秘書ガイド', pw_url( 'guide' ) );
    foreach ( array( 'for-mama', 'for-40s-50s', 'for-side-job', 'check' ) as $s ) {
        if ( pw_is_live( $s ) ) {
            $p = pw_page( $s );
            $col2[] = array( $p['crumb'], pw_url( $s ) );
        }
    }
    echo '<div><p class="ftr-h">About the course</p><p class="pw-ftr-sub">講座について</p><ul>';
    foreach ( $col1 as $it ) { if ( $it[1] ) echo '<li><a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a></li>'; }
    echo '</ul></div>';
    if ( $col2 ) {
        echo '<div><p class="ftr-h">For you</p><p class="pw-ftr-sub">こんな方へ</p><ul>';
        foreach ( $col2 as $it ) echo '<li><a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a></li>';
        echo '</ul></div>';
    }
}
