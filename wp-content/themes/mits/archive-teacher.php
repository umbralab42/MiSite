<?php
/**
 * Архів викладачів.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();
mits_content_open( 'main', '' );
?>
<section class="mits-section mits-teachers-archive">
  <div class="mits-container">
    <h1 class="mits-section__title"><?php mits_te( 'teachers.title' ); ?></h1>
<?php if ( have_posts() ) : ?>
    <div class="mits-teachers-grid">
	<?php
	while ( have_posts() ) {
		the_post();
		mits_teacher_card();
	}
	?>
    </div>
<?php else : ?>
      <p style="color:rgba(255,255,255,0.5);text-align:center;"><?php mits_te( 'teachers.empty' ); ?></p>
<?php endif; ?>
  </div>
</section>
<?php
mits_content_close( 'main' );
get_footer();
