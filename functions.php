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
    add_image_size( 'preworks_card', 800, 600, true );      // 4:3 横長（修了生の声・ブログのカード）
    add_image_size( 'preworks_portrait', 960, 1200, true ); // 4:5 縦長（メンターの顔写真）
}
add_action( 'after_setup_theme', 'preworks_setup' );

function preworks_assets() {
    // フォント
    wp_enqueue_style( 'preworks-fonts', 'https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700;900&family=Space+Grotesk:wght@400;500;700&display=swap', array(), null );
    // メインCSS
    wp_enqueue_style( 'preworks-main', get_template_directory_uri() . '/assets/css/_shared.css', array(), '1.0.17' );
    // GSAP（任意・トップのアニメ用）
    wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );
    wp_enqueue_script( 'gsap-st', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array( 'gsap' ), '3.12.5', true );
    // メインJS
    wp_enqueue_script( 'preworks-main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.15', true );
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

/* =========================================================================
 * 修了生の声（voice） v1.0.12（1.0.13で継続）
 * ------------------------------------------------------------------------
 * テスト環境 1.3.50 から移植。坂さん依頼「本番のWordPressにも修了生の声の実装」
 *
 * 移植したもの
 *   ・カスタム投稿タイプ voice（rewrite slug=voice / with_front=false）
 *   ・かんたん入力メタボックス（本文やHTMLを書かずに項目を埋めるだけ）
 *   ・アイコン選択（メディアライブラリ）
 *   ・Q&A吹き出しのレンダラー（話者プレフィックス方式・同一話者の自動結合）
 *   ・入稿用ショートコード [vvideo][vprofile][vq][vyou][vcs]
 *
 * テンプレート： single-voice.php / archive-voice.php
 * トップページ： front-page.php の「⑨ 参加者の声」を voice 連動に変更
 *                （0件のあいだは従来の固定3枚を表示するフォールバック付き）
 * ========================================================================= */

