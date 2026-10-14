<?php
/**
 * Template Name: МІТС — Кафедра
 *
 * Сторінка «Кафедра»: слайд, опис, траєкторії, картки «Навчання», двоє викладачів.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mits_id       = get_queried_object_id();
$mits_logo_id  = get_theme_mod( 'custom_logo' );
$mits_logo_src = $mits_logo_id ? wp_get_attachment_image_url( $mits_logo_id, 'full' ) : '';
$mits_specs    = array();
for ( $mits_i = 1; $mits_i <= 3; $mits_i++ ) {
	$mits_spec = trim( (string) get_post_meta( $mits_id, '_kafedra_spec_' . $mits_i, true ) );
	if ( '' !== $mits_spec ) {
		$mits_specs[] = $mits_spec;
	}
}
$mits_history_url  = get_post_meta( $mits_id, '_kafedra_history_url', true );
$mits_history_url  = $mits_history_url ? mits_url( $mits_history_url ) : mits_page_link_by_path( 'кафедра/про-кафедру' );
$mits_history_text = get_post_meta( $mits_id, '_kafedra_history_text', true );
$mits_history_text = $mits_history_text ? $mits_history_text : mits_t( 'kafedra.history' );

mits_content_open();
?>
  <!-- ===================== HERO SLIDER ===================== -->
<?php mits_hero( (int) get_post_meta( $mits_id, '_kafedra_hero_id', true ) ); ?>
  <!-- ===================== HERO ===================== -->
  <section class="mits-section mits-kafedra-hero">
    <div class="mits-container">
<?php if ( $mits_logo_src ) : ?>
      <div class="mits-kafedra-hero__logo">
        <img src="<?php echo esc_url( $mits_logo_src ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="120" height="120">
      </div>
<?php endif; ?>
      <h1 class="mits-kafedra-hero__title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
    </div>
  </section>
  <!-- ===================== ОПИС ===================== -->
  <section class="mits-section mits-kafedra-about">
    <div class="mits-container">
      <div class="mits-kafedra-about__box">
<?php
while ( have_posts() ) {
	the_post();
	the_content();
}
?>
      </div>
<?php if ( $mits_history_url ) : ?>
      <div class="mits-kafedra-about__link">
        <a href="<?php echo esc_url( $mits_history_url ); ?>"><?php echo esc_html( $mits_history_text ); ?></a>
      </div>
<?php endif; ?>
    </div>
  </section>
  <!-- ===================== СПЕЦІАЛІЗАЦІЇ ===================== -->
<?php if ( $mits_specs ) : ?>
  <section class="mits-kafedra-specs">
    <div class="mits-container">
      <h2 class="mits-kafedra-specs__subtitle"><?php mits_te( 'kafedra.tracks' ); ?></h2>
      <div class="mits-kafedra-specs__grid">
	<?php foreach ( $mits_specs as $mits_spec ) : ?>
        <div class="mits-kafedra-spec-tile">
          <p><?php echo esc_html( $mits_spec ); ?></p>
        </div>
	<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== НАВЧАННЯ ===================== -->
  <section class="mits-section mits-kafedra-nav-title">
    <div class="mits-container">
      <h2 class="mits-kafedra-section-heading"><?php mits_te( 'kafedra.education' ); ?></h2>
    </div>
  </section>
  <!-- ===================== ДІЯЛЬНІСТЬ ===================== -->
  <section class="mits-section mits-activities mits-kafedra-activities">
    <div class="mits-container">
      <div class="mits-cards">
<?php mits_activity_cards( 2 ); ?>
      </div>
<?php mits_activity_cta(); ?>
    </div>
  </section>
  <!-- ===================== ВИКЛАДАЧІ ===================== -->
<?php
$mits_teachers = new WP_Query( array(
	'post_type'      => 'teacher',
	'post_status'    => 'publish',
	'posts_per_page' => 2,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	'no_found_rows'  => true,
) );
if ( $mits_teachers->have_posts() ) :
	?>
  <section class="mits-section mits-kafedra-teachers">
    <div class="mits-container">
      <h2 class="mits-kafedra-section-heading"><?php mits_te( 'kafedra.teachers' ); ?></h2>
      <div class="mits-teachers-grid mits-kafedra-teachers__grid">
	<?php
	while ( $mits_teachers->have_posts() ) {
		$mits_teachers->the_post();
		mits_teacher_card();
	}
	wp_reset_postdata();
	?>
      </div>
      <div class="mits-kafedra-teachers__all">
        <a href="<?php echo esc_url( (string) get_post_type_archive_link( 'teacher' ) ); ?>" class="mits-btn mits-btn--outline">
          <?php mits_te( 'kafedra.all_teachers' ); ?> →
        </a>
      </div>
    </div>
  </section>
<?php endif; ?>
<?php
mits_content_close( 'div', true );
get_footer();
