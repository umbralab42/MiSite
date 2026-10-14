<?php
/**
 * Template Name: МІТС — Сторінка з заголовком
 *
 * Простий шаблон: заголовок по центру і вміст сторінки (сторінка «Контакти»).
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();
mits_content_open( 'div', ' class="mits-page"' );
?>
<h1 class="mits-page__title"><?php the_title(); ?></h1>
<?php
while ( have_posts() ) {
	the_post();
	the_content();
}
mits_content_close();
get_footer();