// ===== 修了生の声(カスタム投稿タイプ) 投稿・固定ページと別管理・左メニュー独立 =====
function preworks_register_voice() {
    register_post_type( 'voice', array(
        'labels' => array(
            'name' => '修了生の声', 'singular_name' => '修了生の声',
            'add_new' => '新規追加', 'add_new_item' => '修了生の声を追加',
            'edit_item' => '修了生の声を編集', 'all_items' => '修了生の声一覧',
            'menu_name' => '修了生の声', 'view_item' => '修了生の声を表示',
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-format-quote',
        'menu_position' => 6,
        'rewrite' => array( 'slug' => 'voice', 'with_front' => false ),
        'supports' => array( 'title', 'thumbnail' ),
        'show_in_rest' => false,
    ) );
}
add_action( 'init', 'preworks_register_voice' );

/*
 * voice と mentor を追加したので、リライトルールを一度だけ再生成する。
 * ⚠️ テーマを「有効化」せずファイルだけ差し替える運用では after_switch_theme が
 *    走らないため、これが無いと /voice/ と /voice/○○/ が 404 になる
 *    （通常は「設定 → パーマリンク → 変更を保存」で直すが、忘れると坂さんに
 *      壊れたリンクが見える。ここで自動化しておく）。
 * soft flush（第2引数 false）なので .htaccess は書き換えない。
 * 一度実行したらフラグを立て、以後は走らない。
 */
function preworks_flush_rewrite_1013() {
    if ( get_option( 'preworks_rewrite_rev' ) === '1013' ) return;
    flush_rewrite_rules( false );
    update_option( 'preworks_rewrite_rev', '1013' );
}
add_action( 'admin_init', 'preworks_flush_rewrite_1013' );
/*
 * そのカスタム投稿タイプに公開済みの記事があるか。v1.0.13
 * ヘッダー・フッターのリンク先の切り替えに使う。
 *   記事0件 → 従来どおりトップページ内のアンカー（#voice / #mentor）
 *   記事あり → 専用ページ（/voice/ / /mentor/）
 * 入稿が終わるまでナビが空ページを指してしまうのを防ぐ。
 * 記事が公開された時点で自動的に切り替わるので、再反映は不要。
 * wp_count_posts() はWordPress側でキャッシュされるため負荷にならない。
 */
function preworks_has_cpt_posts( $type ) {
    static $cache = array();
    if ( isset( $cache[ $type ] ) ) return $cache[ $type ];
    $counts = wp_count_posts( $type );
    $cache[ $type ] = ( $counts && ! empty( $counts->publish ) );
    return $cache[ $type ];
}


/*
 * 修了生の声のスラッグ検査（v1.0.12）
 * 日本語スラッグ（/voice/大城さん/）は共有時に %E5%A4%A7%E5%9F%8E… に変換され、
 * SNSやメールで扱いづらい。公開前に英字へ直してもらうための警告。
 * 公開は止めない（入稿を妨げない）。
 *
 * ・ブロックエディタでは admin_notices の表示位置が安定しないため、
 *   かんたん入力メタボックスの中にも同じ警告を出す（preworks_slug_warning_box）。
 * ・/voice/○○/ は第2階層なので、固定ページとのスラッグ衝突は起きない。
 *   よって衝突チェックは行わない（投稿を本番に載せる段階で別途必要になる）。
 */
function preworks_slug_post_types() {
    return array( 'voice', 'mentor' );
}

function preworks_slug_warnings( $post_id ) {
    $warn = array();
    $post = get_post( $post_id );
    if ( ! $post || 'auto-draft' === $post->post_status ) return $warn;
    if ( ! in_array( $post->post_type, preworks_slug_post_types(), true ) ) return $warn;
    $slug = urldecode( (string) $post->post_name );
    if ( '' === $slug ) return $warn;
    $ex = ( 'mentor' === $post->post_type ) ? 'nakasone-ikuko' : 'oshiro-san';
    if ( ! preg_match( '/^[a-z0-9\-]+$/', $slug ) ) {
        $warn[] = '<strong>ページURL（スラッグ）をご確認ください。</strong><br>'
                . '現在のスラッグ：<code>' . esc_html( $slug ) . '</code><br>'
                . '日本語のURLは共有時に長い記号列（%E3%82…）に変換され、SNSやメールで扱いづらくなります。'
                . '<strong>英小文字・数字・ハイフン（-）</strong>だけで設定してください。'
                . '例：<code>' . esc_html( $ex ) . '</code><br>'
                . '右パネルの「URL」欄（クラシック画面ではタイトル下の「パーマリンク」）から変更できます。'
                . '<strong>公開後の変更はSNS等の共有リンクが開けなくなるため、公開前に確定してください。</strong>';
    }
    return $warn;
}

function preworks_slug_admin_notice() {
    if ( ! function_exists( 'get_current_screen' ) ) return;
    $screen = get_current_screen();
    if ( ! $screen || 'post' !== $screen->base ) return;
    if ( ! in_array( $screen->post_type, preworks_slug_post_types(), true ) ) return;
    global $post;
    if ( ! $post ) return;
    foreach ( preworks_slug_warnings( $post->ID ) as $w ) {
        echo '<div class="notice notice-error"><p>' . $w . '</p></div>';
    }
}
add_action( 'admin_notices', 'preworks_slug_admin_notice' );

// メタボックス内に出す警告（レイアウトに関係なく必ず見える場所）
function preworks_slug_warning_box( $post_id ) {
    $warn = preworks_slug_warnings( $post_id );
    if ( ! $warn ) return '';
    $html = '<div style="background:#fcf0f1;border:1px solid #d63638;border-left-width:4px;padding:12px 14px;border-radius:4px;font-size:13px;line-height:1.8;margin:0 0 14px;">';
    foreach ( $warn as $i => $w ) {
        $html .= ( $i ? '<hr style="border:0;border-top:1px solid #f0c8c9;margin:10px 0;">' : '' )
               . '<span style="color:#d63638;font-weight:700;">⚠️ </span>' . $w;
    }
    return $html . '</div>';
}



// ===== 修了生の声 かんたん入力欄(全項目ラベル付き・本文やHTML不要) =====
function preworks_voice_metabox() {
    add_meta_box( 'preworks_voice_fields', '修了生の声（かんたん入力）', 'preworks_voice_metabox_html', 'voice', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'preworks_voice_metabox' );
// ===== かんたん入力欄：アイコン選択（メディアライブラリ）=====
function preworks_admin_media_enqueue( $hook ) {
    $screen = get_current_screen();
    if ( $screen && in_array( $screen->post_type, array( 'voice', 'mentor' ), true ) ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'preworks_admin_media_enqueue' );

function preworks_media_picker_assets() {
    static $done = false;
    if ( $done ) return;
    $done = true;
    ?>
<style>
.pw-media{display:flex;align-items:center;gap:10px;margin:2px 0 0;}
.pw-media .pw-media-prev{width:56px;height:56px;border-radius:50%;object-fit:cover;border:1px solid #ddd;background:#fafafa;}
.pw-media .pw-media-none{width:56px;height:56px;border-radius:50%;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;color:#bbb;font-size:11px;}
</style>
<script>
jQuery(function($){
  $(document).on('click','.pw-media-pick',function(e){
    e.preventDefault();
    var $w=$(this).closest('.pw-media');
    var f=wp.media({title:'アイコン画像を選択',button:{text:'このアイコンを使う'},library:{type:'image'},multiple:false});
    f.on('select',function(){
      var a=f.state().get('selection').first().toJSON();
      var u=(a.sizes&&a.sizes.thumbnail)?a.sizes.thumbnail.url:a.url;
      $w.find('input.pw-media-id').val(a.id);
      $w.find('.pw-media-none').hide();
      $w.find('.pw-media-prev').attr('src',u).show();
      $w.find('.pw-media-clear').show();
    });
    f.open();
  });
  $(document).on('click','.pw-media-clear',function(e){
    e.preventDefault();
    var $w=$(this).closest('.pw-media');
    $w.find('input.pw-media-id').val('');
    $w.find('.pw-media-prev').hide();
    $w.find('.pw-media-none').show();
    $(this).hide();
  });
});
</script>
    <?php
}

function preworks_media_field( $field, $value ) {
    preworks_media_picker_assets();
    $id  = absint( $value );
    $url = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
    echo '<div class="pw-media">';
    echo '<div class="pw-media-none"' . ( $url ? ' style="display:none"' : '' ) . '>未設定</div>';
    echo '<img class="pw-media-prev" src="' . esc_url( $url ) . '" alt=""' . ( $url ? '' : ' style="display:none"' ) . '>';
    echo '<input type="hidden" class="pw-media-id" name="' . esc_attr( $field ) . '" value="' . esc_attr( $id ? $id : '' ) . '">';
    echo '<button type="button" class="button pw-media-pick">画像を選択</button>';
    echo '<button type="button" class="button-link pw-media-clear"' . ( $url ? '' : ' style="display:none"' ) . '>削除</button>';
    echo '</div>';
}

function preworks_voice_metabox_html( $post ) {
    wp_nonce_field( 'preworks_voice_save', 'preworks_voice_nonce' );
    echo preworks_slug_warning_box( $post->ID );
    $catch   = get_post_meta( $post->ID, '_pw_catch', true );
    $youtube = get_post_meta( $post->ID, '_pw_youtube', true );
    $profile = get_post_meta( $post->ID, '_pw_profile', true );
    $qa      = get_post_meta( $post->ID, '_pw_qa', true );
    $attr    = get_post_meta( $post->ID, '_pw_attr', true );
    $hl      = get_post_meta( $post->ID, '_pw_hl', true );
    $comment = get_post_meta( $post->ID, '_pw_comment', true );
    $name_l  = get_post_meta( $post->ID, '_pw_name_l', true );
    $name_r  = get_post_meta( $post->ID, '_pw_name_r', true );
    $icon_l  = get_post_meta( $post->ID, '_pw_icon_l', true );
    $icon_r  = get_post_meta( $post->ID, '_pw_icon_r', true );
    $ph = "■受講のきっかけは？\n質問者：まずは、受講しようと思ったきっかけを教えてください。\n本人：「在宅ワーク 未経験」で検索しては、怪しい広告ばかりの毎日でした。\n\nそんなときに、preworksの無料相談を見つけました。\n質問者：**現場を運営している会社**が監修している点が決め手だったんですね。";
    $s = 'style="width:100%;max-width:640px"';
    echo '<style>.pwf label{display:block;margin:14px 0 4px;font-weight:600}.pwf .hint{color:#888;font-size:12px;font-weight:400}.pwf hr{margin:20px 0;border:0;border-top:1px solid #eee}</style>';
    echo '<div class="pwf">';
    echo '<p style="background:#fff8ef;border:1px solid #f0e2cf;padding:10px 14px;border-radius:6px;font-size:13px;">下の欄を埋めるだけでページが完成します。<b>本文（下のエディタ）は使いません。</b><br>①タイトル＝修了生名（画面上の「タイトルを追加」）／②アイキャッチ画像＝修了生の写真（吹き出しのアイコンになります）<br><b>写真は横長で用意してください（推奨 1200×900＝4:3）。</b></p>';
    echo preworks_detail_ready_field( $post->ID, '詳細はこちら ▶' );
    echo '<label>キャッチコピー <span class="hint">（詳細ページ上部の大きな見出し。例：「私なんか」が、変わった2ヶ月。）</span></label>';
    echo '<input type="text" name="pw_catch" value="' . esc_attr( $catch ) . '" ' . $s . '>';
    echo '<label>YouTube動画ID <span class="hint">（https://youtu.be/XXXX の XXXX 部分だけ。動画なしなら空欄）</span></label>';
    echo '<input type="text" name="pw_youtube" value="' . esc_attr( $youtube ) . '" ' . $s . '>';
    echo '<label>プロフィール <span class="hint">（Profile枠に出る紹介文）</span></label>';
    echo '<textarea name="pw_profile" rows="2" ' . $s . '>' . esc_textarea( $profile ) . '</textarea>';
    echo '<hr><p style="font-weight:700;margin:0 0 4px;">▼ 吹き出しの名前とアイコン</p>';
    echo '<p style="font-size:12px;color:#666;margin:0 0 6px;">記事ごとに変更できます。空欄のままなら（　）内が使われます。</p>';
    echo '<label>左（本人）の名前 <span class="hint">（空欄＝タイトルの文字。タイトルと違う表示名にしたいときに入力）</span></label>';
    echo '<input type="text" name="pw_name_l" value="' . esc_attr( $name_l ) . '" ' . $s . ' placeholder="' . esc_attr( get_the_title( $post ) ) . '">';
    echo '<label>左（本人）のアイコン <span class="hint">（空欄＝アイキャッチ画像）</span></label>';
    preworks_media_field( 'pw_icon_l', $icon_l );
    echo '<label>右（聞き手）の名前 <span class="hint">（空欄＝質問者）</span></label>';
    echo '<input type="text" name="pw_name_r" value="' . esc_attr( $name_r ) . '" ' . $s . ' placeholder="質問者">';
    echo '<label>右（聞き手）のアイコン <span class="hint">（空欄＝名前の頭文字の丸バッジ）</span></label>';
    preworks_media_field( 'pw_icon_r', $icon_r );
    echo '<hr><label>インタビュー（Q&amp;A・会話）</label>';
    echo '<div style="background:#fff8ef;border:1px solid #f0e2cf;padding:10px 14px;border-radius:6px;font-size:12.5px;line-height:1.9;margin:0 0 6px;max-width:640px;">'
       . '<b>書き方</b><br>'
       . '・<code>■</code> で始まる行 ＝ 質問見出し<br>'
       . '・<code>質問者：</code> で始まる行 ＝ <b>右</b>の吹き出しが始まる<br>'
       . '・<code>本人：</code> で始まる行 ＝ <b>左</b>の吹き出しが始まる<br>'
       . '・記号を付けない行 ＝ <b>直前の吹き出しの続き（改行）</b><br>'
       . '・空行 ＝ 吹き出しの中の<b>一行スペース</b>（同じ人の発言が続くかぎり1つの吹き出しにまとまります）<br>'
       . '・<code>---</code> だけの行 ＝ 同じ人でも<b>そこで吹き出しを分ける</b><br>'
       . '・装飾： <code>**太字**</code> ／ <code>__下線__</code> ／ <code>==マーカー==</code> ／ <code>[表示文字](URL)</code>'
       . '</div>';
    echo '<textarea name="pw_qa" rows="12" ' . $s . ' placeholder="' . esc_attr( $ph ) . '">' . esc_textarea( $qa ) . '</textarea>';
    echo '<hr><p style="font-weight:700;margin:0 0 4px;">▼ 一覧カードに出る内容</p>';
    echo '<label>属性 <span class="hint">（例：30代・2児ママ｜前職：コールセンター）</span></label>';
    echo '<input type="text" name="pw_attr" value="' . esc_attr( $attr ) . '" ' . $s . '>';
    echo '<label>ハイライト <span class="hint">（カードの太字の一言）</span></label>';
    echo '<input type="text" name="pw_hl" value="' . esc_attr( $hl ) . '" ' . $s . '>';
    echo '<label>コメント <span class="hint">（ハイライトの下の説明文）</span></label>';
    echo '<textarea name="pw_comment" rows="2" ' . $s . '>' . esc_textarea( $comment ) . '</textarea>';
    echo '</div>';
}
function preworks_voice_save( $post_id ) {
    if ( ! isset( $_POST['preworks_voice_nonce'] ) || ! wp_verify_nonce( $_POST['preworks_voice_nonce'], 'preworks_voice_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $text = array( 'pw_catch' => '_pw_catch', 'pw_youtube' => '_pw_youtube', 'pw_attr' => '_pw_attr', 'pw_hl' => '_pw_hl', 'pw_name_l' => '_pw_name_l', 'pw_name_r' => '_pw_name_r' );
    foreach ( $text as $k => $m ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $m, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
    }
    $area = array( 'pw_profile' => '_pw_profile', 'pw_comment' => '_pw_comment', 'pw_qa' => '_pw_qa' );
    foreach ( $area as $k => $m ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $m, sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) );
    }
    $ids = array( 'pw_icon_l' => '_pw_icon_l', 'pw_icon_r' => '_pw_icon_r' );
    foreach ( $ids as $k => $m ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $m, absint( $_POST[ $k ] ) );
    }
    // 下層ページの公開チェック。v1.0.18
    // チェックを外すと $_POST にキーが来ないため、hidden の目印で「この画面から
    // 保存された」ことを判定してから 0 を書き込む（他の保存処理で消えないように）。
    if ( isset( $_POST['pw_detail_ready_present'] ) ) {
        update_post_meta( $post_id, '_pw_detail_ready', empty( $_POST['pw_detail_ready'] ) ? '' : '1' );
    }
}
add_action( 'save_post_voice', 'preworks_voice_save' );

// 左右の名前・アイコンをまとめて取得
function preworks_qa_opts( $post_id ) {
    $il = absint( get_post_meta( $post_id, '_pw_icon_l', true ) );
    $ir = absint( get_post_meta( $post_id, '_pw_icon_r', true ) );
    return array(
        'name_l' => (string) get_post_meta( $post_id, '_pw_name_l', true ),
        'name_r' => (string) get_post_meta( $post_id, '_pw_name_r', true ),
        'icon_l' => $il ? (string) wp_get_attachment_image_url( $il, 'thumbnail' ) : '',
        'icon_r' => $ir ? (string) wp_get_attachment_image_url( $ir, 'thumbnail' ) : '',
    );
}

// ===== インライン装飾（ブログ本文・Q&A吹き出しで共通） =====
// **太字** / __下線__ / ==マーカー== / [表示文字](URL)
function preworks_inline_deco( $s ) {
    $s = esc_html( (string) $s );
    $s = preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s );
    $s = preg_replace( '/__(.+?)__/u', '<u>$1</u>', $s );
    $s = preg_replace( '/==(.+?)==/u', '<mark>$1</mark>', $s );
    $s = preg_replace_callback( '/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/u', function ( $m ) {
        return '<a href="' . esc_url( $m[2] ) . '" target="_blank" rel="noopener">' . $m[1] . '</a>';
    }, $s );
    return $s;
}

// 吹き出しアバター（画像があれば画像、無ければ名前の頭文字バッジ）
function preworks_qa_avatar( $img_url, $name, $side ) {
    if ( $img_url ) {
        return '<img class="vc-ava" src="' . esc_url( $img_url ) . '" alt="">';
    }
    $initial = $name !== '' ? mb_substr( $name, 0, 1 ) : ( $side === 'cs' ? '質' : '本' );
    return '<div class="vc-w' . ( $side === 'you' ? ' vc-w-you' : '' ) . '">' . esc_html( $initial ) . '</div>';
}

// Q&A テキストをチャット吹き出しHTMLに変換
// $opts: name_l / icon_l / name_r / icon_r（未指定なら $name・$photo・「質問者」・頭文字バッジ）
function preworks_render_voice_qa( $qa, $photo, $name, $opts = array() ) {
    $o = wp_parse_args( (array) $opts, array(
        'name_l' => '', 'icon_l' => '', 'name_r' => '', 'icon_r' => '',
    ) );
    $name_l = $o['name_l'] !== '' ? $o['name_l'] : (string) $name;
    $icon_l = $o['icon_l'] !== '' ? $o['icon_l'] : (string) $photo;
    $name_r = $o['name_r'] !== '' ? $o['name_r'] : '質問者';
    $icon_r = $o['icon_r'];

    $av_l = '<div class="vc-av">' . preworks_qa_avatar( $icon_l, $name_l, 'you' ) . '<div class="vc-name">' . esc_html( $name_l ) . '</div></div>';
    $av_r = '<div class="vc-av">' . preworks_qa_avatar( $icon_r, $name_r, 'cs' ) . '<div class="vc-name">' . esc_html( $name_r ) . '</div></div>';

    $lines = preg_split( '/\r\n|\r|\n/', (string) $qa );

    // --- 1st pass: 見出し / 吹き出しのブロックに分解 ---
    $items = array();   // array('q'=>text) | array('b'=>side,'lines'=>array()) | array('bar'=>true)
    $buf   = array();
    $side  = '';

    $flush = function () use ( &$buf, &$side, &$items ) {
        while ( $buf && $buf[0] === '' )     { array_shift( $buf ); }
        while ( $buf && end( $buf ) === '' ) { array_pop( $buf ); }
        if ( $buf ) { $items[] = array( 'b' => ( $side === 'cs' ? 'cs' : 'you' ), 'lines' => $buf ); }
        $buf = array(); $side = '';
    };

    foreach ( $lines as $raw ) {
        $t = trim( $raw );

        // 空行 ＝ ブロックの区切り（同じ話者が続く場合は後段で1つにまとめる）
        if ( $t === '' && $raw === '' ) { $flush(); continue; }
        // 空白だけの行 / 「//」だけの行 ＝ 吹き出しの中の一行スペース
        if ( $t === '' || $t === '//' || $t === '／／' ) {
            if ( $side !== '' ) { $buf[] = ''; }
            continue;
        }
        // 「---」だけの行 ＝ 同じ話者でも吹き出しを分ける（結合を禁止する仕切り）
        if ( preg_match( '/^[-ー－―‐]{3,}$/u', $t ) ) { $flush(); $items[] = array( 'bar' => true ); continue; }

        // ※「質問者：」は右の話者。見出しの「質問：」より先に判定する
        $is_speaker_r = (bool) preg_match( '/^(質問者|コンサルタント|コンサル|W|C)[\s:：]/u', $t );
        $is_speaker_l = (bool) preg_match( '/^(本人|A)[\s:：]/u', $t );

        if ( ! $is_speaker_r && ( mb_substr( $t, 0, 1 ) === '■' || preg_match( '/^(Q|質問)[\s:：]/u', $t ) ) ) {
            $flush();
            $items[] = array( 'q' => preg_replace( '/^(■|Q|質問)[\s:：]*/u', '', $t ) );
            continue;
        }
        if ( $is_speaker_r ) {
            $flush();
            $side  = 'cs';
            $buf[] = preg_replace( '/^(質問者|コンサルタント|コンサル|W|C)[\s:：]*/u', '', $t );
            continue;
        }
        if ( $is_speaker_l ) {
            $flush();
            $side  = 'you';
            $buf[] = preg_replace( '/^(本人|A)[\s:：]*/u', '', $t );
            continue;
        }
        // 記号なしの行＝直前の吹き出しの続き（改行）。吹き出しが開いていなければ左（本人）で開始
        if ( $side === '' ) { $side = 'you'; }
        $buf[] = $t;
    }
    $flush();

    // --- 2nd pass: 隣り合う同じ話者の吹き出しを1つにまとめる（間は一行スペース） ---
    $merged = array();
    foreach ( $items as $it ) {
        if ( isset( $it['bar'] ) ) { $merged[] = $it; continue; }
        $prev = $merged ? $merged[ count( $merged ) - 1 ] : null;
        if ( isset( $it['b'] ) && $prev && isset( $prev['b'] ) && $prev['b'] === $it['b'] ) {
            $merged[ count( $merged ) - 1 ]['lines'] = array_merge( $prev['lines'], array( '' ), $it['lines'] );
            continue;
        }
        $merged[] = $it;
    }

    // --- 3rd pass: HTML化 ---
    $out = '';
    foreach ( $merged as $it ) {
        if ( isset( $it['bar'] ) ) continue;
        if ( isset( $it['q'] ) ) {
            $out .= '<div class="vchat-q">' . preworks_inline_deco( $it['q'] ) . '</div>';
            continue;
        }
        $body = implode( '<br>', array_map( 'preworks_inline_deco', $it['lines'] ) );
        $out .= $it['b'] === 'cs'
            ? '<div class="vc-row vc-cs">' . $av_r . '<div class="vc-bubble">' . $body . '</div></div>'
            : '<div class="vc-row vc-you">' . $av_l . '<div class="vc-bubble">' . $body . '</div></div>';
    }
    return $out;
}

// ===== 修了生の声 入稿用ショートコード(divを触らず簡単入力・エディタで崩れない) =====
// [vvideo id="動画ID"] / [vprofile]…[/vprofile] / [vq]質問[/vq] / [vyou img="写真URL"]本文[/vyou] / [vcs]本文[/vcs]
function preworks_sc_vvideo( $atts ) {
    $a = shortcode_atts( array( 'id' => '' ), $atts );
    return '<div style="margin:0 0 32px;position:relative;width:100%;padding-bottom:56.25%;height:0;border-radius:16px;overflow:hidden;box-shadow:0 18px 44px rgba(150,80,45,.14);background:#000;"><iframe style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" src="https://www.youtube.com/embed/' . esc_attr( $a['id'] ) . '" title="修了生インタビュー" loading="lazy" allowfullscreen></iframe></div>';
}
add_shortcode( 'vvideo', 'preworks_sc_vvideo' );
function preworks_sc_vprofile( $atts, $content = null ) {
    return '<div class="callout"><strong>Profile</strong><br>' . wp_kses_post( do_shortcode( $content ) ) . '</div>';
}
add_shortcode( 'vprofile', 'preworks_sc_vprofile' );
function preworks_sc_vq( $atts, $content = null ) {
    return '<div class="vchat-q">' . esc_html( trim( $content ) ) . '</div>';
}
add_shortcode( 'vq', 'preworks_sc_vq' );
function preworks_sc_vyou( $atts, $content = null ) {
    $a = shortcode_atts( array( 'img' => '', 'name' => '' ), $atts );
    $img = $a['img'] ? '<img class="vc-ava" src="' . esc_url( $a['img'] ) . '" alt="">' : '';
    $name = $a['name'] ? '<div class="vc-name">' . esc_html( $a['name'] ) . '</div>' : '';
    return '<div class="vc-row vc-you"><div class="vc-av">' . $img . $name . '</div><div class="vc-bubble">' . wp_kses_post( trim( $content ) ) . '</div></div>';
}
add_shortcode( 'vyou', 'preworks_sc_vyou' );
function preworks_sc_vcs( $atts, $content = null ) {
    $a = shortcode_atts( array( 'img' => '', 'name' => '質問者' ), $atts );
    $nm = $a['name'] !== '' ? $a['name'] : '質問者';
    $av = preworks_qa_avatar( $a['img'], $nm, 'cs' );
    return '<div class="vc-row vc-cs"><div class="vc-av">' . $av . '<div class="vc-name">' . esc_html( $nm ) . '</div></div><div class="vc-bubble">' . wp_kses_post( trim( $content ) ) . '</div></div>';
}
add_shortcode( 'vcs', 'preworks_sc_vcs' );

/* 本番への移植メモ（v1.0.13）
 * 坂さん依頼（9/18）「本番のWordPressにもメンター紹介の実装を」
 * ＋ No.12（9/21・納期9/28）「トップに新メンター2名を追加。4名のみ配置」
 *
 * トップページ（front-page.php）は menu_order 順の上位4件を表示、
 * 一覧（/mentor/）は全件表示。0件のあいだは従来の固定4枚を表示する。
 * 写真は preworks_portrait（960×1200 / 4:5 縦長）を使う。
 */


/* =========================================================================
 * No.9 メンター紹介（カスタム投稿） v1.0.13
 * 「修了生の声」(voice) と同じ作りに揃えている。記事URLは /mentor/スラグ/
 * ========================================================================= */
function preworks_register_mentor() {
    register_post_type( 'mentor', array(
        'labels' => array(
            'name' => 'メンター紹介', 'singular_name' => 'メンター紹介',
            'add_new' => '新規追加', 'add_new_item' => 'メンター紹介を追加',
            'edit_item' => 'メンター紹介を編集', 'all_items' => 'メンター紹介一覧',
            'menu_name' => 'メンター紹介', 'view_item' => 'メンター紹介を表示',
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-groups',
        'menu_position' => 7,
        'rewrite' => array( 'slug' => 'mentor', 'with_front' => false ),
        'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
        'show_in_rest' => false,
    ) );
}
add_action( 'init', 'preworks_register_mentor' );

// メンター一覧(/mentor/)の並び順を「ページ属性 → 順序」に合わせる（v1.3.25）
// トップページ #mentor は menu_order 指定済み。アーカイブは既定の公開日順だったため、
// 5人目の追加や順序変更でトップと一覧の並びがズレる不具合を修正。
function preworks_mentor_archive_order( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) return;
    if ( ! $query->is_post_type_archive( 'mentor' ) ) return;
    $query->set( 'orderby', 'menu_order title' );
    $query->set( 'order', 'ASC' );
    $query->set( 'posts_per_page', -1 );
}
add_action( 'pre_get_posts', 'preworks_mentor_archive_order' );

/*
 * そのメンターの詳細ページを「見せられる状態」か。v1.0.16
 *
 * 判定はキャッチコピー（_pw_catch）が入っているかだけ。
 *   未入力 → 下層が未入稿。カードをリンクにせず、詳細ページは noindex にする。
 *   入力済 → 従来どおりカードから詳細ページへ遷移する。
 *
 * 投稿チームがキャッチコピーを入れて「更新」した時点で自動的に切り替わるので、
 * テーマの再反映は不要。
 *
 * ⚠️ なぜ _pw_catch なのか
 *    単一の必須項目で、かつ詳細ページの <h1> になる項目だから。未入力だと
 *    <h1> が氏名にフォールバックし、その下の行にも氏名が出て重複する。
 *    _pw_profile は「紹介文の使い回し」で埋まっている運用があるため判定に使えない。
 * ⚠️ 既存4名（戸田・玉木・仲宗根・髙松）は全員 _pw_catch が入っていることを
 *    本番サイトで実測済み。この変更による既存ページの表示差はない。
 */
function preworks_entry_is_ready( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    return ( '1' === (string) get_post_meta( $post_id, '_pw_detail_ready', true ) );
}

/*
 * 1.0.18：判定をキャッチコピーの有無から「下層ページを公開する」チェックボックス
 * （_pw_detail_ready）に変更した。
 *
 * ⚠️ 坂さん依頼（9/24）「メンターの下層ページ…記事の作成が途中のため、一度非公開に
 *    できますでしょうか？」に対応するため。既存の記事にはこのメタが無いので、
 *    反映した時点で mentor・voice の全記事が自動的に非公開になる。
 *
 * ⚠️ WordPressの「非公開」ステータスは使わない。それをやると投稿がクエリから外れ、
 *    トップと一覧のカード自体が消えて従来の固定カードに戻ってしまう（No.12の表示が壊れる）。
 *    カードは出したまま、遷移と検索インデックスだけを止めるのが目的。
 */
function preworks_detail_ready_field( $post_id, $linklabel ) {
    $on = preworks_entry_is_ready( $post_id );
    $h  = '<hr><p style="font-weight:700;margin:0 0 4px;">▼ 下層ページ（詳細ページ）の公開</p>';
    $h .= '<label style="display:block;margin:6px 0 4px;font-weight:600;">';
    $h .= '<input type="hidden" name="pw_detail_ready_present" value="1">';
    $h .= '<input type="checkbox" name="pw_detail_ready" value="1"' . ( $on ? ' checked' : '' ) . '> 下層ページを公開する';
    $h .= '</label>';
    $h .= '<p style="font-size:12.5px;line-height:1.9;color:#555;max-width:640px;margin:0 0 4px;">'
        . 'チェックを<b>外している間</b>：一覧とトップのカードは今までどおり表示されますが、'
        . '「' . esc_html( $linklabel ) . '」は出ず、カードをクリックしても移動しません。'
        . '詳細ページのURLを直接開いても一覧ページに転送され、検索結果にも出ません。'
        . '<b>ログイン中の管理者だけは中身を確認できます。</b><br>'
        . '記事を書き終えてからチェックを入れてください。</p>';
    if ( ! $on ) {
        $h = '<div style="background:#fcf9e8;border:1px solid #e0d48a;border-left-width:4px;padding:2px 14px 12px;border-radius:4px;margin:14px 0;">'
           . $h . '<p style="margin:0;font-size:12.5px;color:#7a6a1f;"><b>現在このページは非公開です。</b></p></div>';
    }
    return $h;
}
// 1.0.16 で mentor 専用として作った関数。voice でも同じ判定を使うので中身を
// preworks_entry_is_ready() に移した。呼び出し側はそのままでよい。
function preworks_mentor_is_ready( $post_id = null ) {
    return preworks_entry_is_ready( $post_id );
}

/*
 * 修了生の声をサイトに出してよい状態か。v1.0.17
 *
 * トップの「修了生の声」欄は3枚組。1件だけ公開するとそこが1枚になってしまうため、
 * 公開が3件そろうまでは従来の固定3枚（大城さん・Yさん・Kさん）のままにする。
 * 上部メニューとフッターの「修了生の声」も同じ条件で切り替える。
 * （TOPが従来表示なのにメニューだけ /voice/ を指す、という食い違いを避ける）
 *
 * ⚠️ 坂さんの運用は「3名分を下書きで入稿 → 公開」。3件そろうまで反映されないことを
 *    入稿者が分かるように、管理画面に残り件数を表示する（下の関数）。
 */
function preworks_voice_live_min() {
    return 3;
}
function preworks_voice_published() {
    static $n = null;
    if ( null !== $n ) return $n;
    $c = wp_count_posts( 'voice' );
    $n = ( $c && ! empty( $c->publish ) ) ? (int) $c->publish : 0;
    return $n;
}
function preworks_voice_is_live() {
    return ( preworks_voice_published() >= preworks_voice_live_min() );
}

/*
 * 「修了生の声」の入稿者向けの案内。v1.0.17
 * あと何件公開すればサイトに反映されるかを、一覧・編集画面の上に出す。
 */
function preworks_voice_live_notice() {
    if ( ! function_exists( 'get_current_screen' ) ) return;
    $screen = get_current_screen();
    if ( ! $screen || 'voice' !== $screen->post_type ) return;
    if ( ! in_array( $screen->base, array( 'post', 'edit' ), true ) ) return;
    $n   = preworks_voice_published();
    $min = preworks_voice_live_min();
    if ( $n >= $min ) {
        echo '<div class="notice notice-success"><p><strong>修了生の声は公開中です。</strong>'
           . '公開済み ' . $n . ' 件。トップページと上部メニューに反映されています。</p></div>';
        return;
    }
    $rest = $min - $n;
    echo '<div class="notice notice-warning"><p><strong>修了生の声は、あと ' . $rest . ' 件公開するとサイトに反映されます。</strong>'
       . '（公開済み ' . $n . ' 件／必要 ' . $min . ' 件）<br>'
       . 'トップページの「修了生の声」欄は3枚組です。1件ずつ公開すると欄が1枚だけになってしまうため、'
       . $min . ' 件そろうまでは従来の表示のままにしています。'
       . '<strong>3件を下書きで仕上げてから、まとめて公開してください。</strong></p></div>';
}
add_action( 'admin_notices', 'preworks_voice_live_notice' );

/*
 * 下層が未入稿のメンター詳細ページは、一覧・TOPのどちらからもリンクしていない
 * 孤立ページになる。検索結果に出ても中身が薄いだけなので noindex にする。v1.0.16
 * follow は残すので、ページ内のリンク（他のメンター・CTA）の評価は通る。
 * echo ではなく wp_robots フィルタを使う理由：WordPress本体が出す
 * max-image-preview:large と同じ1つの meta タグにまとめるため。
 */
function preworks_mentor_wp_robots( $robots ) {
    // 詳細ページ：キャッチコピー未入力（＝下層が未入稿）なら noindex。v1.0.16→1.0.17 で voice も対象。
    if ( is_singular( array( 'mentor', 'voice' ) ) ) {
        if ( ! preworks_entry_is_ready( get_queried_object_id() ) ) {
            $robots['noindex'] = true;
            $robots['follow']  = true;
        }
        return $robots;
    }
    // 一覧ページ /voice/：3件そろうまではどこからもリンクしていない孤立ページなので noindex。v1.0.17
    if ( is_post_type_archive( 'voice' ) && ! preworks_voice_is_live() ) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }
    return $robots;
}
add_filter( 'wp_robots', 'preworks_mentor_wp_robots' );

/*
 * 下層ページが未公開のあいだ、詳細ページを一覧ページへ転送する。v1.0.18
 * 301ではなく302。あとでチェックを入れて公開する前提の一時的な非公開なので、
 * 恒久移動としてキャッシュされると公開に切り替えても旧転送が残る。
 * ログイン中の編集権限者は転送しない（坂さんが中身を仕上げ・確認できるように）。
 */
function preworks_detail_guard() {
    if ( is_admin() || is_preview() ) return;
    if ( ! is_singular( array( 'mentor', 'voice' ) ) ) return;
    $id = get_queried_object_id();
    if ( ! $id || preworks_entry_is_ready( $id ) ) return;
    if ( current_user_can( 'edit_posts' ) ) return;
    $url = get_post_type_archive_link( get_post_type( $id ) );
    if ( ! $url ) { $url = home_url( '/' ); }
    wp_safe_redirect( $url, 302 );
    exit;
}
add_action( 'template_redirect', 'preworks_detail_guard', 1 );

// 管理者が未公開の詳細ページを見ているとき、公開済みと勘違いしないよう明示する。v1.0.18
function preworks_detail_guard_banner() {
    if ( ! is_singular( array( 'mentor', 'voice' ) ) ) return;
    $id = get_queried_object_id();
    if ( ! $id || preworks_entry_is_ready( $id ) ) return;
    if ( ! current_user_can( 'edit_posts' ) ) return;
    echo '<div style="position:relative;z-index:9999;background:#7a6a1f;color:#fff;font-size:13.5px;line-height:1.7;padding:10px 16px;text-align:center;">'
       . 'このページは<b>非公開</b>です。ログイン中の管理者にだけ表示されています。'
       . '公開するには編集画面の「<b>下層ページを公開する</b>」にチェックを入れてください。'
       . '</div>' . "\n";
}
add_action( 'wp_body_open', 'preworks_detail_guard_banner' );

// 属性テキスト（例：オンライン秘書歴 3年｜40代｜3児ママ）を既存の見た目に整形
function preworks_mentor_attr_html( $attr ) {
    $out = esc_html( $attr );
    $out = preg_replace( '/(\d+)/', '<b class="mnum">$1</b>', $out );
    $out = str_replace( array( '｜', '|' ), '<span class="mdiv">｜</span>', $out );
    return $out;
}

// 顔写真のURL。アイキャッチ未設定なら、旧URLスラグ(mentor-1〜)からテーマ内の既存写真を使う
// メンターの顔写真は縦長（4:5）。preworks_card（4:3）を使うと
// アイキャッチを設定した時点でWordPress側が4:3に切ってしまい、
// さらに4:5の枠に入るため二段階で切られる。既定を preworks_portrait にする。
function preworks_mentor_photo_url( $post_id = null, $size = 'preworks_portrait' ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $url = get_the_post_thumbnail_url( $post_id, $size );
    if ( $url ) return $url;
    $key = get_post_meta( $post_id, '_pw_oldslug', true );
    if ( ! $key ) $key = get_post_field( 'post_name', $post_id );
    if ( preg_match( '/mentor-([1-9][0-9]?)/', (string) $key, $m ) ) {
        $file = '/mentor-' . $m[1] . '.jpg';
        if ( file_exists( get_template_directory() . '/assets/img' . $file ) ) {
            return PREWORKS_IMG . $file;
        }
    }
    return '';
}

function preworks_mentor_metabox() {
    add_meta_box( 'preworks_mentor_fields', 'メンター紹介（かんたん入力）', 'preworks_mentor_metabox_html', 'mentor', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'preworks_mentor_metabox' );

function preworks_mentor_metabox_html( $post ) {
    wp_nonce_field( 'preworks_mentor_save', 'preworks_mentor_nonce' );
    echo preworks_slug_warning_box( $post->ID );
    $catch   = get_post_meta( $post->ID, '_pw_catch', true );
    $youtube = get_post_meta( $post->ID, '_pw_youtube', true );
    $profile = get_post_meta( $post->ID, '_pw_profile', true );
    $qa      = get_post_meta( $post->ID, '_pw_qa', true );
    $attr    = get_post_meta( $post->ID, '_pw_attr', true );
    $role    = get_post_meta( $post->ID, '_pw_role', true );
    $comment = get_post_meta( $post->ID, '_pw_comment', true );
    $oldslug = get_post_meta( $post->ID, '_pw_oldslug', true );
    $name_l  = get_post_meta( $post->ID, '_pw_name_l', true );
    $name_r  = get_post_meta( $post->ID, '_pw_name_r', true );
    $icon_l  = get_post_meta( $post->ID, '_pw_icon_l', true );
    $icon_r  = get_post_meta( $post->ID, '_pw_icon_r', true );
    $ph = "■メンターになったきっかけは？\n質問者：メンターになったきっかけを教えてください。\n本人：育児と両立できる働き方を探していたときに、URAKATAに出会いました。\n\n自分が助けてもらった経験があるので、今度は誰かの力になりたいと思いました。\n質問者：現場のリアルを知っている方に伴走してもらえるのは**心強い**ですね。";
    $s = 'style="width:100%;max-width:640px"';
    echo '<style>.pwf label{display:block;margin:14px 0 4px;font-weight:600}.pwf .hint{color:#888;font-size:12px;font-weight:400}.pwf hr{margin:20px 0;border:0;border-top:1px solid #eee}</style>';
    echo '<div class="pwf">';
    echo '<p style="background:#fff8ef;border:1px solid #f0e2cf;padding:10px 14px;border-radius:6px;font-size:13px;">下の欄を埋めるだけでページが完成します。<b>本文（下のエディタ）は使いません。</b><br>①タイトル＝メンター名（画面上の「タイトルを追加」）／②アイキャッチ画像＝顔写真（一覧カードと吹き出しのアイコンになります）<br><b>顔写真は縦長で用意してください（推奨 960×1200＝4:5）。</b>一覧・トップのカードは縦長で表示されます。</p>';
    echo preworks_detail_ready_field( $post->ID, 'プロフィールを見る →' );
    echo '<label>キャッチコピー <span class="hint">（詳細ページ上部の大きな見出し）</span></label>';
    echo '<input type="text" name="pw_catch" value="' . esc_attr( $catch ) . '" ' . $s . '>';
    echo '<label>YouTube動画ID <span class="hint">（https://youtu.be/XXXX の XXXX 部分だけ。動画なしなら空欄でOK）</span></label>';
    echo '<input type="text" name="pw_youtube" value="' . esc_attr( $youtube ) . '" ' . $s . '>';
    echo '<label>プロフィール <span class="hint">（Profile枠に出る紹介文）</span></label>';
    echo '<textarea name="pw_profile" rows="3" ' . $s . '>' . esc_textarea( $profile ) . '</textarea>';
    echo '<hr><p style="font-weight:700;margin:0 0 4px;">▼ 吹き出しの名前とアイコン</p>';
    echo '<p style="font-size:12px;color:#666;margin:0 0 6px;">記事ごとに変更できます。空欄のままなら（　）内が使われます。</p>';
    echo '<label>左（本人）の名前 <span class="hint">（空欄＝タイトルの文字。タイトルと違う表示名にしたいときに入力）</span></label>';
    echo '<input type="text" name="pw_name_l" value="' . esc_attr( $name_l ) . '" ' . $s . ' placeholder="' . esc_attr( get_the_title( $post ) ) . '">';
    echo '<label>左（本人）のアイコン <span class="hint">（空欄＝アイキャッチ画像）</span></label>';
    preworks_media_field( 'pw_icon_l', $icon_l );
    echo '<label>右（聞き手）の名前 <span class="hint">（空欄＝質問者）</span></label>';
    echo '<input type="text" name="pw_name_r" value="' . esc_attr( $name_r ) . '" ' . $s . ' placeholder="質問者">';
    echo '<label>右（聞き手）のアイコン <span class="hint">（空欄＝名前の頭文字の丸バッジ）</span></label>';
    preworks_media_field( 'pw_icon_r', $icon_r );
    echo '<hr><label>インタビュー（Q&amp;A・会話）</label>';
    echo '<div style="background:#fff8ef;border:1px solid #f0e2cf;padding:10px 14px;border-radius:6px;font-size:12.5px;line-height:1.9;margin:0 0 6px;max-width:640px;">'
       . '<b>書き方</b><br>'
       . '・<code>■</code> で始まる行 ＝ 質問見出し<br>'
       . '・<code>質問者：</code> で始まる行 ＝ <b>右</b>の吹き出しが始まる<br>'
       . '・<code>本人：</code> で始まる行 ＝ <b>左</b>の吹き出しが始まる<br>'
       . '・記号を付けない行 ＝ <b>直前の吹き出しの続き（改行）</b><br>'
       . '・空行 ＝ 吹き出しの中の<b>一行スペース</b>（同じ人の発言が続くかぎり1つの吹き出しにまとまります）<br>'
       . '・<code>---</code> だけの行 ＝ 同じ人でも<b>そこで吹き出しを分ける</b><br>'
       . '・装飾： <code>**太字**</code> ／ <code>__下線__</code> ／ <code>==マーカー==</code> ／ <code>[表示文字](URL)</code>'
       . '</div>';
    echo '<textarea name="pw_qa" rows="12" ' . $s . ' placeholder="' . esc_attr( $ph ) . '">' . esc_textarea( $qa ) . '</textarea>';
    echo '<hr><p style="font-weight:700;margin:0 0 4px;">▼ 一覧カードに出る内容</p>';
    echo '<label>属性 <span class="hint">（例：オンライン秘書歴 3年｜40代｜3児ママ　※数字は自動で強調されます）</span></label>';
    echo '<input type="text" name="pw_attr" value="' . esc_attr( $attr ) . '" ' . $s . '>';
    echo '<label>肩書き・担当 <span class="hint">（例：URAKATA オペレーションマネージャー）</span></label>';
    echo '<input type="text" name="pw_role" value="' . esc_attr( $role ) . '" ' . $s . '>';
    echo '<label>紹介文 <span class="hint">（一覧カードに出る短い文章）</span></label>';
    echo '<textarea name="pw_comment" rows="3" ' . $s . '>' . esc_textarea( $comment ) . '</textarea>';
    echo '<hr><label>旧URLスラグ <span class="hint">（任意。以前の固定ページが /mentor-1/ だった方は「mentor-1」と入力。旧URLからの自動転送に使われ、アイキャッチ未設定のときはテーマ内の既存写真 mentor-1.jpg も自動で使われます）</span></label>';
    echo '<input type="text" name="pw_oldslug" value="' . esc_attr( $oldslug ) . '" ' . $s . '>';
    echo '</div>';
}

function preworks_mentor_save( $post_id ) {
    if ( ! isset( $_POST['preworks_mentor_nonce'] ) || ! wp_verify_nonce( $_POST['preworks_mentor_nonce'], 'preworks_mentor_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $text = array( 'pw_catch' => '_pw_catch', 'pw_youtube' => '_pw_youtube', 'pw_attr' => '_pw_attr', 'pw_role' => '_pw_role', 'pw_oldslug' => '_pw_oldslug', 'pw_name_l' => '_pw_name_l', 'pw_name_r' => '_pw_name_r' );
    foreach ( $text as $k => $m ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $m, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
    }
    $area = array( 'pw_profile' => '_pw_profile', 'pw_comment' => '_pw_comment', 'pw_qa' => '_pw_qa' );
    foreach ( $area as $k => $m ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $m, sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) );
    }
    $ids = array( 'pw_icon_l' => '_pw_icon_l', 'pw_icon_r' => '_pw_icon_r' );
    foreach ( $ids as $k => $m ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $m, absint( $_POST[ $k ] ) );
    }
    // 下層ページの公開チェック。v1.0.18
    // チェックを外すと $_POST にキーが来ないため、hidden の目印で「この画面から
    // 保存された」ことを判定してから 0 を書き込む（他の保存処理で消えないように）。
    if ( isset( $_POST['pw_detail_ready_present'] ) ) {
        update_post_meta( $post_id, '_pw_detail_ready', empty( $_POST['pw_detail_ready'] ) ? '' : '1' );
    }
}
add_action( 'save_post_mentor', 'preworks_mentor_save' );

// 旧URL（/mentor-1/ 〜）から新しい記事URLへ301転送。該当が無ければ一覧へ
function preworks_mentor_legacy_redirect() {
    if ( ! is_404() ) return;
    $path = isset( $_SERVER['REQUEST_URI'] ) ? parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
    $seg  = basename( trim( (string) $path, '/' ) );
    if ( ! preg_match( '/^mentor-[1-9][0-9]?$/', $seg ) ) return;
    $found = get_posts( array(
        'post_type'      => 'mentor',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_pw_oldslug',
        'meta_value'     => $seg,
    ) );
    $url = $found ? get_permalink( $found[0] ) : get_post_type_archive_link( 'mentor' );
    if ( $url ) {
        wp_safe_redirect( $url, 301 );
        exit;
    }
}
add_action( 'template_redirect', 'preworks_mentor_legacy_redirect' );



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
        $parts['title'] = 'オンライン秘書講座 preworks｜未経験から最短2ヵ月でオンライン秘書デビュー';
        unset( $parts['tagline'], $parts['site'] );
    }
    return $parts;
} );

