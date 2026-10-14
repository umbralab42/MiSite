<?php
/**
 * Звичайна сторінка: заголовок і вміст (Про кафедру, Загальний опис, Голосування…).
 *
 * Старі сторінки «Бакалавр», «Спеціаліст», «Магістр» розділу «Вступ» (позначка
 * _mits_level_page) виводяться як підсторінки вступу.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( mits_is_level_page( get_queried_object_id() ) ) {
	get_template_part( 'partials/vstup-level' );
	get_footer();
	return;
}

while ( have_posts() ) :
	the_post();
	$mits_has_content = '' !== trim( get_the_content() );
	?>
<div id="Content" role="main">
	<div class="content_wrapper clearfix">
		<main class="sections_group">
			<div class="entry-content" itemprop="mainContentOfPage">
				<h1 class="mits-page-title"><?php the_title(); ?></h1>
				<div data-id="<?php the_ID(); ?>" class="mfn-builder-content mfn-default-content-buider"></div><section class="section mcb-section the_content <?php echo $mits_has_content ? 'has_content' : 'no_content'; ?>"><div class="section_wrapper"><div class="the_content_wrapper "><?php the_content(); ?></div></div></section>
				<section class="section section-page-footer">
					<div class="section_wrapper clearfix">
						<div class="column one page-pager">
							<div class="mcb-column-inner">
								<?php wp_link_pages(); ?>
							</div>
						</div>
					</div>
				</section>
			</div>
		</main>
	</div>
</div>
	<?php
endwhile;

get_footer();
