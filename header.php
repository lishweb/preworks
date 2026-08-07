<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?php echo PREWORKS_IMG; ?>/logo.png">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'grain' ); ?>>
<?php wp_body_open(); ?>
<?php $preworks_nav_base = preworks_is_lp() ? '' : home_url('/'); // LPではページ内アンカーにして離脱を防ぐ ?>
<header class="hdr" id="hdr">
  <a class="brand" href="<?php echo preworks_is_lp() ? '#hero' : home_url('/'); ?>">preworks</a>
  <nav>
    <a href="<?php echo $preworks_nav_base; ?>#about">preworksについて</a>
    <a href="<?php echo $preworks_nav_base; ?>#program">プログラム</a>
    <a href="<?php echo $preworks_nav_base; ?>#voice">修了生の声</a>
    <a href="<?php echo $preworks_nav_base; ?>#mentor">メンター紹介</a>
    <a href="<?php echo $preworks_nav_base; ?>#faq">よくある質問</a>
  </nav>
  <div class="nav-cta">
    <a class="btn btn-line" href="<?php echo esc_url( preworks_line_url() ); ?>" target="_blank" rel="noopener"><img src="<?php echo PREWORKS_IMG; ?>/LINE_Brand_icon.png" alt="LINE" class="liico">無料オンライン相談</a>
  </div>
  <button class="burger" id="burger" aria-label="メニュー"><span></span><span></span><span></span></button>
</header>

