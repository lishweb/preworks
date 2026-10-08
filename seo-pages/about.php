<?php
/**
 * preworksについて（About）  /about/
 * 2026-10 新規。素材: preworks-about-assets.zip（about-preworks.html・images）
 *
 * - 冒頭「preworksとは？」は TOP（front-page.php「② preworksとは」）の .about/.compare をそのまま採用。
 *   見出しは pw_page_hero() の H1（登録表 inc/pw-pages.php の 'about'）で1回だけ出し、TOP の h2 は出さない。
 *   実績数値は共通データ（pw_stats / inc/pw-data.php）を優先する。
 * - OUR STORY / VISION / URAKATA TRACK RECORD / MESSAGE は納品HTMLの文言・画像を基準に実装。
 *   数値は inc/pw-data.php（urakata.applicants_total / pass_rate / workers_total）から出す。
 * - FREE SEMINAR は全ページ共通の末尾CTA pw_final_cta()（inc/pw-core.php）をそのまま使う（二重に出さない）。
 * - title / description / パンくず / AboutPage・BreadcrumbList は登録表から自動。CSS は assets/css/pw-pages.css の .pw-about-*。
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$pw_img = PREWORKS_IMG . '/about';
$pw_rec = pw( 'stats.urakata_companies' );
?>
<main class="pw-main pw-about">
  <?php pw_page_hero(); ?>

  <!-- preworksとは（TOP「② preworksとは」と同じ比較フロー） -->
  <section class="about sec-pad pw-about-intro" id="about-intro">
    <span class="orb m soft" style="top:-90px;right:-80px;"></span>
    <div class="wrap">
      <div class="compare reveal">
        <p class="compare-lead">preworksは、スクールでも求人でもない、<b>新しい場所</b>です。<br>座学と実践を同時に行い、「本番に近い環境で実践ワーク」を繰り返しながら、<b>2ヵ月後には実際の案件をひとりで回せるようになる</b>プログラムです。</p>
        <div class="compare-rows">
          <div class="cmp-row old">
            <span class="cmp-label">従来の流れ</span>
            <div class="cmp-flow">
              <span class="cmp-box">座学で知識を学ぶ</span>
              <span class="cmp-arw">→</span>
              <span class="cmp-box">実績なしで仕事探し</span>
              <span class="cmp-arw">→</span>
              <span class="cmp-box cmp-q">？</span>
            </div>
          </div>
          <div class="cmp-row new">
            <span class="cmp-label">preworksの流れ</span>
            <div class="cmp-flow">
              <span class="cmp-box">知識＋実務を学ぶ</span>
              <span class="cmp-arw">→</span>
              <span class="cmp-box">実務経験・実績をつくる</span>
              <span class="cmp-arw">→</span>
              <span class="cmp-box cmp-goal">自分に合う働き方で仕事開始</span>
            </div>
          </div>
        </div>
        <p class="compare-note">受講中はオンライン秘書サービス「URAKATA」の案件で実務経験と実績をつくれます。修了後はURAKATAで続けることも、自分で案件を獲得することもできます。</p>
      </div>
      <?php pw_stats(); ?>
    </div>
  </section>

  <!-- OUR STORY -->
  <section class="pw-about-sec pw-about-story" id="story">
    <div class="wrap">
      <div class="headblock"><span class="eyebrow">Our Story</span><h2 class="htitle">preworksが生まれた理由</h2></div>
      <div class="pw-about-copy">
        <p>私たちはLISHは、オンライン秘書サービス「URAKATA」を運営しており、これまでに<?php echo number_format( (int) pw( 'urakata.applicants_total' ) ); ?>件を超えるご応募をいただきました。</p>
        <p>しかし、選考を通過できたのは約<?php echo (int) pw( 'urakata.pass_rate' ); ?>％にとどまり、多くの方が仕事を始めるために必要な準備や実践経験が不足しているという現状がありました。</p>
        <p>そんな課題を解決し、学びから仕事へつながる環境をつくるために、URAKATAの採用・運営の実践経験をもとに、preworksは生まれました。</p>
      </div>
      <div class="pw-about-flow">
        <div class="pw-about-flowbox">累計応募<br><span class="pw-about-num"><?php echo number_format( (int) pw( 'urakata.applicants_total' ) ); ?></span>件超</div>
        <div class="pw-about-arrow" aria-hidden="true">→</div>
        <div class="pw-about-flowbox">選考通過率<br>約<span class="pw-about-num"><?php echo (int) pw( 'urakata.pass_rate' ); ?></span>％</div>
        <div class="pw-about-arrow" aria-hidden="true">→</div>
        <div class="pw-about-flowbox"><span class="pw-about-brand">preworks</span><br>誕生</div>
      </div>
    </div>
  </section>

  <!-- VISION ＋ URAKATA TRACK RECORD -->
  <section class="pw-about-sec pw-about-vision" id="vision">
    <div class="wrap">
      <div class="headblock"><span class="eyebrow">Vision</span><h2 class="htitle">おうちで眠るスキルを、<br>循環させる社会に。</h2></div>
      <div class="pw-about-copy">
        <p>おうちで眠っているスキル・経験・才能を、それを必要としている人や企業へ届けられるように。preworksで学び、URAKATAで実践する。その仕組みを通じて、場所や時間にとらわれず、自分の力を存分に発揮できる人を増やしていきます。</p>
        <p>働く人と企業がつながり、自分らしい挑戦が自然と循環してくる。preworksとURAKATAは、一人ひとりの力が社会の中で循環する仕組みとコミュニティをつくっていきます。</p>
      </div>
      <div class="pw-about-panel pw-about-record">
        <div class="pw-about-record-body">
          <p class="pw-about-record-eyebrow">URAKATA TRACK RECORD</p>
          <h3 class="pw-about-record-title">オンライン秘書サービス「URAKATA」の実績</h3>
          <div class="pw-about-record-grid">
            <div><b>累計稼働ワーカー</b><br><span class="pw-about-num"><?php echo number_format( (int) pw( 'urakata.workers_total' ) ); ?></span>名</div>
            <div><b>導入企業</b><br><span class="pw-about-num"><?php echo number_format( (int) $pw_rec['value'] ); ?></span><?php echo esc_html( $pw_rec['unit'] . ( ! empty( $pw_rec['plus'] ) ? '以上' : '' ) ); ?></div>
          </div>
        </div>
        <img class="pw-about-photo" src="<?php echo esc_url( $pw_img . '/01-urakata-track-record.jpg' ); ?>" alt="" width="1672" height="941" loading="lazy" decoding="async">
      </div>
    </div>
  </section>

  <!-- MESSAGE -->
  <section class="pw-about-sec pw-about-message" id="message">
    <div class="wrap">
      <div class="headblock"><span class="eyebrow">Message</span><h2 class="htitle">preworksの想い</h2></div>
      <div class="pw-about-panel pw-about-msg">
        <div class="pw-about-msg-body">
          <p>オンライン秘書サービスを運営する中で、私たちはたくさんのママさんとお話ししてきました。</p>
          <p>その中で感じたのは、これまでの仕事や、家事・育児の中で培ってきた力を活かせる場所が、まだまだ少ないということ。</p>
          <p>オンライン秘書という働き方を通じて、自分らしく働く選択肢を広げたい。</p>
          <p>学んで終わりではなく、実際の仕事につながる環境を届けたい。</p>
          <p>そんな想いから、preworksをつくりました。</p>
          <p class="pw-about-closing">在宅ワークの0歩目を、<br>ひとりで悩まず、私たちと一緒に踏み出しませんか？</p>
        </div>
        <img class="pw-about-photo pw-about-msg-photo" src="<?php echo esc_url( $pw_img . '/02-preworks-message-photo.jpg' ); ?>" alt="" width="1448" height="1086" loading="lazy" decoding="async">
      </div>
    </div>
  </section>

  <!-- FREE SEMINAR（全ページ共通の末尾CTA） -->
  <?php pw_final_cta(); ?>
</main>
<?php get_footer(); ?>
