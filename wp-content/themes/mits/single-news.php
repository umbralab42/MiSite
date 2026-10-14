<?php
/**
 * Новина: зображення, дата, заголовок, текст, посилання на всі новини, коментарі.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();
mits_content_open( 'main', '' );

while ( have_posts() ) :
	the_post();
	$mits_has_content = '' !== trim( get_the_content() );
	?>
<section class="mits-section mits-news-single">
  <div class="mits-container">
	<?php if ( has_post_thumbnail() ) : ?>
      <div class="mits-news-single__image">
		<?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
      </div>
	<?php endif; ?>
    <div class="mits-news-single__meta">
      <time class="mits-news-single__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
        <?php echo esc_html( get_the_date() ); ?>
      </time>
    </div>
    <h1 class="mits-news-single__title"><?php the_title(); ?></h1>
    <div class="mits-news-single__body">
      <div data-id="<?php the_ID(); ?>" class="mfn-builder-content mfn-default-content-buider"></div><section class="section mcb-section the_content <?php echo $mits_has_content ? 'has_content' : 'no_content'; ?>"><div class="section_wrapper"><div class="the_content_wrapper "><?php the_content(); ?></div></div></section>
    </div>
    <div class="mits-news-single__back">
      <a href="<?php echo esc_url( (string) get_post_type_archive_link( 'news' ) ); ?>"
         class="mits-btn mits-btn--outline"><?php mits_te( 'news.back' ); ?></a>
    </div>
  </div><!-- /.mits-container -->
</section>
	<?php if ( comments_open() || get_comments_number() ) : ?>
  <div class="mits-container">
		<?php comments_template(); ?>
  </div>
	<?php endif; ?>
	<?php
endwhile;

mits_content_close( 'main' );
get_footer();
