<?php get_header(); ?>
<main>
<?php while ( have_posts() ) : the_post(); ?>
  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ <a href="<?php echo home_url('/blog/'); ?>">ブログ</a> ／ 記事</div>
    <span class="eyebrow">Blog</span><h1><?php the_title(); ?></h1>
    <p class="sub"><?php $c = get_the_category(); echo $c ? esc_html( $c[0]->name ) : 'お役立ち'; ?> ／ <?php echo get_the_date( 'Y.m.d' ); ?></p>
  </div></section>
  <section class="about sec-pad"><div class="wrap"><div class="article reveal">
    <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'class' => 'feat' ) ); } ?>
    <?php the_content(); ?>
    <div class="byline"><img src="<?php echo PREWORKS_IMG; ?>/profile.jpg" alt="Preworks編集部"><div><div class="bn">Preworks編集部</div><div class="br">在宅ワーク・オンライン秘書のリアルな情報をお届けします。</div></div></div>
    <div class="center" style="margin-top:44px;"><a class="morelink" href="<?php echo home_url('/blog/'); ?>">ブログ一覧へ戻る</a></div>
  </div></div></section>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
