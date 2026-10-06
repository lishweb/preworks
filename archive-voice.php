<?php
/**
 * S11 修了生の声（一覧）  /voice/
 * 主要KW：オンライン秘書 講座 口コミ
 * H1・パンくず・title・description・BreadcrumbList は登録表（inc/pw-pages.php の 'voice'）から自動。
 * ※自社サイト上の口コミのため Review / AggregateRating は出力しない（Googleのガイドライン対象外）。
 */
get_header();
?>
<main class="pw-main">
  <?php pw_page_hero( array(
      'lead' => 'preworksの修了生の多くは、事務未経験やブランクからスタートした子育て中のママです。受講前の不安、育児との両立の工夫、初めての仕事を獲得するまでを、実際の声でお届けします。',
  ) ); ?>

  <section class="voice sec-pad"><div class="wrap">
    <div class="headblock"><p class="voice-badge">お仕事獲得率 <b><?php echo (int) pw( 'stats.job_rate.value' ); ?><span class="vb-u">%</span></b><span class="vb-sub">修了生<?php echo (int) pw( 'graduates_total' ); ?>名中<?php echo (int) pw( 'graduates_hired' ); ?>名がURAKATAでの稼働につながっています（※<?php echo esc_html( pw( 'stats_as_of' ) ); ?>）</span></p></div>
    <div class="vgrid">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <?php $attr = get_post_meta( get_the_ID(), '_pw_attr', true ); $hl = get_post_meta( get_the_ID(), '_pw_hl', true ); $comment = get_post_meta( get_the_ID(), '_pw_comment', true );
        /* v2.0.0 追加項目：受講プラン・現在の仕事（入力欄は notes/S-c.md の提案で functions.php に追加予定。
           本人の掲載了承が取れるまで要確認ブロックで非公開） */
        $plan = get_post_meta( get_the_ID(), '_pw_plan', true ); $job = get_post_meta( get_the_ID(), '_pw_job', true ); ?>
      <?php /* 下層（キャッチコピー）が未入稿ならカードをリンクにしない。v1.0.17 */
        $v_rdy  = preworks_entry_is_ready();
        $v_tag  = $v_rdy ? 'a' : 'div';
        $v_atts = $v_rdy ? ' href="' . esc_url( get_permalink() ) . '" style="text-decoration:none;color:inherit;"' : ''; ?>
      <<?php echo $v_tag; ?> class="vcard vcard-cpt reveal"<?php echo $v_atts; ?>>
        <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'preworks_card', array( 'class' => 'vph', 'alt' => get_the_title() ) ); } else { ?><img class="vph" src="<?php echo PREWORKS_IMG; ?>/voice-1.jpg" alt=""><?php } ?>
        <div class="vbody">
          <div class="who"><?php the_title(); ?></div>
          <?php if ( $attr ) : ?><span class="v-attr"><?php echo esc_html( $attr ); ?></span><?php endif; ?>
          <?php if ( $plan || $job ) : pw_tbc_start( 'voice_card_info' ); ?>
            <p class="v-attr" style="display:block;margin-top:6px;"><?php if ( $plan ) : ?>受講プラン：<?php echo esc_html( $plan ); ?><?php endif; ?><?php if ( $plan && $job ) echo '<br>'; ?><?php if ( $job ) : ?>現在の仕事：<?php echo esc_html( $job ); ?><?php endif; ?></p>
          <?php pw_tbc_end(); endif; ?>
          <?php if ( $hl ) : ?><p class="vhl-q"><?php echo esc_html( $hl ); ?></p><?php endif; ?>
          <?php if ( $comment ) : ?><p class="v-comment"><?php echo esc_html( $comment ); ?></p><?php endif; ?>
          <?php if ( $v_rdy ) : ?><span class="v-detail-btn">詳細はこちら<span class="btn-arw">▶</span></span><?php endif; ?>
        </div>
      </<?php echo $v_tag; ?>>
    <?php endwhile; else : ?>
      <?php /* 公開記事が0件のあいだは、TOPと同じ従来の固定3枚を表示する（空ページにしない） */ ?>
      <div class="vcard reveal"><img class="vph" src="<?php echo PREWORKS_IMG; ?>/voice-1.jpg" alt="大城さん"><div class="vbody"><div class="who">大城さん<small>30代・2児ママ（6歳・0歳）｜前職：コールセンター業務</small></div><p>新しいことを学ぶ毎日が新鮮でした。初めは事務経験がほぼゼロで不安でしたが、メンターさんの支えのおかげで、最後までやり切れました！</p><a class="vlink" href="https://youtu.be/EhX5RDQ5Jfk" target="_blank" rel="noopener"><span class="vplay">▶</span>修了生の動画をみる</a></div></div>
      <div class="vcard reveal"><img class="vph" src="<?php echo PREWORKS_IMG; ?>/voice-2.jpg" alt="Yさん"><div class="vbody"><div class="who">Yさん<small>50代・1児ママ（19歳）｜前職：営業事務</small></div><p>事務経験はありましたが、自分が必要だと思っていた知識が狭かったと痛感しました。現役のオンライン秘書に相談できるため信頼して学べる環境でした！</p><a class="vlink" href="https://youtu.be/91_5nU7nGL8" target="_blank" rel="noopener"><span class="vplay">▶</span>修了生の動画をみる</a></div></div>
      <div class="vcard reveal"><img class="vph" src="<?php echo PREWORKS_IMG; ?>/voice-3.jpg" alt="Kさん"><div class="vbody"><div class="who">Kさん<small>40代・2児ママ（6歳・1歳）｜前職：専業主婦</small></div><p>受講後はURAKATA以外でも業務委託の契約につながり、学んだことを実務でも活かせています。失敗しても次の改善策を自分で考えられるようになり、自信につながりました！</p><a class="vlink" href="https://youtu.be/F8_b3OvXHj4" target="_blank" rel="noopener"><span class="vplay">▶</span>修了生の動画をみる</a></div></div>
    <?php endif; ?>
    </div>
    <?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '前へ', 'next_text' => '次へ' ) ); ?>
    <?php /* フィルター（年代／前職の事務経験／子どもの年齢）は、公開が6名以上になったら実装する（仕様書S11）。 */ ?>
  </div></section>

  <section class="pw-body sec-pad" style="padding-top:0;"><div class="wrap"><div class="article pw-article">
    <?php pw_cta( array(
        'h'   => '自分と似た経歴の修了生の話をもっと聞いてみたい方は、無料相談でお気軽にどうぞ。',
        'p'   => pw_is_live( 'consultation' )
                 ? '無料オンライン相談では、あなたと似た経歴から在宅ワークを始めた方の事例をもとに、何から始めればよいのかを一緒に整理します。' . pw_a( 'consultation', '無料オンライン相談の内容を見る' )
                 : '無料オンライン相談では、あなたと似た経歴から在宅ワークを始めた方の事例をもとに、何から始めればよいのかを一緒に整理します。受講を前提としない相談だけでも大丈夫です。',
        'pos' => 'voice',
    ) ); ?>
    <?php pw_related( array( 'career', 'program', 'for-mama', 'for-40s-50s', 'price' ) ); ?>
  </div></div></section>

  <?php pw_final_cta(); ?>
</main>
<?php get_footer(); ?>
