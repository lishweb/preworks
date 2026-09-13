<?php
/**
 * 広告用LP② Google P-MAX広告（/lp002/）
 * TOPページ複製。本文は template-parts/lp-body.php を lp001 と共用。
 * LINEボタンの遷移先・計測スクリプトは functions.php（preworks_line_url / preworks_lp_tracking_script）で制御。
 */
get_header();
get_template_part( 'template-parts/lp-body' );
get_footer();
