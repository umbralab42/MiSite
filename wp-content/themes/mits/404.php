<?php
/**
 * Сторінка 404.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
	<main id="Error_404">
		<div class="container">
			<div class="column one">
				<div class="mcb-column-inner">
					<div class="error_pic">
						<i class="icon-traffic-cone" aria-hidden="true"><?php echo file_get_contents( get_template_directory() . '/images/traffic-cone.svg' ); // phpcs:ignore ?></i>
					</div>
					<div class="error_desk">
						<h2><?php mits_te( '404.title' ); ?></h2>
						<h4><?php mits_te( '404.subtitle' ); ?></h4>
						<p><span class="check"><?php mits_te( '404.text' ); ?></span> <a class="button button_filled" href="<?php echo esc_url( mits_home_url() ); ?>"><?php mits_te( '404.button' ); ?></a></p>
					</div>
				</div>
			</div>
		</div>
	</main>
<?php
get_footer();
