<?php get_header(); ?>
<main>

  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ お問い合わせ</div>
    <span class="eyebrow">Contact</span><h1>お問い合わせ</h1>
  </div></section>

  <section class="about sec-pad"><div class="wrap">
    <p class="contact-lead">こちらは法人・メディア関連専用のお問い合わせフォームです。<br>preworksの受講を検討頂いている方は、<br><a href="<?php echo esc_url( preworks_line_url() ); ?>" target="_blank" rel="noopener">公式LINE</a>からご連絡をお願いいたします。</p>
    <?php echo preworks_contact_form(); ?>
  </div></section>

</main>
<?php get_footer(); ?>