// トップページの meta description（本番はSEOプラグイン未導入のためテーマ側で出力）
function preworks_front_meta_description() {
    if ( is_front_page() ) {
        echo '<meta name="description" content="オンライン秘書講座「preworks」は、未経験から最短2ヵ月で在宅ワークデビューを目指せる実践型講座です。現役オンライン秘書の1on1サポートや実践課題を通して、仕事に必要なスキルを習得。受講中から実務経験を積める機会も用意しています。">' . "\n";
    }
}
add_action( 'wp_head', 'preworks_front_meta_description', 1 );

// ===== 広告用LP（lp001=Google検索広告 / lp002=Google P-MAX広告） =====
// LP判定
function preworks_is_lp() {
    return is_page( array( 'lp001', 'lp002' ) );
}

// LINEボタンの遷移先URL。広告用LPでは計測用LIFF URLを、それ以外は従来URLを返す
function preworks_line_url() {
    $map = array(
        'lp001' => 'https://liff.line.me/2008701289-ICL3J7sI/track?trackingId=7f9a24e4-c8b4-4c2a-a979-4a20a0742fef&line-ad-track=true&liffId=2008701289-ICL3J7sI&follow=%40034mfpdk',
        'lp002' => 'https://liff.line.me/2008701289-ICL3J7sI/track?trackingId=146fdd6d-acd4-401e-82d9-309ea95db53b&line-ad-track=true&liffId=2008701289-ICL3J7sI&follow=%40034mfpdk',
    );
    if ( preworks_is_lp() ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        if ( isset( $map[ $slug ] ) ) {
            return $map[ $slug ];
        }
    }
    return 'https://sub.preworks.jp/line/open/bQAtEmMQRwat?mtid=taFGWWNm6MRj';
}

