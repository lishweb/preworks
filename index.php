<?php get_header(); ?>
<main>
  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ ブログ</div>
    <span class="eyebrow">Blog</span><h1>ブログ・お役立ち記事</h1><p class="sub">在宅ワーク・オンライン秘書・本業並行のヒント。</p>
  </div></section>
  <section class="blog sec-pad"><div class="wrap"><div class="bloggrid">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <a class="acard reveal" href="<?php the_permalink(); ?>">
        <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'preworks_card', array( 'alt' => get_the_title() ) ); } else { ?><img src="<?php echo PREWORKS_IMG; ?>/case-1.jpg" alt=""><?php } ?>
        <div class="abody"><div class="meta"><span class="cat"><?php $c = get_the_category(); echo $c ? esc_html( $c[0]->name ) : 'お役立ち'; ?></span><span><?php echo get_the_date( 'Y.m.d' ); ?></span></div><h3><?php the_title(); ?></h3></div>
      </a>
    <?php endwhile; else : ?>
      <p style="grid-column:1/-1;text-align:center;color:var(--muted);">記事はまだありません。WordPress管理画面の「投稿」から記事を追加してください。</p>
    <?php endif; ?>
  </div>
  <?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
  </div></section>
</main>
<?php get_footer(); ?>
