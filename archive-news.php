<?php
/**
 * B07 お知らせ（一覧）  /news/
 * 投稿タイプ news（functions.php で登録済み）のアーカイブ。
 * H1・パンくず・title・description・CollectionPage/BreadcrumbList は登録表（inc/pw-pages.php の 'news'）から自動。
 * 開講情報・メディア掲載・実績数値（お仕事獲得率・URAKATA導入企業数など）の更新を告知する。
 */
get_header();
$pw_news = pw_page( 'news' );
?>
<main class="pw-main">
  <?php pw_page_hero( array(
      'h1'      => $pw_news['h1'],
      'eyebrow' => $pw_news['eyebrow'],
      'lead'    => 'オンライン秘書講座preworksからのお知らせです。開講情報やメディア掲載、修了生のお仕事獲得状況などの実績数値の更新、サイトの更新情報をお届けします。',
  ) ); ?>

  <section class="pw-body sec-pad"><div class="wrap"><div class="article pw-article">
    <?php if ( have_posts() ) : ?>
      <div class="pw-related-list pw-news-list" style="grid-template-columns:1fr;">
        <?php while ( have_posts() ) : the_post(); ?>
          <a class="pw-related-item" href="<?php the_permalink(); ?>">
            <span class="pw-related-d"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time></span>
            <span class="pw-related-t"><?php the_title(); ?></span>
            <?php if ( has_excerpt() ) : ?><span class="pw-related-d"><?php echo esc_html( wp_html_excerpt( get_the_excerpt(), 80, '…' ) ); ?></span><?php endif; ?>
          </a>
        <?php endwhile; ?>
      </div>
      <?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '前へ', 'next_text' => '次へ' ) ); ?>
    <?php else : ?>
      <p style="text-align:center;color:var(--muted);">現在、お知らせはありません。</p>
    <?php endif; ?>

    <h2 data-notoc>最新の実績</h2>
    <p>preworksとURAKATAの主な実績です。数値は更新のたびにこのページでもお知らせします（それぞれ記載の時点のものです）。</p>
    <?php pw_stats(); ?>

    <?php pw_related( array( 'career', 'price', 'program' ) ); ?>
  </div></div></section>

  <?php pw_final_cta(); ?>
</main>
<?php get_footer(); ?>
