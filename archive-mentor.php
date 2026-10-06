<?php
/**
 * S12 メンター紹介（一覧）  /mentor/
 * 各固定ページと将来の記事の「監修者ページ」を兼ねる（E-E-A-T の土台）。
 * H1・パンくず・title・description・BreadcrumbList は登録表（inc/pw-pages.php の 'mentor'）から自動。
 */
get_header();
?>
<main class="pw-main">
  <?php pw_page_hero( array(
      'lead' => 'preworksのメンターは全員、オンライン秘書サービス「URAKATA」で今も働いている現役のオンライン秘書です。その多くが、専業主婦や長いブランク、未経験から在宅ワークを始めた子育て中のママ。「自分も同じところで悩んだ」経験をもとに、月2回の面談とチャットで伴走します。',
  ) ); ?>

  <section class="mentors sec-pad" id="mentor">
    <span class="orb m soft alt" style="top:40px;right:-100px;"></span>
    <div class="wrap">
      <?php /* 下層ではヒーローのH1と重複するため eyebrow・h2 は出さない（TOPの front-page.php はそのまま） */ ?>
      <div class="headblock"><p class="subhead">オンライン秘書サービス「URAKATA」の実務メンバーが、1on1でサポートします。</p></div>
      <div class="mgrid">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
          $attr = get_post_meta( get_the_ID(), '_pw_attr', true );
          $cm   = get_post_meta( get_the_ID(), '_pw_comment', true );
          /* 下層（キャッチコピー）が未入稿ならカードをリンクにしない。v1.0.16
             .mcard のCSSは a/div どちらにも当たる（a.mcard 限定の指定は無い）ので
             見た目は変わらない。 */
          $rdy  = preworks_mentor_is_ready();
          $tag  = $rdy ? 'a' : 'div';
          $atts = $rdy ? ' href="' . esc_url( get_permalink() ) . '" style="text-decoration:none;"' : ''; ?>
          <<?php echo $tag; ?> class="mcard reveal"<?php echo $atts; ?>>
            <?php $ph = preworks_mentor_photo_url(); if ( $ph ) : ?><img src="<?php echo esc_url( $ph ); ?>" alt="<?php the_title_attribute(); ?>"><?php endif; ?>
            <div class="mbody">
              <h3><?php the_title(); ?></h3>
              <?php if ( $attr ) : ?><p class="m-attr"><?php echo preworks_mentor_attr_html( $attr ); ?></p><?php endif; ?>
              <?php if ( $cm ) : ?><p><?php echo esc_html( $cm ); ?></p><?php endif; ?>
              <?php if ( $rdy ) : ?><p class="mprof-link">プロフィールを見る →</p><?php endif; ?>
            </div>
          </<?php echo $tag; ?>>
        <?php endwhile; else : ?>
          <?php /* 公開記事が0件のあいだは、TOPと同じ従来の固定4枚を表示する（空ページにしない） */ ?>
          <div class="mcard reveal"><img src="<?php echo PREWORKS_IMG; ?>/mentor-1.jpg" alt="仲宗根 偉子"><div class="mbody"><h3>仲宗根 偉子</h3><p class="m-attr">オンライン秘書歴：3年／40代／3児ママ</p><p>受講中の不安やつまずきにも寄り添いながら、一人ひとりのペースに合わせてサポートします。オンライン秘書として働くための一歩を、一緒に進んでいきましょう！</p></div></div>
          <div class="mcard reveal"><img src="<?php echo PREWORKS_IMG; ?>/mentor-2.jpg" alt="髙松 由衣"><div class="mbody"><h3>髙松 由衣</h3><p class="m-attr">オンライン秘書歴：2年／30代／1児ママ</p><p>URAKATAのPR・広報を担当。「伝える力」の基本から、実務で使える形でお伝えします。</p></div></div>
          <div class="mcard reveal"><img src="<?php echo PREWORKS_IMG; ?>/mentor-3.jpg" alt="戸田 あかね"><div class="mbody"><h3>戸田 あかね</h3><p class="m-attr">オンライン秘書歴：2年／30代／3児ママ</p><p>URAKATAのカスタマーサクセス担当。クライアント対応の勘所を、一緒に磨きます。</p></div></div>
          <div class="mcard reveal"><img src="<?php echo PREWORKS_IMG; ?>/mentor-4.jpg" alt="玉木 達子"><div class="mbody"><h3>玉木 達子</h3><p class="m-attr">オンライン秘書歴：2年半／30代／3児ママ</p><p>URAKATAのコーディネートディレクター。案件と人をつなぐ視点で、あなたに合う進め方を一緒に考えます。</p></div></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="pw-body sec-pad" style="padding-top:0;"><div class="wrap"><div class="article pw-article">
    <?php pw_cta( array(
        'pos' => 'mentor',
    ) ); ?>
    <?php pw_related( array( 'program', 'career', 'consultation' ) ); ?>
  </div></div></section>

  <?php pw_final_cta(); ?>
</main>
<?php get_footer(); ?>