// 広告計測スクリプト（LP限定・body開始直後。header.php の wp_body_open() から出力）
function preworks_lp_tracking_script() {
    if ( preworks_is_lp() ) {
        echo '<script async src="https://l-cv-tracking-client.vercel.app/line-ad/v1/append-ad-params.js"></script>' . "\n";
    }
}
add_action( 'wp_body_open', 'preworks_lp_tracking_script' );

// 広告用LPはTOPの複製（重複コンテンツ）のためインデックスさせない
function preworks_lp_noindex() {
    if ( preworks_is_lp() ) {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
}
add_action( 'wp_head', 'preworks_lp_noindex', 0 );

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

/* =========================================================================
 * No.8 お問い合わせフォーム（自前実装 / v1.3.21）
 * ------------------------------------------------------------------------
 * 送付先メール / Chatworkルーム / APIトークンは
 *   管理画面「設定 → preworks 通知設定」から入力する（v1.3.28以降）
 *
 * wp-config.php に定数を書いた場合は、そちらが設定画面より優先される。
 *   define( 'PREWORKS_CONTACT_TO', 'school@lishinc.com' );
 *   define( 'PREWORKS_CHATWORK_ROOM', '447097049' );
 *   define( 'PREWORKS_CHATWORK_TOKEN', 'ここにAPIトークン' );
 *
 * ※ここで define() してしまうと設定画面の入力が無効になるため、
 *   テーマ側では定数を定義しない。既定値は各リゾルバ関数が持つ。
 * ========================================================================= */
if ( ! defined( 'PREWORKS_CONTACT_TO_DEFAULT' ) )    define( 'PREWORKS_CONTACT_TO_DEFAULT', 'school@lishinc.com' );
if ( ! defined( 'PREWORKS_CHATWORK_ROOM_DEFAULT' ) ) define( 'PREWORKS_CHATWORK_ROOM_DEFAULT', '447097049' );

function preworks_contact_fields() {
    return array(
        'company' => array( 'label' => '会社・団体名',     'ph' => '例）○○株式会社',      'type' => 'text' ),
        'name'    => array( 'label' => '氏名',             'ph' => '例）山田　花子',        'type' => 'text' ),
        'kana'    => array( 'label' => 'ふりがな',         'ph' => '例）やまだ　はなこ',    'type' => 'text' ),
        'tel'     => array( 'label' => '電話番号',         'ph' => '例）012-1234-5678',    'type' => 'tel' ),
        'email'   => array( 'label' => 'メールアドレス',   'ph' => '例）testmail@test.jp', 'type' => 'email' ),
        'body'    => array( 'label' => 'お問い合わせ内容', 'ph' => 'お問い合わせ内容を入力してください', 'type' => 'textarea' ),
    );
}

// 入力エラー時に値とエラーを描画側へ引き渡すための保管場所
function &preworks_contact_state() {
    static $state = array( 'errors' => array(), 'old' => array() );
    return $state;
}

function preworks_contact_handle() {
    if ( ! is_page( 'contact' ) ) return;
    if ( empty( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) return;
    if ( empty( $_POST['pw_contact_nonce'] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_contact_nonce'] ) ), 'pw_contact' ) ) return;
    // ハニーポット：人間には見えない欄。埋まっていればbotとして静かに破棄
    if ( ! empty( $_POST['pw_hp'] ) ) return;

    $fields = preworks_contact_fields();
    $data   = array();
    $errors = array();

    foreach ( $fields as $key => $f ) {
        $raw = isset( $_POST[ 'pw_' . $key ] ) ? wp_unslash( $_POST[ 'pw_' . $key ] ) : '';
        $val = ( 'textarea' === $f['type'] ) ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
        $data[ $key ] = $val;
        if ( '' === trim( $val ) ) {
            $errors[ $key ] = '入力してください。';
            continue;
        }
        if ( 'email' === $f['type'] && ! is_email( $val ) ) {
            $errors[ $key ] = 'メールアドレスの形式をご確認ください。';
        }
    }
    if ( empty( $_POST['pw_agree'] ) ) {
        $errors['agree'] = '個人情報保護方針への同意が必要です。';
    }

    if ( $errors ) {
        $state = &preworks_contact_state();
        $state = array( 'errors' => $errors, 'old' => $data );
        return;
    }

    preworks_contact_send_mail( $data );
    preworks_contact_send_autoreply( $data );
    preworks_chatwork_notify( $data );
    wp_safe_redirect( add_query_arg( 'sent', '1', get_permalink() ) );
    exit;
}
add_action( 'template_redirect', 'preworks_contact_handle' );

function preworks_contact_send_mail( $d ) {
    $lines = array(
        '公式サイトのお問い合わせフォームから送信がありました。',
        '',
        '会社・団体名：' . $d['company'],
        '氏名：' . $d['name'],
        'ふりがな：' . $d['kana'],
        '電話番号：' . $d['tel'],
        'メールアドレス：' . $d['email'],
        '',
        'お問い合わせ内容：',
        $d['body'],
        '',
        '--------------------------------',
        '送信日時：' . wp_date( 'Y-m-d H:i' ),
        '送信ページ：' . home_url( '/contact/' ),
    );
    // From を明示しないと WordPress 既定の「WordPress <wordpress@サイトドメイン>」になる。
    // 実在しないアドレス＋汎用的な差出人名になり、受信側で迷惑メール判定されやすい。
    // 控えメールと同じ no-reply@（実在・DKIM署名・SPF許可済み）を差出人にし、
    // エンベロープ（Return-Path）も揃えて DMARC のアラインメントを成立させる。
    $site  = 'オンライン秘書講座 preworks';
    $from  = preworks_opt_autoreply_from();
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site . ' <' . $from . '>',
        'Reply-To: ' . $d['email'],
    );
    add_action( 'phpmailer_init', 'preworks_autoreply_envelope' );
    $ok = wp_mail( preworks_opt_contact_to(), '【preworks】お問い合わせがありました', implode( "\n", $lines ), $headers );
    remove_action( 'phpmailer_init', 'preworks_autoreply_envelope' );
    if ( ! $ok ) {
        error_log( '[preworks] お問い合わせメールの送信に失敗しました（宛先: ' . preworks_opt_contact_to() . '）' );
    }
    return $ok;
}

/* ===== 問い合わせ控えメール（送信者本人への自動返信）v1.3.35 ===== */
function preworks_contact_send_autoreply( $d ) {
    if ( ! preworks_opt_autoreply_enabled() ) return false;
    if ( empty( $d['email'] ) || ! is_email( $d['email'] ) ) return false;

    $site  = 'オンライン秘書講座 preworks';
    $from  = preworks_opt_autoreply_from();
    $reply = preworks_opt_contact_to();

    $lines = array(
        $d['name'] . ' 様',
        '',
        'このたびは ' . $site . ' へお問い合わせいただき、ありがとうございます。',
        '以下の内容で受け付けいたしました。',
        '内容を確認のうえ、担当者より折り返しご連絡いたします。',
        '',
        '────────────────────────────',
        '■ 会社・団体名',
        $d['company'],
        '',
        '■ 氏名',
        $d['name'] . '（' . $d['kana'] . '）',
        '',
        '■ 電話番号',
        $d['tel'],
        '',
        '■ メールアドレス',
        $d['email'],
        '',
        '■ お問い合わせ内容',
        $d['body'],
        '────────────────────────────',
        '',
        '※本メールは自動送信です。',
        '　ご返信いただいた場合は、お問い合わせ窓口に届きます。',
        '',
        '────────────────────────────',
        $site,
        '運営：LISH株式会社',
        home_url( '/' ),
        'お問い合わせ：' . $reply,
        '────────────────────────────',
    );

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site . ' <' . $from . '>',
        'Reply-To: ' . $reply,
    );

    // エンベロープ送信者(Return-Path)をFromと同じドメインに揃える。
    // 既定では共有サーバー名(例 lishinc@sv14978.xserver.jp)になり、SPFがFromドメインで
    // 照合されず迷惑メール判定されやすいため。控えメールの送信時だけ適用する。
    add_action( 'phpmailer_init', 'preworks_autoreply_envelope' );
    $ok = wp_mail( $d['email'], '【preworks】お問い合わせありがとうございます', implode( "\n", $lines ), $headers );
    remove_action( 'phpmailer_init', 'preworks_autoreply_envelope' );

    if ( ! $ok ) {
        error_log( '[preworks] 控えメールの送信に失敗しました（宛先: ' . $d['email'] . ' / 送信元: ' . $from . '）' );
    }
    return $ok;
}

function preworks_autoreply_envelope( $phpmailer ) {
    $from = preworks_opt_autoreply_from();
    if ( $from && is_email( $from ) ) {
        $phpmailer->Sender = $from;
    }
}

function preworks_chatwork_notify( $d ) {
    $token = preworks_opt_chatwork_token();
    $room  = preworks_opt_chatwork_room();
    if ( ! $token || ! $room ) {
        return false; // トークン/ルーム未設定：通知はスキップしてメール送信のみ
    }
    $body  = "[info][title]preworks お問い合わせ[/title]";
    $body .= "会社・団体名：" . $d['company'] . "\n";
    $body .= "氏名：" . $d['name'] . "（" . $d['kana'] . "）\n";
    $body .= "電話番号：" . $d['tel'] . "\n";
    $body .= "メール：" . $d['email'] . "\n\n";
    $body .= $d['body'] . "\n\n";
    $body .= "受信日時：" . wp_date( 'Y-m-d H:i' );
    $body .= "[/info]";

    $res = wp_remote_post(
        'https://api.chatwork.com/v2/rooms/' . rawurlencode( $room ) . '/messages',
        array(
            'timeout' => 15,
            'headers' => array( 'X-ChatWorkToken' => $token ),
            'body'    => array( 'body' => $body, 'self_unread' => '1' ),
        )
    );
    $code = wp_remote_retrieve_response_code( $res );
    if ( is_wp_error( $res ) || 200 !== (int) $code ) {
        error_log( '[preworks] Chatwork通知に失敗: ' . ( is_wp_error( $res ) ? $res->get_error_message() : $code . ' ' . wp_remote_retrieve_body( $res ) ) );
        return false;
    }
    return true;
}

function preworks_contact_form() {
    $state  = preworks_contact_state();
    $errors = $state['errors'];
    $old    = $state['old'];
    $sent   = isset( $_GET['sent'] ) && '1' === $_GET['sent'];

    ob_start();
    if ( $sent ) : ?>
      <div class="cform-done reveal">
        <p class="cd-t">お問い合わせありがとうございます。</p>
        <p>内容を確認のうえ、担当者より折り返しご連絡いたします。</p>
        <?php if ( preworks_opt_autoreply_enabled() ) : ?>
        <p class="cd-sub">ご入力いただいたメールアドレスに、お問い合わせ内容の控えをお送りしました。<br>届いていない場合は、迷惑メールフォルダもご確認ください。</p>
        <?php endif; ?>
      </div>
    <?php else : ?>
      <?php if ( $errors ) : ?>
        <p class="cform-alert">入力内容にご確認いただきたい箇所があります。</p>
      <?php endif; ?>
      <form class="cform reveal" method="post" action="<?php echo esc_url( get_permalink() ); ?>" novalidate>
        <?php wp_nonce_field( 'pw_contact', 'pw_contact_nonce' ); ?>
        <div class="pw-hp" aria-hidden="true"><label>この欄は入力しないでください<input type="text" name="pw_hp" tabindex="-1" autocomplete="off"></label></div>
        <?php foreach ( preworks_contact_fields() as $key => $f ) :
            $val = isset( $old[ $key ] ) ? $old[ $key ] : '';
            $err = isset( $errors[ $key ] ) ? $errors[ $key ] : '';
            $id  = 'pw_' . $key; ?>
          <div class="row<?php echo $err ? ' has-err' : ''; ?>">
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $f['label'] ); ?><span class="req">必須</span></label>
            <?php if ( 'textarea' === $f['type'] ) : ?>
              <textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" placeholder="<?php echo esc_attr( $f['ph'] ); ?>"><?php echo esc_textarea( $val ); ?></textarea>
            <?php else : ?>
              <input type="<?php echo esc_attr( $f['type'] ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $val ); ?>" placeholder="<?php echo esc_attr( $f['ph'] ); ?>">
            <?php endif; ?>
            <?php if ( $err ) : ?><p class="err"><?php echo esc_html( $err ); ?></p><?php endif; ?>
          </div>
        <?php endforeach; ?>
        <div class="row consent<?php echo isset( $errors['agree'] ) ? ' has-err' : ''; ?>">
          <input type="checkbox" id="pw_agree" name="pw_agree" value="1"<?php echo ! empty( $_POST['pw_agree'] ) ? ' checked' : ''; ?>>
          <label for="pw_agree"><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>" target="_blank" rel="noopener">個人情報保護方針</a>の内容に同意する</label>
          <?php if ( isset( $errors['agree'] ) ) : ?><p class="err"><?php echo esc_html( $errors['agree'] ); ?></p><?php endif; ?>
        </div>
        <div class="submit"><button type="submit">送信する</button></div>
      </form>
    <?php endif;
    return ob_get_clean();
}

/* =========================================================================
 * preworks 通知設定（管理画面）v1.3.28
 *  設定 → 「preworks 通知設定」で、お問い合わせフォームの
 *  メール送付先 / Chatwork APIトークン / 通知先ルームID を入力できる。
 *
 *  値の優先順位：
 *    1. wp-config.php の定数（PREWORKS_CONTACT_TO 等）※定義があれば最優先
 *    2. この設定画面で保存した値（DBのオプション）
 *    3. コード内の既定値
 *
 *  APIトークンは画面に再表示されない（保存済みかどうかだけ表示）。
 * ========================================================================= */

const PREWORKS_OPT_TOKEN  = 'preworks_chatwork_token';
const PREWORKS_OPT_ROOM   = 'preworks_chatwork_room';
const PREWORKS_OPT_MAIL   = 'preworks_contact_to';
const PREWORKS_OPT_ARFROM = 'preworks_autoreply_from';
const PREWORKS_OPT_ARON   = 'preworks_autoreply_enabled';

// 控えメールの送信元アドレス。既定は no-reply@（サイトのドメイン）
// 送信サーバと同じドメインにしないと、なりすまし判定で迷惑メール扱いされやすい
function preworks_opt_autoreply_from() {
    if ( defined( 'PREWORKS_AUTOREPLY_FROM' ) && PREWORKS_AUTOREPLY_FROM ) return PREWORKS_AUTOREPLY_FROM;
    $v = trim( (string) get_option( PREWORKS_OPT_ARFROM, '' ) );
    if ( $v !== '' ) return $v;
    $host = preg_replace( '/^www\./', '', (string) parse_url( home_url( '/' ), PHP_URL_HOST ) );
    return 'no-reply@' . $host;
}
function preworks_opt_autoreply_enabled() {
    if ( defined( 'PREWORKS_AUTOREPLY' ) ) return (bool) PREWORKS_AUTOREPLY;
    return '1' === (string) get_option( PREWORKS_OPT_ARON, '1' );
}

function preworks_opt_contact_to() {
    if ( defined( 'PREWORKS_CONTACT_TO' ) && PREWORKS_CONTACT_TO ) return PREWORKS_CONTACT_TO;
    $v = trim( (string) get_option( PREWORKS_OPT_MAIL, '' ) );
    return $v !== '' ? $v : PREWORKS_CONTACT_TO_DEFAULT;
}
function preworks_opt_chatwork_room() {
    if ( defined( 'PREWORKS_CHATWORK_ROOM' ) && PREWORKS_CHATWORK_ROOM ) return (string) PREWORKS_CHATWORK_ROOM;
    $v = trim( (string) get_option( PREWORKS_OPT_ROOM, '' ) );
    return $v !== '' ? $v : PREWORKS_CHATWORK_ROOM_DEFAULT;
}
function preworks_opt_chatwork_token() {
    if ( defined( 'PREWORKS_CHATWORK_TOKEN' ) && PREWORKS_CHATWORK_TOKEN ) return (string) PREWORKS_CHATWORK_TOKEN;
    return trim( (string) get_option( PREWORKS_OPT_TOKEN, '' ) );
}

// Chatworkへ任意の本文を送る（テスト送信と本番通知で共用）
function preworks_chatwork_post( $text ) {
    $token = preworks_opt_chatwork_token();
    $room  = preworks_opt_chatwork_room();
    if ( ! $token ) return new WP_Error( 'no_token', 'APIトークンが未設定です。' );
    if ( ! $room )  return new WP_Error( 'no_room', '通知先ルームIDが未設定です。' );
    $res = wp_remote_post(
        'https://api.chatwork.com/v2/rooms/' . rawurlencode( $room ) . '/messages',
        array(
            'timeout' => 15,
            'headers' => array( 'X-ChatWorkToken' => $token ),
            'body'    => array( 'body' => $text, 'self_unread' => '1' ),
        )
    );
    if ( is_wp_error( $res ) ) return $res;
    $code = (int) wp_remote_retrieve_response_code( $res );
    if ( 200 !== $code ) {
        $msg = wp_remote_retrieve_body( $res );
        $hint = '';
        if ( 401 === $code ) $hint = 'APIトークンが誤っている可能性があります。';
        if ( 403 === $code ) $hint = 'このルームに参加していない、または権限がない可能性があります。';
        if ( 404 === $code ) $hint = 'ルームIDが存在しない可能性があります。';
        return new WP_Error( 'http_' . $code, 'Chatwork APIがエラーを返しました（HTTP ' . $code . '）。' . $hint . ' 応答：' . wp_strip_all_tags( (string) $msg ) );
    }
    return true;
}

function preworks_notify_settings_menu() {
    add_options_page( 'preworks 通知設定', 'preworks 通知設定', 'manage_options', 'preworks-notify', 'preworks_notify_settings_page' );
}
add_action( 'admin_menu', 'preworks_notify_settings_menu' );

function preworks_notify_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $notices = array();

    // ---- 保存 ----
    $submitted = isset( $_POST['preworks_notify_save'] ) || isset( $_POST['preworks_notify_testcw'] ) || isset( $_POST['preworks_notify_testmail'] );
    if ( $submitted && check_admin_referer( 'preworks_notify_save' ) ) {
        $mail = isset( $_POST['pw_mail'] ) ? sanitize_email( wp_unslash( $_POST['pw_mail'] ) ) : '';
        $room = isset( $_POST['pw_room'] ) ? preg_replace( '/[^0-9]/', '', wp_unslash( $_POST['pw_room'] ) ) : '';
        update_option( PREWORKS_OPT_MAIL, $mail );
        update_option( PREWORKS_OPT_ROOM, $room );
        $arfrom = isset( $_POST['pw_arfrom'] ) ? sanitize_email( wp_unslash( $_POST['pw_arfrom'] ) ) : '';
        update_option( PREWORKS_OPT_ARFROM, $arfrom );
        update_option( PREWORKS_OPT_ARON, empty( $_POST['pw_aron'] ) ? '0' : '1' );

        if ( ! empty( $_POST['pw_token_clear'] ) ) {
            delete_option( PREWORKS_OPT_TOKEN );
            $notices[] = array( 'ok', 'APIトークンを削除しました。' );
        } else {
            $tok = isset( $_POST['pw_token'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['pw_token'] ) ) ) : '';
            if ( $tok !== '' ) {
                update_option( PREWORKS_OPT_TOKEN, $tok );
                $notices[] = array( 'ok', 'APIトークンを保存しました。' );
            }
        }
        if ( isset( $_POST['preworks_notify_save'] ) ) $notices[] = array( 'ok', '設定を保存しました。' );
    }

    // ---- Chatworkテスト送信 ----
    if ( isset( $_POST['preworks_notify_testcw'] ) && check_admin_referer( 'preworks_notify_save' ) ) {
        $msg  = "[info][title]preworks 通知テスト[/title]";
        $msg .= "これは preworks 公式サイトからの接続テストです。\n";
        $msg .= "この文字が見えていれば、お問い合わせフォームの通知は正常に動作します。\n\n";
        $msg .= "送信元：" . home_url( '/' ) . "\n";
        $msg .= "送信日時：" . wp_date( 'Y-m-d H:i' );
        $msg .= "[/info]";
        $r = preworks_chatwork_post( $msg );
        if ( is_wp_error( $r ) ) {
            $notices[] = array( 'err', 'Chatworkテスト送信に失敗しました。' . $r->get_error_message() );
        } else {
            $notices[] = array( 'ok', 'Chatworkへテスト送信しました。ルームID ' . esc_html( preworks_opt_chatwork_room() ) . ' を確認してください。' );
        }
    }

    // ---- メールテスト送信 ----
    if ( isset( $_POST['preworks_notify_testmail'] ) && check_admin_referer( 'preworks_notify_save' ) ) {
        $to = preworks_opt_contact_to();
        $ok = wp_mail(
            $to,
            '【preworks】通知テストメール',
            "これは preworks 公式サイトからの接続テストです。\nこのメールが届いていれば、お問い合わせフォームのメール通知は正常に動作します。\n\n送信元：" . home_url( '/' ) . "\n送信日時：" . wp_date( 'Y-m-d H:i' ),
            array( 'Content-Type: text/plain; charset=UTF-8' )
        );
        $notices[] = $ok
            ? array( 'ok', 'テストメールを ' . esc_html( $to ) . ' へ送信しました。受信を確認してください。' )
            : array( 'err', 'テストメールの送信に失敗しました。サーバーのメール設定を確認してください。' );
    }

    $has_token   = preworks_opt_chatwork_token() !== '';
    $c_mail      = defined( 'PREWORKS_CONTACT_TO' ) && PREWORKS_CONTACT_TO;
    $c_room      = defined( 'PREWORKS_CHATWORK_ROOM' ) && PREWORKS_CHATWORK_ROOM;
    $c_token     = defined( 'PREWORKS_CHATWORK_TOKEN' ) && PREWORKS_CHATWORK_TOKEN;
    $lock        = ' <span style="color:#b32d2e;font-size:12px;">※wp-config.php の設定が優先されているため、この欄は使われません</span>';
    ?>
    <div class="wrap">
      <h1>preworks 通知設定</h1>
      <p>お問い合わせフォーム（<?php echo esc_html( home_url( '/contact/' ) ); ?>）の通知先を設定します。</p>
      <?php foreach ( $notices as $n ) : ?>
        <div class="notice notice-<?php echo 'ok' === $n[0] ? 'success' : 'error'; ?>"><p><?php echo wp_kses_post( $n[1] ); ?></p></div>
      <?php endforeach; ?>

      <form method="post">
        <?php wp_nonce_field( 'preworks_notify_save' ); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="pw_mail">メール送付先</label></th>
            <td>
              <input name="pw_mail" id="pw_mail" type="email" class="regular-text" value="<?php echo esc_attr( get_option( PREWORKS_OPT_MAIL, '' ) ); ?>" placeholder="school@lishinc.com">
              <?php if ( $c_mail ) echo $lock; ?>
              <p class="description">現在の送付先：<code><?php echo esc_html( preworks_opt_contact_to() ); ?></code><br>空欄の場合は <code><?php echo esc_html( PREWORKS_CONTACT_TO_DEFAULT ); ?></code> が使われます。</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="pw_room">Chatwork 通知先ルームID</label></th>
            <td>
              <input name="pw_room" id="pw_room" type="text" class="regular-text" value="<?php echo esc_attr( get_option( PREWORKS_OPT_ROOM, '' ) ); ?>" placeholder="447097049">
              <?php if ( $c_room ) echo $lock; ?>
              <p class="description">
                現在のルームID：<code><?php echo esc_html( preworks_opt_chatwork_room() ?: '（未設定）' ); ?></code><br>
                Chatworkで対象のチャットを開き、URL末尾の <code>#!rid</code> に続く数字を入力してください。<br>
                例：<code>https://www.chatwork.com/#!rid447097049</code> → <code>447097049</code><br>
                空欄の場合は <code><?php echo esc_html( PREWORKS_CHATWORK_ROOM_DEFAULT ); ?></code> が使われます。
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="pw_token">Chatwork APIトークン</label></th>
            <td>
              <input name="pw_token" id="pw_token" type="password" class="regular-text" value="" autocomplete="new-password"
                     placeholder="<?php echo $has_token ? '保存済み（変更する場合のみ入力）' : '40桁程度の英数字'; ?>">
              <?php if ( $c_token ) echo $lock; ?>
              <p class="description">
                状態：<strong><?php echo $has_token ? '保存済み' : '未設定'; ?></strong><br>
                取得方法：Chatwork画面右上の自分のアイコン →「サービス連携」→「API Token」<br>
                セキュリティのため、保存したトークンは画面に再表示されません。変更する場合だけ入力してください。<br>
                <label><input type="checkbox" name="pw_token_clear" value="1"> 保存済みのトークンを削除する</label>
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row">問い合わせ控えメール</th>
            <td>
              <label><input type="checkbox" name="pw_aron" value="1"<?php echo preworks_opt_autoreply_enabled() ? ' checked' : ''; ?>> お問い合わせされた方に、入力内容の控えを自動返信する</label>
              <?php if ( defined( 'PREWORKS_AUTOREPLY' ) ) echo $lock; ?>
              <p class="description">フォーム送信時に、入力されたメールアドレスへ受付内容の控えを送ります。</p>
              <p style="margin:14px 0 4px;"><label for="pw_arfrom"><strong>控えメールの送信元アドレス</strong></label></p>
              <input name="pw_arfrom" id="pw_arfrom" type="email" class="regular-text" value="<?php echo esc_attr( get_option( PREWORKS_OPT_ARFROM, '' ) ); ?>" placeholder="<?php echo esc_attr( preworks_opt_autoreply_from() ); ?>">
              <?php if ( defined( 'PREWORKS_AUTOREPLY_FROM' ) && PREWORKS_AUTOREPLY_FROM ) echo $lock; ?>
              <p class="description">
                現在の送信元：<code><?php echo esc_html( preworks_opt_autoreply_from() ); ?></code><br>
                返信先（Reply-To）には <code><?php echo esc_html( preworks_opt_contact_to() ); ?></code> が入ります。<br>
                <strong style="color:#b32d2e;">このサイトと同じドメインのアドレスを指定してください。</strong>
                別ドメイン（例：lishinc.com）を指定すると、送信サーバーとドメインが一致せず迷惑メール扱いされやすくなります。<br>
                空欄の場合は <code>no-reply@<?php echo esc_html( preg_replace( '/^www\./', '', (string) parse_url( home_url( '/' ), PHP_URL_HOST ) ) ); ?></code> が使われます。
              </p>
            </td>
          </tr>
        </table>
        <p class="submit">
          <button type="submit" name="preworks_notify_save" value="1" class="button button-primary">設定を保存</button>
        </p>
        <hr>
        <h2>接続テスト</h2>
        <p>保存した設定で実際に1通送ってみます。フォームから送信しなくても確認できます。</p>
        <p>
          <button type="submit" name="preworks_notify_testcw" value="1" class="button">Chatworkへテスト送信</button>
          <button type="submit" name="preworks_notify_testmail" value="1" class="button">テストメールを送信</button>
        </p>
        <p class="description">※テスト送信のボタンを押すと、入力中の内容も同時に保存されます。</p>
      </form>
    </div>
    <?php
}
