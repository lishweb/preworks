<?php
/**
 * G00 オンライン秘書ガイド（目次）  /guide/
 * コラムのうち「ガイドに掲載」にチェックした記事を、セクションごとに並べる。
 * セクションは inc/pw-guide.php の pw_guide_sections()、並びは各記事の「並び順」。
 */
if ( ! defined( 'ABSPATH' ) ) exit;
pw_page_start( array( 'layout' => 'wide' ) );
$groups = pw_guides_by_section();
?>

<?php if ( ! $groups ) : ?>
  <p class="center">ガイドは準備中です。公開までしばらくお待ちください。</p>
<?php else : ?>
  <ul class="pw-kana" aria-label="セクション">
  <?php foreach ( pw_guide_sections() as $key => $sec ) : if ( empty( $groups[ $key ] ) ) continue; ?>
    <li><a href="#<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $sec['name'] ); ?></a></li>
  <?php endforeach; ?>
  </ul>
  <?php foreach ( pw_guide_sections() as $key => $sec ) : if ( empty( $groups[ $key ] ) ) continue; ?>
  <div class="pw-guide-sec">
    <h2 id="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $sec['name'] ); ?></h2>
    <p class="pw-guide-sec-d"><?php echo esc_html( $sec['desc'] ); ?></p>
    <div class="pw-cards pw-cols-3">
    <?php foreach ( $groups[ $key ] as $g ) :
        $lead = get_post_meta( $g->ID, '_pw_bloglead', true );
        $lead = ( $lead && function_exists( 'preworks_plain_text_from_easy' ) ) ? preworks_plain_text_from_easy( $lead, 70 ) : wp_html_excerpt( wp_strip_all_tags( get_the_excerpt( $g ) ), 70, '…' );
    ?>
      <a class="pw-card is-link" href="<?php echo esc_url( get_permalink( $g ) ); ?>">
        <p class="pw-card-t"><?php echo esc_html( get_the_title( $g ) ); ?></p>
        <?php if ( $lead ) : ?><p class="pw-card-d"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
        <span class="pw-card-more">読む</span>
      </a>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php pw_cta( array(
    'h' => 'preworksで、オンライン秘書として働く準備をはじめませんか？',
    'p' => 'ガイドを読んで気になったことは、無料オンライン相談でお気軽にご相談ください。',
) ); ?>
<?php pw_related( array( 'program', 'price', 'career', 'consultation' ), 'preworksの講座について' ); ?>

<?php pw_page_end(); ?>
