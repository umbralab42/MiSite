<?php
/**
 * Сторінка викладача.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();
mits_content_open( 'main', '' );

while ( have_posts() ) :
	the_post();
	$mits_id          = get_the_ID();
	$mits_position    = get_post_meta( $mits_id, '_teacher_position', true );
	$mits_grade       = get_post_meta( $mits_id, '_teacher_grade', true );
	$mits_description = get_post_meta( $mits_id, '_teacher_description', true );
	$mits_photo_id    = (int) get_post_meta( $mits_id, '_teacher_photo_id', true );
	$mits_photo       = $mits_photo_id ? wp_get_attachment_image_url( $mits_photo_id, 'large' ) : '';
	$mits_subjects    = array_filter( mits_get_meta_list( $mits_id, '_teacher_subjects' ), 'strlen' );
	?>
<section class="mits-section mits-teacher-single">
  <div class="mits-container">
    <!-- Header row: photo + main info -->
    <div class="mits-teacher-single__header">
	<?php if ( $mits_photo ) : ?>
        <div class="mits-teacher-single__photo">
          <img src="<?php echo esc_url( $mits_photo ); ?>"
               alt="<?php echo esc_attr( get_the_title() ); ?>">
        </div>
	<?php endif; ?>
      <div class="mits-teacher-single__info">
        <h1 class="mits-teacher-single__name"><?php the_title(); ?></h1>
	<?php if ( $mits_position ) : ?>
          <p class="mits-teacher-single__position"><?php echo esc_html( $mits_position ); ?></p>
	<?php endif; ?>
	<?php if ( $mits_grade ) : ?>
          <p class="mits-teacher-single__grade"><?php echo esc_html( $mits_grade ); ?></p>
	<?php endif; ?>
	<?php if ( $mits_subjects ) : ?>
          <div class="mits-teacher-single__subjects">
            <h3><?php mits_te( 'teachers.subjects' ); ?></h3>
            <ul>
		<?php foreach ( $mits_subjects as $mits_subject ) : ?>
              <li><?php echo esc_html( $mits_subject ); ?></li>
		<?php endforeach; ?>
            </ul>
          </div>
	<?php endif; ?>
      </div>
    </div><!-- /.header -->
	<?php if ( $mits_description ) : ?>
    <div class="mits-teacher-single__description">
      <h2><?php mits_te( 'teachers.about' ); ?></h2>
      <div class="mits-teacher-single__description-text">
		<?php echo wp_kses_post( wpautop( $mits_description ) ); ?>
      </div>
    </div>
	<?php endif; ?>
    <div class="mits-teacher-single__back">
      <a href="<?php echo esc_url( (string) get_post_type_archive_link( 'teacher' ) ); ?>"
         class="mits-btn mits-btn--outline"><?php echo esc_html( mits_t( 'teachers.back' ) ); ?></a>
    </div>
  </div><!-- /.mits-container -->
</section>
	<?php
endwhile;

mits_content_close( 'main' );
get_footer();
