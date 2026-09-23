<?php get_header(); ?>
<main>
  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ 修了生の声</div>
    <div class="headblock"><span class="eyebrow">Voice</span><h1 class="htitle">preworks<span class="mk">修了生の声</span></h1><p class="voice-badge">オンライン秘書デビュー率 <b>100<span class="vb-u">%</span></b><span class="vb-sub">修了生6名中6名がお仕事獲得につながっています（※2026年7月時点）</span></p></div>
  </div></section>
  <section class="voice sec-pad"><div class="wrap"><div class="vgrid">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <?php $attr = get_post_meta( get_the_ID(), '_pw_attr', true ); $hl = get_post_meta( get_the_ID(), '_pw_hl', true ); $comment = get_post_meta( get_the_ID(), '_pw_comment', true ); ?>
      <?php /* 下層（キャッチコピー）が未入稿ならカードをリンクにしない。v1.0.17 */
        $v_rdy  = preworks_entry_is_ready();
        $v_tag  = $v_rdy ? 'a' : 'div';
        $v_atts = $v_rdy ? ' href="' . esc_url( get_permalink() ) . '" style="text-decoration:none;color:inherit;"' : ''; ?>
      <<?php echo $v_tag; ?> class="vcard vcard-cpt reveal"<?php echo $v_atts; ?>>
        <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'preworks_card', array( 'class' => 'vph', 'alt' => get_the_title() ) ); } else { ?><img class="vph" src="<?php echo PREWORKS_IMG; ?>/voice-1.jpg" alt=""><?php } ?>
        <div class="vbody">
          <div class="who"><?php the_title(); ?></div>
          <?php if ( $attr ) : ?><span class="v-attr"><?php echo esc_html( $attr ); ?></span><?php endif; ?>
          <?php if ( $hl ) : ?><p class="vhl-q"><?php echo esc_html( $hl ); ?></p><?php endif; ?>
          <?php if ( $comment ) : ?><p class="v-comment"><?php echo esc_html( $comment ); ?></p><?php endif; ?>
          <?php if ( $v_rdy ) : ?><span class="v-detail-btn">詳細はこちら<span class="btn-arw">▶</span></span><?php endif; ?>
        </div>
      </<?php echo $v_tag; ?>>
    <?php endwhile; else : ?>
      <p style="grid-column:1/-1;text-align:center;color:var(--muted);">修了生の声はまだありません。管理画面の「修了生の声 → 新規追加」から追加してください。</p>
    <?php endif; ?>
  </div></div></section>
  <section class="finalcta sec-pad" id="contact"><div class="wrap">
    <span class="eyebrow" style="color:var(--gold)">Contact</span>
    <div class="mincho finalcta-h">「いつか在宅で働きたい」を、<br>今日から少しずつ形に。</div>
    <p>無料相談では、未経験からオンライン秘書を始めた方の事例をもとに、<br>今のあなたが何から始めればよいのかを一緒に整理します。</p>
    <a class="btn btn-line" href="<?php echo esc_url( preworks_line_url() ); ?>" target="_blank" rel="noopener"><img src="<?php echo PREWORKS_IMG; ?>/LINE_Brand_icon.png" alt="LINE" class="liico">LINEで無料オンライン相談をする</a>
  </div></section>
</main>
<?php get_footer(); ?>
