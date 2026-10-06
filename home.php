<?php
/**
 * B06 コラム一覧  /blog/（投稿一覧＝設定 → 表示設定 →「投稿ページ」に指定した固定ページ）
 * 主要KW：オンライン秘書 コラム
 * H1・パンくず・title・description・CollectionPage/BreadcrumbList は登録表（inc/pw-pages.php の 'column'）から自動。
 * URLは /blog/ のまま。新しい投稿タイプは作らず、標準の投稿＋カテゴリで運用する。
 * 記事0件でも、カテゴリ（テーマ）の一覧とサービスページへの導線が出るので空ページにならない。
 */
get_header();

/* コラムの6カテゴリ。
 * 管理画面でこのスラッグのカテゴリを作れば、記事が公開された時点でカードがカテゴリ一覧へのリンクになる。
 * カテゴリに記事が無いあいだはリンクにしない（一般的な解説はコラムで扱うため、固定ページへの代替リンクは張らない）。 */
$pw_col_cats = array(
    'job'    => array( 'name' => '仕事を知る',       'd' => '業務内容別の解説や1日の流れなど、オンライン秘書の仕事内容を知るためのコラム。' ),
    'start'  => array( 'name' => 'なり方・始め方',   'd' => '未経験からの道のり、独学でできること、応募・面接の準備など、始め方のコラム。' ),
    'money'  => array( 'name' => 'お金・働き方',     'd' => '時給と月額契約の違い、開業届や確定申告、扶養の考え方など、お金と働き方のコラム。' ),
    'skill'  => array( 'name' => 'スキル・ツール',   'd' => 'スプレッドシート、Notion、Chatwork、Canva、AIの使い方など、実務スキルのコラム。' ),
    'life'   => array( 'name' => '子育て・年代別の悩み', 'd' => '小1の壁、育児と在宅ワークの時間のやりくり、50代からの再挑戦など、暮らしと両立するためのコラム。' ),
    'remote' => array( 'name' => '在宅ワーク全般',   'd' => '在宅ワークの種類の比べ方や、注意したい募集の見分け方など、在宅ワーク全般のコラム。' ),
);
$pw_col = pw_page( 'column' );
?>
<main class="pw-main">
  <?php pw_page_hero( array(
      'h1'      => $pw_col['h1'],
      'eyebrow' => $pw_col['eyebrow'],
      'lead'    => 'オンライン秘書・在宅ワークに関するコラムです。仕事内容、なり方・始め方、お金と働き方、スキル・ツール、子育てや年代別の悩み、在宅ワーク全般の6つのテーマから、知りたいことを探せます。',
  ) ); ?>

  <section class="blog sec-pad"><div class="wrap">
    <div class="headblock"><span class="eyebrow">Category</span><h2 class="htitle">テーマから<span class="mk">探す</span></h2></div>
    <div class="pw-cards pw-cols-3" style="margin-top:34px;">
      <?php foreach ( $pw_col_cats as $pw_slug => $pw_c ) :
        $pw_term = get_term_by( 'slug', $pw_slug, 'category' );
        $pw_href = '';
        $pw_more = '';
        if ( $pw_term && $pw_term->count > 0 ) {
            $pw_href = get_term_link( $pw_term );
            $pw_more = 'コラムを読む';
        }
        $pw_tag = $pw_href ? 'a' : 'div'; ?>
        <<?php echo $pw_tag; ?> class="pw-card<?php echo $pw_href ? ' is-link' : ''; ?>"<?php echo $pw_href ? ' href="' . esc_url( $pw_href ) . '"' : ''; ?>>
          <span class="pw-card-tag"><?php echo esc_html( $pw_slug ); ?></span>
          <p class="pw-card-t"><?php echo esc_html( $pw_c['name'] ); ?></p>
          <p class="pw-card-d"><?php echo esc_html( $pw_c['d'] ); ?></p>
          <?php if ( $pw_more ) : ?><span class="pw-card-more"><?php echo esc_html( $pw_more ); ?></span><?php endif; ?>
        </<?php echo $pw_tag; ?>>
      <?php endforeach; ?>
    </div>
  </div></section>

  <section class="blog sec-pad" style="padding-top:0;"><div class="wrap">
    <div class="headblock"><span class="eyebrow">Latest</span><h2 class="htitle">新着<span class="mk">コラム</span></h2></div>
    <?php
      $q = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
      if ( $q->have_posts() ) :
        /* 絞り込みタブ：記事のあるカテゴリだけ。6カテゴリを先に、その他（既存カテゴリ）を後に並べる */
        $pw_tabs = array();
        foreach ( array_keys( $pw_col_cats ) as $pw_slug ) {
            $t = get_term_by( 'slug', $pw_slug, 'category' );
            if ( $t && $t->count > 0 ) $pw_tabs[ $t->slug ] = $t->name;
        }
        foreach ( get_categories( array( 'hide_empty' => true ) ) as $t ) {
            if ( $t->slug === 'uncategorized' || isset( $pw_tabs[ $t->slug ] ) ) continue;
            $pw_tabs[ $t->slug ] = $t->name;
        }
    ?>
    <?php if ( count( $pw_tabs ) > 1 ) : ?>
    <div class="blogtabs" style="margin-top:34px;">
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
          <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'preworks_card', array( 'alt' => get_the_title() ) ); } else { ?><img src="<?php echo PREWORKS_IMG; ?>/case-1.jpg" alt=""><?php } ?>
          <div class="abody"><div class="meta"><?php if ( function_exists( 'pw_is_guide_post' ) && pw_is_guide_post( get_the_ID() ) ) : ?><span class="cat pw-guide-badge">ガイド</span><?php endif; ?><span class="cat"><?php $c = get_the_category(); echo $c ? esc_html( $c[0]->name ) : 'お役立ち'; ?></span><span><?php echo get_the_date( 'Y.m.d' ); ?></span></div><h3><?php the_title(); ?></h3><span class="readmore">続きを読む →</span></div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php if ( $q->post_count > 6 ) : /* 6件（JSの STEP）を超えるときだけ表示 */ ?>
    <div class="center" style="margin-top:36px;"><button class="btn-more" id="blogMore" type="button">記事をもっと見る　→</button></div>
    <?php endif; ?>
    <?php else : ?>
      <p class="center" style="margin-top:34px;color:var(--muted);">コラムは準備中です。preworksの講座内容や修了後の働き方は、下のページからご覧ください。</p>
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
