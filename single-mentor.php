<?php get_header(); ?>
<main>
<?php while ( have_posts() ) : the_post();
  $pw_catch   = get_post_meta( get_the_ID(), '_pw_catch', true );
  $catch      = $pw_catch ? $pw_catch : ( has_excerpt() ? get_the_excerpt() : get_the_title() );
  $pw_youtube = get_post_meta( get_the_ID(), '_pw_youtube', true );
  $pw_profile = get_post_meta( get_the_ID(), '_pw_profile', true );
  $pw_qa      = get_post_meta( get_the_ID(), '_pw_qa', true );
  $pw_role    = get_post_meta( get_the_ID(), '_pw_role', true );
  $pw_attr    = get_post_meta( get_the_ID(), '_pw_attr', true );
  $pw_photo   = preworks_mentor_photo_url( get_the_ID(), 'medium' );
?>
  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ <a href="<?php echo esc_url( get_post_type_archive_link( 'mentor' ) ); ?>">メンター紹介</a></div>
    <span class="eyebrow">Mentor</span><h1><?php echo wp_kses_post( $catch ); ?></h1>
    <?php if ( $pw_attr || $pw_role ) : ?>
      <?php /* キャッチコピー未入力時は上の <h1> が氏名になるため、ここで氏名を繰り返さない。v1.0.16
               キャッチコピーがある場合の出力は従来と完全に同じ。 */ ?>
      <p class="sub"><?php
        if ( $pw_catch ) { the_title(); }
        if ( $pw_attr ) { echo $pw_catch ? '（' . preworks_mentor_attr_html( $pw_attr ) . '）' : preworks_mentor_attr_html( $pw_attr ); }
        if ( $pw_role ) { echo ( ( $pw_catch || $pw_attr ) ? '｜' : '' ) . esc_html( $pw_role ); }
      ?></p>
    <?php endif; ?>
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
    /* 下層が未入稿のメンターは「他のメンター」にも出さない。v1.0.16
       このカードは「詳細はこちら」ボタン付きなので、リンクしないカードを混ぜると
       一覧・TOPと挙動が食い違う。キャッチコピーが入った時点で自動的に出てくる。 */
    $rel = new WP_Query( array( 'post_type' => 'mentor', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'orderby' => 'menu_order title', 'order' => 'ASC',
        'meta_query' => array( array( 'key' => '_pw_detail_ready', 'value' => '1' ) ) ) );
    if ( $rel->have_posts() ) : ?>
  <section class="mentors sec-pad"><span class="orb m soft alt" style="top:40px;right:-100px;"></span><div class="wrap">
    <div class="mod-head" style="text-align:center;margin-bottom:28px;"><span class="eyebrow">Mentor</span><h2 class="htitle">preworksのメンター紹介</h2><p class="subhead">現役のオンライン秘書がマンツーマンでサポート！</p></div>
    <div class="mgrid-v">
      <?php while ( $rel->have_posts() ) : $rel->the_post();
        $r_attr  = get_post_meta( get_the_ID(), '_pw_attr', true );
        $r_cm    = get_post_meta( get_the_ID(), '_pw_comment', true );
        $r_catch = get_post_meta( get_the_ID(), '_pw_catch', true ); ?>
        <a class="mcard-v reveal" href="<?php the_permalink(); ?>">
          <?php $ph = preworks_mentor_photo_url(); if ( $ph ) : ?><img src="<?php echo esc_url( $ph ); ?>" alt="<?php the_title_attribute(); ?>"><?php endif; ?>
          <div class="mvbody">
            <?php if ( $r_catch ) : ?><p class="mv-catch"><?php echo esc_html( $r_catch ); ?></p><?php endif; ?>
            <?php if ( $r_cm ) : ?><p class="mv-comment"><?php echo esc_html( $r_cm ); ?></p><?php endif; ?>
            <p class="mv-meta"><?php the_title(); ?><?php if ( $r_attr ) : ?><span class="mv-div">｜</span><?php echo esc_html( $r_attr ); ?><?php endif; ?></p>
            <span class="mv-btn">詳細はこちら<span class="btn-arw">▶</span></span>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <p class="slider-hint">← 横にスワイプして他のメンターを見る →</p>
    <div class="center" style="margin-top:40px;"><a class="morelink" href="<?php echo esc_url( get_post_type_archive_link( 'mentor' ) ); ?>">メンター紹介一覧へ戻る</a></div>
  </div></section>
  <?php endif; ?>

<?php endwhile; ?>

  <section class="finalcta sec-pad" id="contact"><div class="wrap">
    <span class="eyebrow" style="color:var(--gold)">Contact</span>
    <div class="mincho finalcta-h">「いつか在宅で働きたい」を、<br>今日から少しずつ形に。</div>
    <p>無料相談では、未経験からオンライン秘書を始めた方の事例をもとに、<br>今のあなたが何から始めればよいのかを一緒に整理します。</p>
    <a class="btn btn-line" href="<?php echo esc_url( preworks_line_url() ); ?>" target="_blank" rel="noopener"><img src="<?php echo PREWORKS_IMG; ?>/LINE_Brand_icon.png" alt="LINE" class="liico">LINEで無料オンライン相談をする</a>
  </div></section>

</main>
<?php get_footer(); ?>
