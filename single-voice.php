<?php get_header(); ?>
<main>
<?php while ( have_posts() ) : the_post();
  $pw_catch   = get_post_meta( get_the_ID(), '_pw_catch', true );
  $catch      = $pw_catch ? $pw_catch : ( has_excerpt() ? get_the_excerpt() : get_the_title() );
  $pw_youtube = get_post_meta( get_the_ID(), '_pw_youtube', true );
  $pw_profile = get_post_meta( get_the_ID(), '_pw_profile', true );
  $pw_qa      = get_post_meta( get_the_ID(), '_pw_qa', true );
  $pw_photo   = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
?>
  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ <a href="<?php echo home_url('/voice/'); ?>">修了生の声</a></div>
    <span class="eyebrow">Voice</span><h1><?php echo wp_kses_post( $catch ); ?></h1>
  </div></section>
  <section class="about sec-pad"><div class="wrap" style="max-width:840px;">
    <div class="article reveal">
      <?php if ( $pw_youtube ) : ?>
        <div style="margin:0 0 32px;position:relative;width:100%;padding-bottom:56.25%;height:0;border-radius:16px;overflow:hidden;box-shadow:0 18px 44px rgba(150,80,45,.14);background:#000;"><iframe style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" src="https://www.youtube.com/embed/<?php echo esc_attr( $pw_youtube ); ?>" title="<?php the_title_attribute(); ?>インタビュー" loading="lazy" allowfullscreen></iframe></div>
      <?php endif; ?>
      <?php if ( $pw_profile ) : ?>
        <div class="callout"><strong>Profile</strong><br><?php echo nl2br( esc_html( $pw_profile ) ); ?></div>
      <?php endif; ?>
      <?php
        if ( $pw_qa ) {
            echo preworks_render_voice_qa( $pw_qa, $pw_photo, get_the_title(), preworks_qa_opts( get_the_ID() ) );
        } else {
            the_content();
        }
      ?>
    </div>
  </div></section>

  <?php
    /* 下層が未入稿の記事は「他の修了生」にも出さない。v1.0.17
       このカードは「詳細はこちら」ボタン付きなので、リンクしないカードを混ぜると
       トップ・一覧と挙動が食い違う。キャッチコピーが入った時点で自動的に出てくる。 */
    $rel = new WP_Query( array( 'post_type' => 'voice', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'orderby' => 'rand',
        'meta_query' => array( array( 'key' => '_pw_detail_ready', 'value' => '1' ) ) ) );
    if ( $rel->have_posts() ) : ?>
  <section class="voice sec-pad"><div class="wrap">
    <div class="mod-head" style="text-align:center;margin-bottom:28px;"><span class="eyebrow">Other Interview</span><h2 class="htitle">他の修了生のインタビュー</h2></div>
    <div class="vgrid">
      <?php while ( $rel->have_posts() ) : $rel->the_post(); ?>
        <?php $r_attr = get_post_meta( get_the_ID(), '_pw_attr', true ); $r_hl = get_post_meta( get_the_ID(), '_pw_hl', true ); $r_cm = get_post_meta( get_the_ID(), '_pw_comment', true ); ?>
        <a class="vcard vcard-cpt reveal" href="<?php the_permalink(); ?>" style="text-decoration:none;color:inherit;">
          <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'preworks_card', array( 'class' => 'vph', 'alt' => get_the_title() ) ); } else { ?><img class="vph" src="<?php echo PREWORKS_IMG; ?>/voice-1.jpg" alt=""><?php } ?>
          <div class="vbody">
            <div class="who"><?php the_title(); ?></div>
            <?php if ( $r_attr ) : ?><span class="v-attr"><?php echo esc_html( $r_attr ); ?></span><?php endif; ?>
            <?php if ( $r_hl ) : ?><p class="vhl-q"><?php echo esc_html( $r_hl ); ?></p><?php endif; ?>
            <?php if ( $r_cm ) : ?><p class="v-comment"><?php echo esc_html( $r_cm ); ?></p><?php endif; ?>
            <span class="v-detail-btn">詳細はこちら<span class="btn-arw">▶</span></span>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div></section>
  <?php endif; ?>

  <?php pw_final_cta(); /* 2026-10：全ページ共通の「無料オンライン勉強会」CTA（inc/pw-core.php） */ ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
