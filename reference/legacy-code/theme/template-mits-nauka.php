<?php
/**
 * Template Name: MITS — Наука
 *
 * Головна сторінка розділу «Наука».
 *
 * @package Betheme
 */

get_header();

$is_en = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';

// ── Options ──────────────────────────────────────────────────────
$hero_id = (int) get_option( 'mits_nauka_hero_id', 0 );
$photo   = get_option( 'mits_nauka_photo', '' );
$text    = get_option( 'mits_nauka_text',  '' );
$links   = json_decode( get_option( 'mits_nauka_links' ) ?: '[]', true );
$stats   = json_decode( get_option( 'mits_nauka_stats' ) ?: '[]', true );
if ( ! is_array( $links ) )   $links   = [];
if ( ! is_array( $stats ) )   $stats   = [];

// ── Contacts page ────────────────────────────────────────────────
$contacts_id = 24;
if ( function_exists( 'pll_get_post' ) ) {
	$translated = pll_get_post( $contacts_id );
	if ( $translated ) $contacts_id = $translated;
}
$contacts_page = get_post( $contacts_id );
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

  <!-- ===================== HERO SLIDER ===================== -->
  <?php if ( function_exists( 'mits_hero_render' ) ) mits_hero_render( '#contact-form', '', $hero_id ); ?>

  <!-- ===================== ПРО ВІДДІЛ: фото + текст ===================== -->
  <?php if ( $photo || $text ) : ?>
  <section class="mits-section mits-intl-about">
    <div class="mits-container">
      <div class="mits-intl-about__inner<?php echo ( $photo && $text ) ? ' mits-intl-about__inner--split' : ''; ?>">
        <?php if ( $photo ) : ?>
        <div class="mits-intl-about__photo">
          <img src="<?php echo esc_url( $photo ); ?>" alt="" class="mits-intl-about__img">
        </div>
        <?php endif; ?>
        <?php if ( $text || ! empty( $links ) ) : ?>
        <div class="mits-intl-about__text">
          <?php if ( $text ) : ?>
          <?php echo wp_kses_post( $text ); ?>
          <?php endif; ?>
          <?php if ( ! empty( $links ) ) : ?>
          <ul class="mits-intl-about__links">
            <?php foreach ( $links as $link ) :
              $lname = esc_html( $link['name'] ?? '' );
              $lurl  = esc_url( $link['url'] ?? '' );
              if ( ! $lname && ! $lurl ) continue;
            ?>
            <li>
              <?php if ( $lurl ) : ?>
              <a href="<?php echo $lurl; ?>" target="_blank" rel="noopener noreferrer">
                <?php echo $lname ?: $lurl; ?>
              </a>
              <?php else : ?>
              <span><?php echo $lname; ?></span>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== СТАТИСТИКА ===================== -->
  <?php
  $has_stats = ! empty( array_filter( $stats, fn( $item ) => empty( $item['divider'] ) && ( trim( $item['label'] ?? '' ) !== '' || trim( $item['value'] ?? '' ) !== '' ) ) );
  if ( $has_stats ) :
    $in_row    = false;
    $row_count = 0;
  ?>
  <section class="mits-section mits-intl-stats">
    <div class="mits-container">
      <?php foreach ( $stats as $item ) :
        if ( ! empty( $item['divider'] ) ) :
          if ( $in_row ) { echo '</div>'; $in_row = false; $row_count = 0; }
          ?><div class="mits-intl-stats__divider"></div><?php
        else :
          $val = trim( $item['value'] ?? '' );
          $lbl = trim( $item['label'] ?? '' );
          if ( $val === '' && $lbl === '' ) { continue; }
          if ( ! $in_row || $row_count >= 4 ) {
            if ( $in_row ) echo '</div>';
            echo '<div class="mits-intl-stats__row">';
            $in_row    = true;
            $row_count = 0;
          }
          $row_count++;
          ?>
          <div class="mits-intl-stat">
            <span class="mits-intl-stat__value"><?php echo esc_html( $val ); ?></span>
            <span class="mits-intl-stat__label"><?php echo esc_html( $lbl ); ?></span>
          </div>
          <?php
        endif;
      endforeach;
      if ( $in_row ) echo '</div>';
      ?>
    </div>
  </section>
  <?php endif; ?>

</div><!-- #mits-front -->
</div><!-- .entry-content -->
</div><!-- .sections_group -->
</div><!-- .content_wrapper -->
</div><!-- #Content -->

<!-- ===================== КОНТАКТИ ===================== -->
<?php if ( $contacts_page ) : ?>
<section class="mits-section mits-contacts" id="contact-form">
  <div class="mits-container">
    <h2 class="mits-page__title"><?php echo esc_html( $contacts_page->post_title ); ?></h2>
    <?php echo apply_filters( 'the_content', $contacts_page->post_content ); ?>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
