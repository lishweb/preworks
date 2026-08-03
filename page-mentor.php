<?php get_header(); ?>
<main>

  <section class="page-hero"><span class="orb m soft" style="top:0;right:-60px;"></span><div class="wrap">
    <div class="bc"><a href="<?php echo home_url('/'); ?>">トップ</a> ／ お問い合わせ</div>
    <span class="eyebrow">Contact</span><h1>お問い合わせ</h1><p class="sub">受講のご相談はもちろん、メディア・インタビューのご依頼も受け付けています。</p>
  </div></section>
  <section class="about sec-pad"><div class="wrap">
    <div class="center" style="margin-bottom:40px;"><a class="btn btn-line" href="https://sub.online-assistant.biz/line/open/2sRcbvLGNaq8?mtid=BxzwhkkjSRAt" target="_blank" rel="noopener" style="font-size:16px;padding:18px 40px;"><img src="<?php echo PREWORKS_IMG; ?>/LINE_Brand_icon.png" alt="LINE" class="liico">いちばん早いのはLINE相談（5分）</a></div>
    <form class="cform reveal" onsubmit="alert('デモのため送信されません。公開時にフォーム送信先を設定してください。');return false;">
      <div class="row"><label>お名前<span class="req">必須</span></label><input type="text" name="name" required></div>
      <div class="row"><label>メールアドレス<span class="req">必須</span></label><input type="email" name="email" required></div>
      <div class="row"><label>電話番号（任意）</label><input type="tel" name="tel"></div>
      <div class="row"><label>お問い合わせ内容<span class="req">必須</span></label><textarea name="message" required></textarea></div>
      <div class="row consent"><input type="checkbox" id="consent" required><label for="consent" style="font-weight:400;"><a href="<?php echo home_url('/privacy/'); ?>" style="color:var(--terra);">プライバシーポリシー</a>に同意します</label></div>
      <div class="submit"><button type="submit" class="btn btn-line" style="border:0;cursor:pointer;">この内容で送信する</button></div>
    </form>
    <p class="center" style="color:var(--muted);margin-top:24px;font-size:clamp(14px,1.8vw,16px);">※このフォームはデモ表示です。公開時に Googleフォーム / Tally / Formspree 等の送信先を設定してください。</p>
  </div></section>
</main>
<?php get_footer(); ?>
