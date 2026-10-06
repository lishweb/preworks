<?php
/**
 * オンライン秘書ガイド（/guide/）
 * ------------------------------------------------------------------
 * コラム（通常の投稿）のうち「ガイドに掲載」にチェックした記事を、
 * /guide/ の目次ページにセクションごとにまとめて表示する仕組み。
 *  - 記事のURLはコラムのまま変わらない（目次でまとめ直すだけ）。
 *  - 投稿の編集画面の右側「オンライン秘書ガイド」欄で、掲載・セクション・並び順を指定。
 *  - 掲載した記事は、パンくずが「トップ ／ オンライン秘書ガイド ／ 記事」になり、
 *    見出し上のラベル・最終更新日・同じセクションの記事一覧が表示される。
 *  - /guide/ は固定ページ（スラッグ guide）。本文は seo-pages/guide.php。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** ガイドのセクション（目次の並び順）。増やすときはここに1行追加 */
function pw_guide_sections() {
    return array(
        'basics' => array( 'name' => 'オンライン秘書を知る',     'desc' => '仕事内容や働き方、必要なスキルなど、まず知っておきたい基本です。' ),
        'start'  => array( 'name' => 'オンライン秘書になるには', 'desc' => '未経験からの始め方、副業での始め方、学ぶ場所の選び方です。' ),
        'money'  => array( 'name' => 'お金と働き方',             'desc' => '報酬の決まり方や収入の目安です。' ),
        'work'   => array( 'name' => '業務別の仕事内容',         'desc' => 'オンライン秘書が担当する代表的な業務を、分野ごとに紹介します。' ),
        'words'  => array( 'name' => '用語集',                   'desc' => '仕事でよく使う言葉をまとめています。' ),
    );
}

/* ---------- 投稿の編集画面 ---------- */

