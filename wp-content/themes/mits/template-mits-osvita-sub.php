<?php
/**
 * Template Name: МІТС — Освіта, підсторінка
 *
 * Загальні положення, Бакалавр, Магістратура, Аспірантура в розділі «Освіта».
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mits_id = get_queried_object_id();

mits_content_open();
?>
  <!-- ===================== HERO SLIDER ===================== -->
<?php mits_hero( (int) get_post_meta( $mits_id, '_osvita_sub_hero_id', true ) ); ?>
  <!-- ===================== ЗМІСТ СТОРІНКИ ===================== -->
<?php
$mits_content = '';
if ( have_posts() ) {
	the_post();
	$mits_content = get_the_content();
}
if ( trim( wp_strip_all_tags( $mits_content ) ) ) :
	?>
  <section class="mits-section mits-osvita-sub-content">
    <div class="mits-container">
      <div class="mits-vstup-sub-content__card">
        <div class="mits-vstup-sub-content__body">
	<?php echo apply_filters( 'the_content', $mits_content ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>
<?php
mits_content_close( 'div', true );
get_footer();
