<?php
/**
 * Головна сторінка.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mits_stats_defaults = array(
	1 => array( '800+', 'Наукових публікацій' ),
	2 => array( '15+', 'Років досвіду' ),
	3 => array( '150+', 'Студентів щорічно' ),
	4 => array( '600+', 'Випускників' ),
);
$mits_edu_title = (string) mits_get_option( 'mits_front_edu_title', '' );
$mits_edu_lines = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $mits_edu_title ) ) ) );
$mits_edu_text  = (string) mits_get_option( 'mits_front_edu_text', '' );

mits_content_open();
?>

<?php mits_hero(); ?>

<!-- ===================== ЧОМУ МИ? ===================== -->
<section class="mits-section mits-why">
  <div class="mits-container">
    <h2 class="mits-section__title">
      <?php echo esc_html( mits_get_field( 'mits_why', 'title', mits_t( 'front.why' ) ) ); ?>
    </h2>
    <div class="mits-why__text">
      <p><?php echo wp_kses( (string) mits_get_field( 'mits_why', 'text', '' ), mits_kses_inline() ); ?></p>
    </div>
    <div class="mits-stats">
<?php foreach ( $mits_stats_defaults as $mits_i => $mits_default ) : ?>
      <div class="mits-stat">
        <span class="mits-stat__number"><?php echo esc_html( mits_get_field( 'mits_why', 'stat' . $mits_i . '_num', $mits_default[0] ) ); ?></span>
        <span class="mits-stat__label"><?php echo esc_html( mits_get_field( 'mits_why', 'stat' . $mits_i . '_label', $mits_default[1] ) ); ?></span>
      </div>
<?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== ПЕРЕВАГИ ===================== -->
<?php
$mits_adv = new WP_Query( array(
	'post_type'      => 'mits_advantage',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	'no_found_rows'  => true,
) );
?>
<section class="mits-section mits-advantages">
  <div class="mits-container">
    <h2 class="mits-section__title"><?php echo esc_html( mits_get_field( 'mits_advantages', 'title', mits_t( 'front.advantages' ) ) ); ?></h2>
    <div class="mits-advantages__accordion">
<?php
while ( $mits_adv->have_posts() ) {
	$mits_adv->the_post();
	mits_accordion_item( get_the_title(), get_post_meta( get_the_ID(), '_adv_details', true ) );
}
wp_reset_postdata();
?>
    </div>
  </div>
</section>

<!-- ===================== IT НАВЧАННЯ ===================== -->
<?php if ( $mits_edu_lines || $mits_edu_text ) : ?>
<section class="mits-section mits-education">
  <div class="mits-container">
    <h2 class="mits-education__title">
	<?php
	$mits_classes = array( 'grad-orange', 'grad-cyan' );
	foreach ( $mits_edu_lines as $mits_i => $mits_line ) {
		if ( $mits_i > 0 ) {
			echo "<br>\n      ";
		} else {
			echo '  ';
		}
		printf( '<span class="%1$s">%2$s</span>', esc_attr( $mits_classes[ min( $mits_i, 1 ) ] ), esc_html( $mits_line ) );
	}
	echo "\n";
	?>
    </h2>
    <div class="mits-education__card">
      <?php echo wp_kses_post( $mits_edu_text ); ?>
    </div>
    <div class="mits-education__cta">
      <a href="#contact-form" class="mits-btn mits-btn--primary"><?php mits_te( 'nav.cta' ); ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== ДІЯЛЬНІСТЬ ===================== -->
<section class="mits-section mits-activities">
  <div class="mits-container">
    <h2 class="mits-section__title"><?php echo esc_html( mits_get_field( 'mits_activities', 'title', mits_t( 'front.activities' ) ) ); ?></h2>
    <div class="mits-cards">
<?php mits_activity_cards(); ?>
    </div>
<?php mits_activity_cta(); ?>
  </div>
</section>

<?php mits_news_section( true ); ?>

<?php mits_content_close( 'div', true ); ?>

<?php mits_contacts_section( 'mits-contacts' ); ?>

<?php
get_footer();
