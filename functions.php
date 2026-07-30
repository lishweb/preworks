<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// テーマ画像ディレクトリ
define( 'PREWORKS_IMG', get_template_directory_uri() . '/assets/img' );

function preworks_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
    register_nav_menus( array( 'primary' => 'メインメニュー' ) );
    add_image_size( 'preworks_card', 800, 600, true );
}
add_action( 'after_setup_theme', 'preworks_setup' );

function preworks_assets() {
    // フォント
    wp_enqueue_style( 'preworks-fonts', 'https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700;900&family=Space+Grotesk:wght@400;500;700&display=swap', array(), null );
    // メインCSS
    wp_enqueue_style( 'preworks-main', get_template_directory_uri() . '/assets/css/_shared.css', array(), '1.0.4' );
    // GSAP（任意・トップのアニメ用）
    wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );
    wp_enqueue_script( 'gsap-st', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array( 'gsap' ), '3.12.5', true );
    // メインJS
    wp_enqueue_script( 'preworks-main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.4', true );
}
add_action( 'wp_enqueue_scripts', 'preworks_assets' );

// <html> に j クラス（CSSの .j 前提）
function preworks_html_class( $output ) { return $output . ' j'; }
add_filter( 'language_attributes', function( $out ){ return $out; } );
add_action( 'wp_head', function(){ echo "<script>document.documentElement.className+=' j';</script>\n"; }, 1 );

// テーマ有効化時に固定ページを自動生成し、トップ／投稿ページを設定
function preworks_setup_pages() {
    $pages = array(
        'privacy' => 'プライバシーポリシー',
        'terms' => '利用規約',
        'legal' => '特定商取引法に基づく表記',
    );
    foreach ( $pages as $slug => $title ) {
        if ( ! get_page_by_path( $slug ) ) {
            wp_insert_post( array(
                'post_type'   => 'page',
                'post_status' => 'publish',
                'post_name'   => $slug,
                'post_title'  => $title,
                'post_content'=> '',
            ) );
        }
    }
    // トップページ（front-page.php が自動適用）
    $home = get_page_by_path( 'home' );
    if ( ! $home ) {
        $home_id = wp_insert_post( array( 'post_type'=>'page','post_status'=>'publish','post_name'=>'home','post_title'=>'Home','post_content'=>'' ) );
    } else { $home_id = $home->ID; }
    // ブログは公開対象外のため自動生成しない
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home_id );
}
add_action( 'after_switch_theme', 'preworks_setup_pages' );

// Organization / WebSite 構造化データ（最低限）
function preworks_jsonld() {
    $home = home_url( '/' );
    echo '<script type="application/ld+json">' . wp_json_encode( array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'オンライン秘書養成講座 Preworks（LISH株式会社）',
        'url'      => $home,
        'logo'     => PREWORKS_IMG . '/logo.png',
    ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'preworks_jsonld' );

// ===== WordPress構築の知見反映（Googleシート/吉田さん） =====
// お知らせ（/news）を Custom Post Type に。SEO対象外の告知用。標準投稿は「コラム（SEO対象）」として運用。
function preworks_register_news() {
    register_post_type( 'news', array(
        'labels' => array(
            'name' => 'お知らせ', 'singular_name' => 'お知らせ',
            'add_new' => '新規追加', 'add_new_item' => 'お知らせを追加', 'edit_item' => 'お知らせを編集', 'all_items' => 'お知らせ一覧',
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-megaphone',
        'menu_position' => 5,
        'rewrite' => array( 'slug' => 'news', 'with_front' => false ),
        'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'show_in_rest' => true,
    ) );
}
add_action( 'init', 'preworks_register_news' );

// パーマリンクを Post ID 形式に（日本語スラッグ回避＝吉田さん要望。固定ページは従来どおりスラッグ /about/ 等）
function preworks_set_permalink() {
    if ( get_option( 'permalink_structure' ) !== '/%post_id%/' ) {
        update_option( 'permalink_structure', '/%post_id%/' );
        global $wp_rewrite; $wp_rewrite->init(); $wp_rewrite->flush_rules();
    }
}
add_action( 'after_switch_theme', 'preworks_set_permalink' );

// トップページの <title> を指定文言に固定（SEOプラグイン導入時はそちらが優先されます）
add_filter( 'document_title_parts', function( $parts ) {
    if ( is_front_page() ) {
        $parts['title'] = 'オンライン秘書育成プログラム preworks ｜未経験から最短2ヵ月でオンライン秘書デビュー';
        unset( $parts['tagline'], $parts['site'] );
    }
    return $parts;
} );

// OGP / Twitterカード（SEOプラグイン未導入時のフォールバック。SEO SIMPLE PACK 等でOGP出力するなら本関数を削除）
function preworks_ogp() {
    if ( is_admin() || is_feed() ) return;
    $title = wp_get_document_title();
    $url   = is_singular() ? get_permalink() : home_url( '/' );
    $img   = PREWORKS_IMG . '/og.jpg';
    if ( is_singular() && has_post_thumbnail() ) {
        $t = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
        if ( $t ) { $img = $t[0]; }
    }
    $desc = get_bloginfo( 'description' );
    if ( is_singular() ) {
        $e = wp_strip_all_tags( get_the_excerpt() );
        if ( $e ) { $desc = mb_substr( $e, 0, 120 ); }
    }
    echo '<meta property="og:locale" content="ja_JP">' . "\n";
    echo '<meta property="og:type" content="' . ( is_front_page() ? 'website' : 'article' ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
}
add_action( 'wp_head', 'preworks_ogp', 5 );
