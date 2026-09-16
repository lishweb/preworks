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
    wp_enqueue_style( 'preworks-main', get_template_directory_uri() . '/assets/css/_shared.css', array(), '1.0.11' );
    // GSAP（任意・トップのアニメ用）
    wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );
    wp_enqueue_script( 'gsap-st', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array( 'gsap' ), '3.12.5', true );
    // メインJS
    wp_enqueue_script( 'preworks-main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.11', true );
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
