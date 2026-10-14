<?php
/**
 * Template Name: МІТС — Вступ
 *
 * Сторінка «Вступ»: слайд, освітні рівні, відео, FAQ, новини, контакти.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mits_id    = get_queried_object_id();
$mits_video = get_post_meta( $mits_id, '_vstup_video_url', true );
$mits_levels = new WP_Query( array(
	'post_type'      => 'mits_edu_level',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	'no_found_rows'  => true,
) );
$mits_faq = new WP_Query( array(
	'post_type'      => 'mits_faq',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	'no_found_rows'  => true,
) );

mits_content_open();
?>
  <!-- ===================== HERO SLIDER ===================== -->
<?php mits_hero( (int) get_post_meta( $mits_id, '_vstup_hero_id', true ) ); ?>
  <!-- ===================== ОСВІТНІ РІВНІ ===================== -->
  <section class="mits-section mits-vstup-levels">
    <div class="mits-container">
      <h2 class="mits-section__title"><?php mits_te( 'vstup.levels' ); ?></h2>
<?php
$mits_content = '';
while ( have_posts() ) {
	the_post();
	$mits_content = get_the_content();
}
if ( trim( wp_strip_all_tags( $mits_content ) ) ) :
	?>
      <div class="mits-vstup-levels__desc">
	<?php echo apply_filters( 'the_content', $mits_content ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
<?php endif; ?>
      <div class="mits-vstup-levels__buttons">
<?php
while ( $mits_levels->have_posts() ) :
	$mits_levels->the_post();
	$mits_page = (int) get_post_meta( get_the_ID(), '_edu_level_page_id', true );
	$mits_url  = $mits_page ? get_permalink( mits_translated_id( $mits_page ) ) : mits_url( get_post_meta( get_the_ID(), '_edu_level_url', true ) );
	?>
        <a href="<?php echo esc_url( $mits_url ? $mits_url : '#' ); ?>" class="mits-btn mits-btn--level">
          <?php the_title(); ?>
        </a>
	<?php
endwhile;
wp_reset_postdata();
?>
      </div>
    </div>
  </section>
  <!-- ===================== ВІДЕО ===================== -->
<?php if ( $mits_video ) : ?>
  <section class="mits-section mits-vstup-video">
    <div class="mits-container">
      <div class="mits-vstup-video__wrap">
        <iframe
          src="<?php echo esc_url( $mits_video ); ?>"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="<?php echo esc_attr( get_the_title( $mits_id ) ); ?>"></iframe>
      </div>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== FAQ ===================== -->
<?php if ( $mits_faq->have_posts() ) : ?>
  <section class="mits-section mits-vstup-faq">
    <div class="mits-container">
      <h2 class="mits-section__title"><?php mits_te( 'vstup.faq' ); ?></h2>
      <div class="mits-advantages__accordion">
	<?php
	while ( $mits_faq->have_posts() ) {
		$mits_faq->the_post();
		mits_accordion_item( get_the_title(), get_post_meta( get_the_ID(), '_faq_answer', true ) );
	}
	wp_reset_postdata();
	?>
      </div>
      <div class="mits-vstup-faq__cta">
        <a href="#contact-form" class="mits-btn mits-btn--primary"><?php mits_te( 'nav.cta' ); ?></a>
      </div>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== НОВИНИ ===================== -->
<?php mits_news_section(); ?>
<?php
mits_content_close( 'div', true );
mits_contacts_section();
get_footer();
