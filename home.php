<?php get_header(); ?>
<main>
  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ ブログ</div>
    <span class="eyebrow">Blog</span><h1>ブログ・お役立ち記事</h1><p class="sub">在宅ワーク・オンライン秘書・本業並行のヒント。</p>
  </div></section>
  <section class="blog sec-pad"><div class="wrap">
    <?php $fixed_cats = array( 'オンライン秘書', '在宅ワーク', '修了生の声' ); ?>
    <div class="blogtabs">
      <button class="btab is-active" data-cat="*" type="button">すべて</button>
      <?php foreach ( $fixed_cats as $cname ) :
        $cat = get_term_by( 'name', $cname, 'category' );
        if ( ! $cat ) continue; ?>
        <button class="btab" data-cat="<?php echo esc_attr( $cat->slug ); ?>" type="button"><?php echo esc_html( $cname ); ?></button>
      <?php endforeach; ?>
    </div>
    <div class="bloggrid" id="bloggrid">
      <?php
        $q = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
        if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
          $slugs = wp_list_pluck( get_the_category(), 'slug' );
      ?>
        <a class="acard reveal blogitem" data-cat="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>" href="<?php the_permalink(); ?>">
          <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'preworks_card', array( 'alt' => get_the_title() ) ); } else { ?><img src="<?php echo PREWORKS_IMG; ?>/case-1.jpg" alt=""><?php } ?>
          <div class="abody"><div class="meta"><span class="cat"><?php $c = get_the_category(); echo $c ? esc_html( $c[0]->name ) : 'お役立ち'; ?></span><span><?php echo get_the_date( 'Y.m.d' ); ?></span></div><h3><?php the_title(); ?></h3><span class="readmore">続きを読む →</span></div>
        </a>
      <?php endwhile; wp_reset_postdata(); else : ?>
        <p style="grid-column:1/-1;text-align:center;color:var(--muted);">記事はまだありません。WordPress管理画面の「投稿」から記事を追加してください。</p>
      <?php endif; ?>
    </div>
    <div class="center" style="margin-top:36px;"><button class="btn-more" id="blogMore" type="button">記事をもっと見る　→</button></div>
  </div></section>
</main>
<script>
(function(){
  var STEP = 6, grid = document.getElementById('bloggrid');
  if (!grid) return;
  var items = [].slice.call(grid.querySelectorAll('.blogitem'));
  var tabs  = [].slice.call(document.querySelectorAll('.btab'));
  var moreBtn = document.getElementById('blogMore');
  var cat = '*', shown = STEP;
  function render(){
    var match = items.filter(function(el){
      return cat === '*' || (' ' + el.getAttribute('data-cat') + ' ').indexOf(' ' + cat + ' ') > -1;
    });
    items.forEach(function(el){ el.style.display = 'none'; });
    match.slice(0, shown).forEach(function(el){ el.style.display = ''; });
    if (moreBtn) moreBtn.style.display = match.length > shown ? '' : 'none';
  }
  tabs.forEach(function(t){ t.addEventListener('click', function(){
    tabs.forEach(function(x){ x.classList.remove('is-active'); });
    t.classList.add('is-active'); cat = t.getAttribute('data-cat'); shown = STEP; render();
  }); });
  if (moreBtn) moreBtn.addEventListener('click', function(){ shown += STEP; render(); });
  render();
})();
</script>
<?php get_footer(); ?>
