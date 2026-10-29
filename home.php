<?php
/**
 * B06 お役立ちブログ一覧  /blog/（投稿一覧＝設定 → 表示設定 →「投稿ページ」に指定した固定ページ）
 * 主要KW：オンライン秘書 ブログ
 * H1・パンくず・title・description・CollectionPage/BreadcrumbList は登録表（inc/pw-pages.php の 'column'）から自動。
 * URLは /blog/ のまま。新しい投稿タイプは作らず、標準の投稿＋カテゴリで運用する。
 * 記事0件でも、「ブログは準備中です」の表示とサービスページへの導線（関連ページ・CTA）が出るので空ページにならない。
 */
get_header();

$pw_col = pw_page( 'column' );
?>
<main class="pw-main">
  <?php pw_page_hero( array(
      'h1'      => $pw_col['h1'],
      'eyebrow' => $pw_col['eyebrow'],
      'lead'    => 'オンライン秘書・在宅ワークに役立つ情報をお届けします。気になるカテゴリーで絞り込んで、知りたい記事を探せます。',
  ) ); ?>

  <section class="blog blog-index sec-pad"><div class="wrap">
    <?php
      $q = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
      if ( $q->have_posts() ) :
        /* 絞り込みタブ：公開記事のあるカテゴリだけ（hierarchical=false＝子にだけ記事がある親は出さない。記事の data-cat は直接のカテゴリのみのため） */
        $pw_tabs = array();
        foreach ( get_categories( array( 'hide_empty' => true, 'hierarchical' => false ) ) as $t ) {
            if ( $t->slug === 'uncategorized' ) continue;
            $pw_tabs[ $t->slug ] = $t->name;
        }
    ?>
    <?php if ( count( $pw_tabs ) >= 1 ) : /* 公開記事のあるカテゴリーが1つだけでも「すべて」＋そのタブを出す */ ?>
    <div class="blogtabs">
      <button class="btab is-active" data-cat="*" type="button">すべて</button>
      <?php foreach ( $pw_tabs as $pw_slug => $pw_name ) : ?>
        <button class="btab" data-cat="<?php echo esc_attr( $pw_slug ); ?>" type="button"><?php echo esc_html( $pw_name ); ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="bloggrid" id="bloggrid">
      <?php while ( $q->have_posts() ) : $q->the_post();
          $slugs = wp_list_pluck( get_the_category(), 'slug' ); ?>
        <a class="acard reveal blogitem" data-cat="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>" href="<?php the_permalink(); ?>">
          <?php pw_blog_thumb(); ?>
          <div class="abody"><div class="meta"><?php if ( function_exists( 'pw_is_guide_post' ) && pw_is_guide_post( get_the_ID() ) ) : ?><span class="cat pw-guide-badge">ガイド</span><?php endif; ?><span class="cat"><?php $c = get_the_category(); echo $c ? esc_html( $c[0]->name ) : 'お役立ち'; ?></span><span><?php echo get_the_date( 'Y.m.d' ); ?></span></div><h3><?php the_title(); ?></h3><span class="readmore">続きを読む →</span></div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php if ( $q->post_count > 6 ) : /* 6件（JSの STEP）を超えるときだけ表示 */ ?>
    <div class="center" style="margin-top:36px;"><button class="btn-more" id="blogMore" type="button">記事をもっと見る　→</button></div>
    <?php endif; ?>
    <?php else : ?>
      <p class="center" style="color:var(--muted);">ブログは準備中です。preworksの講座内容や修了後の働き方は、下のページからご覧ください。</p>
    <?php endif; ?>
  </div></section>

  <section class="pw-body sec-pad" style="padding-top:0;"><div class="wrap"><div class="article pw-article">
    <?php pw_related( array( 'program', 'career', 'price', 'for-mama', 'faq' ), 'preworksについて知る' ); ?>
    <?php pw_cta(); ?>
  </div></div></section>

  <?php pw_final_cta(); ?>
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
