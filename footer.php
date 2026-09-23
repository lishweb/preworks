<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<footer class="ftr">
  <div class="wrap">
    <div class="fgrid">
      <div>
        <div class="brand">preworks</div>
      </div>
      <div>
        <p class="ftr-h">Navigation</p>
        <?php
          $preworks_nav_base = preworks_is_lp() ? '' : home_url('/'); // LPではページ内アンカーにして離脱を防ぐ
          // 修了生の声は専用ページ（/voice/）を持つ。ただしLPは離脱を防ぐためページ内アンカーのまま。v1.0.12
          $preworks_voice_url = get_post_type_archive_link( 'voice' );
          if ( ! $preworks_voice_url ) { $preworks_voice_url = home_url( '/voice/' ); }
          $preworks_voice_href = ( ! preworks_is_lp() && preworks_has_cpt_posts( 'voice' ) )
        ? $preworks_voice_url                      // 記事あり → 専用ページ
        : $preworks_nav_base . '#voice';            // 0件・LP → 従来のアンカー
        // メンター紹介も専用ページ（/mentor/）を持つ。LPは離脱を防ぐためアンカーのまま。v1.0.13
        $preworks_mentor_url = get_post_type_archive_link( 'mentor' );
        if ( ! $preworks_mentor_url ) { $preworks_mentor_url = home_url( '/mentor/' ); }
        $preworks_mentor_href = ( ! preworks_is_lp() && preworks_has_cpt_posts( 'mentor' ) )
        ? $preworks_mentor_url                      // 記事あり → 専用ページ
        : $preworks_nav_base . '#mentor';            // 0件・LP → 従来のアンカー
        ?>
        <ul>
          <li><a href="<?php echo $preworks_nav_base; ?>#about">preworksについて</a></li>
          <li><a href="<?php echo $preworks_nav_base; ?>#program">プログラム</a></li>
          <li><a href="<?php echo esc_url( $preworks_voice_href ); ?>">修了生の声</a></li>
          <li><a href="<?php echo esc_url( $preworks_mentor_href ); ?>">メンター紹介</a></li>
          <li><a href="<?php echo home_url('/faq/'); ?>">よくある質問</a></li>
          <li><a href="<?php echo home_url('/contact/'); ?>">お問い合わせ</a></li>
        </ul>
      </div>
      <div>
        <p class="ftr-h">SNS / Contact</p>
        <a class="btn btn-line" href="<?php echo esc_url( preworks_line_url() ); ?>" target="_blank" rel="noopener" style="margin-bottom:16px;"><img src="<?php echo PREWORKS_IMG; ?>/LINE_Brand_icon.png" alt="LINE" class="liico">無料オンライン相談はこちら</a>
        <div class="sns">
          <a href="https://instagram.com/zaitaku_jp" target="_blank" rel="noopener"><svg class="sni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>Instagram（@zaitaku_jp）</a>
          <a href="https://www.threads.net/@zaitaku_jp" target="_blank" rel="noopener"><svg class="sni" viewBox="0 0 24 24" fill="currentColor"><path d="M17 11.2c-.1 0-.2-.1-.3-.1-.2-2.9-1.8-4.6-4.4-4.6-1.6 0-2.9.7-3.7 1.9l1.5 1c.5-.8 1.4-1 2.2-1 1.4 0 2.2.9 2.4 2.2-.6-.1-1.2-.1-1.8-.1-2 .1-3.4 1.3-3.3 2.9.1 1.4 1.3 2.4 2.9 2.4 1.4 0 2.8-.7 3.3-2.5.3.6.8 1 1.3 1.3-.5 1.8-1.9 3.3-4.6 3.3-3.2 0-5.3-2.1-5.3-6s2.1-6 5.3-6c2.4 0 4.2 1.2 4.9 3.4l1.5-.5C18.8 5.6 16.5 4 13.2 4 8.9 4 6 6.9 6 11.7s2.9 7.7 7.2 7.7c3.7 0 5.8-2.3 6.4-4.7l-1.6-.4zM12.6 15c-.8 0-1.3-.4-1.4-.9-.1-.7.5-1.1 1.5-1.2.4 0 .7 0 1.1.1-.2 1.4-.9 2-2.2 2z"/></svg>Threads（@zaitaku_jp）</a>
          <a href="https://www.youtube.com/@%E5%9C%A8%E5%AE%85%E3%83%AF%E3%83%BC%E3%82%AF%E7%A0%94%E7%A9%B6%E6%89%80/featured" target="_blank" rel="noopener"><svg class="sni" viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.4-.4-5c-.2-.9-.9-1.6-1.8-1.8C19 4.7 12 4.7 12 4.7s-7 0-8.8.5c-.9.2-1.6.9-1.8 1.8C1 8.6 1 12 1 12s0 3.4.4 5c.2.9.9 1.6 1.8 1.8 1.8.5 8.8.5 8.8.5s7 0 8.8-.5c.9-.2 1.6-.9 1.8-1.8.4-1.6.4-5 .4-5zM9.8 15.3V8.7l5.6 3.3-5.6 3.3z"/></svg>YouTube（在宅ワーク研究所）</a>
          <a href="https://open.spotify.com/show/53lNyb4OrbjhlWAqdLXMGO" target="_blank" rel="noopener"><svg class="sni" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm4.6 14.4c-.2.3-.5.4-.8.2-2.2-1.3-4.9-1.6-8.2-.9-.3.1-.6-.1-.7-.4-.1-.3.1-.6.4-.7 3.6-.8 6.7-.4 9.1 1 .3.2.4.5.2.8zm1.2-2.7c-.2.4-.6.5-1 .3-2.5-1.5-6.3-2-9.2-1.1-.4.1-.8-.1-.9-.5-.1-.4.1-.8.5-.9 3.4-1 7.6-.5 10.4 1.3.3.2.4.6.2 1zm.1-2.8C15 9.2 9.9 9 7 9.9c-.5.1-1-.1-1.1-.6-.1-.5.1-1 .6-1.1 3.4-1 9-.8 12.5 1.3.4.3.6.8.3 1.2-.2.4-.7.6-1.1.3z"/></svg>ポッドキャスト（Spotify）</a>
        </div>
      </div>
    </div>
    <div class="copy">
      <span>© 2026 LISH株式会社</span>
      <span><a href="<?php echo home_url('/privacy/'); ?>">プライバシーポリシー</a>｜<a href="<?php echo home_url('/legal/'); ?>">特定商取引法に基づく表記</a>｜<a href="<?php echo home_url('/terms/'); ?>">利用規約</a>｜<a href="<?php echo home_url('/company/'); ?>">会社概要</a></span>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
