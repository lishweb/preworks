<?php get_header(); ?>
<main>

  <section class="mentors sec-pad" id="mentor">
    <span class="orb m soft alt" style="top:40px;right:-100px;"></span>
    <div class="wrap">
      <div class="bc" style="margin-bottom:22px;"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ メンター紹介</div>
      <div class="headblock"><span class="eyebrow">Mentor</span><h1 class="htitle">現役オンライン秘書が <span class="mk">あなた専属のメンターに</span></h1><p class="subhead">オンライン秘書サービス「URAKATA」の実務メンバーが、1on1でサポートします。</p></div>
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
          <p style="grid-column:1/-1;text-align:center;color:var(--muted);">メンター紹介はまだありません。管理画面の「メンター紹介 → 新規追加」から追加してください。</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="finalcta sec-pad" id="contact"><div class="wrap">
    <span class="eyebrow" style="color:var(--gold)">Contact</span>
    <div class="mincho finalcta-h">「いつか在宅で働きたい」を、<br>今日から少しずつ形に。</div>
    <p>無料相談では、未経験からオンライン秘書を始めた方の事例をもとに、<br>今のあなたが何から始めればよいのかを一緒に整理します。</p>
    <a class="btn btn-line" href="<?php echo esc_url( preworks_line_url() ); ?>" target="_blank" rel="noopener"><img src="<?php echo PREWORKS_IMG; ?>/LINE_Brand_icon.png" alt="LINE" class="liico">LINEで無料オンライン相談をする</a>
  </div></section>

</main>
<?php get_footer(); ?>