function pw_guide_metabox() {
    add_meta_box( 'pw_guide_box', 'オンライン秘書ガイド', 'pw_guide_metabox_html', 'post', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'pw_guide_metabox' );

function pw_guide_metabox_html( $post ) {
    wp_nonce_field( 'pw_guide_save', 'pw_guide_nonce' );
    $on  = get_post_meta( $post->ID, '_pw_guide', true );
    $sec = get_post_meta( $post->ID, '_pw_guide_section', true );
    $ord = get_post_meta( $post->ID, '_pw_guide_order', true );
    echo '<p><label><input type="checkbox" name="pw_guide" value="1"' . checked( $on, '1', false ) . '> <b>ガイドに掲載する</b></label></p>';
    echo '<p><label>セクション<br><select name="pw_guide_section" style="width:100%">';
    foreach ( pw_guide_sections() as $k => $s ) echo '<option value="' . esc_attr( $k ) . '"' . selected( $sec, $k, false ) . '>' . esc_html( $s['name'] ) . '</option>';
    echo '</select></label></p>';
    echo '<p><label>並び順（小さい順）<br><input type="number" name="pw_guide_order" value="' . esc_attr( $ord !== '' ? $ord : 10 ) . '" style="width:100%"></label></p>';
    echo '<p style="font-size:12px;color:#666">チェックすると /guide/ の目次に並びます。記事のURLは変わりません。</p>';
}

function pw_guide_save( $post_id ) {
    if ( ! isset( $_POST['pw_guide_nonce'] ) || ! wp_verify_nonce( $_POST['pw_guide_nonce'], 'pw_guide_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    update_post_meta( $post_id, '_pw_guide', empty( $_POST['pw_guide'] ) ? '' : '1' );
    if ( isset( $_POST['pw_guide_section'] ) ) {
        $sec = sanitize_key( wp_unslash( $_POST['pw_guide_section'] ) );
        if ( isset( pw_guide_sections()[ $sec ] ) ) update_post_meta( $post_id, '_pw_guide_section', $sec );
    }
    if ( isset( $_POST['pw_guide_order'] ) ) update_post_meta( $post_id, '_pw_guide_order', (int) $_POST['pw_guide_order'] );
}
add_action( 'save_post_post', 'pw_guide_save' );

/** 投稿一覧に「ガイド」列 */
add_filter( 'manage_post_posts_columns', function( $c ) { $c['pw_guide'] = 'ガイド'; return $c; } );
add_action( 'manage_post_posts_custom_column', function( $col, $id ) {
    if ( $col !== 'pw_guide' || ! pw_is_guide_post( $id ) ) return;
    $s = pw_guide_sections(); $k = get_post_meta( $id, '_pw_guide_section', true );
    echo esc_html( isset( $s[ $k ] ) ? $s[ $k ]['name'] : '掲載' ) . '（' . (int) get_post_meta( $id, '_pw_guide_order', true ) . '）';
}, 10, 2 );

/* ---------- 取得 ---------- */

function pw_is_guide_post( $id = null ) {
    $id = $id ? $id : get_the_ID();
    return get_post_type( $id ) === 'post' && get_post_meta( $id, '_pw_guide', true ) === '1';
}

function pw_guide_section_of( $id ) {
    $k = get_post_meta( $id, '_pw_guide_section', true );
    $s = pw_guide_sections();
    return isset( $s[ $k ] ) ? $k : 'basics';
}

/** セクションごとのガイド記事（公開済み、並び順） */
function pw_guides_by_section() {
    $q = get_posts( array(
        'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1,
        'meta_key' => '_pw_guide_order', 'orderby' => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ),
        'meta_query' => array( array( 'key' => '_pw_guide', 'value' => '1' ) ),
    ) );
    $out = array();
    foreach ( $q as $p ) $out[ pw_guide_section_of( $p->ID ) ][] = $p;
    return $out;
}

/** ガイドに掲載された公開記事があるか（ナビの表示判定用） */
function pw_guide_has_posts() {
    return (bool) get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_pw_guide', 'value' => '1' ) ) ) );
}

/* ---------- ガイド記事の表示（single.php から呼ぶ） ---------- */

/** パンくず（ガイド記事のとき） */
function pw_guide_breadcrumb( $id ) {
    return '<a href="' . esc_url( home_url( '/' ) ) . '">トップ</a> ／ <a href="' . esc_url( pw_url( 'guide' ) ) . '">オンライン秘書ガイド</a> ／ 記事';
}

/** 見出し上のラベル */
function pw_guide_eyebrow( $id ) {
    $s = pw_guide_sections();
    return 'Guide ／ ' . $s[ pw_guide_section_of( $id ) ]['name'];
}

/** 本文の後ろ：同じセクションの記事一覧＋目次へのリンク */
function pw_guide_after_content( $id ) {
    $sec    = pw_guide_section_of( $id );
    $groups = pw_guides_by_section();
    $name   = pw_guide_sections()[ $sec ]['name'];
    if ( ! empty( $groups[ $sec ] ) && count( $groups[ $sec ] ) > 1 ) {
        echo '<nav class="pw-guide-nav" aria-label="' . esc_attr( $name ) . '"><p>' . esc_html( $name ) . 'のガイド</p><ul>';
        foreach ( $groups[ $sec ] as $g ) {
            echo $g->ID === $id ? '<li class="is-current">' . esc_html( get_the_title( $g ) ) . '</li>' : '<li><a href="' . esc_url( get_permalink( $g ) ) . '">' . esc_html( get_the_title( $g ) ) . '</a></li>';
        }
        echo '</ul></nav>';
    }
    echo '<div class="center" style="margin-top:28px;"><a class="morelink" href="' . esc_url( pw_url( 'guide' ) ) . '">オンライン秘書ガイドの目次へ</a></div>';
}

/** ガイド記事の BreadcrumbList（記事ページの構造化データ） */
function pw_guide_jsonld() {
    if ( ! is_singular( 'post' ) || ! pw_is_guide_post( get_queried_object_id() ) ) return;
    $id = get_queried_object_id();
    $list = array(
        array( '@type' => 'ListItem', 'position' => 1, 'name' => 'トップ', 'item' => home_url( '/' ) ),
        array( '@type' => 'ListItem', 'position' => 2, 'name' => 'オンライン秘書ガイド', 'item' => pw_url( 'guide' ) ),
        array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title( $id ), 'item' => get_permalink( $id ) ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'pw_guide_jsonld', 5 );
