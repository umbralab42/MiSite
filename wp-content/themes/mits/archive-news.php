<?php
/**
 * Архів новин.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();
mits_content_open( 'main', '' );
?>
<section class="mits-section mits-news-archive">
  <div class="mits-container">
    <h1 class="mits-section__title"><?php mits_te( 'news.title' ); ?></h1>
<?php if ( have_posts() ) : ?>
    <div class="mits-news-grid">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
      <a class="mits-news-card" href="<?php the_permalink(); ?>">
        <div class="mits-news-card__image-wrap">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium', array( 'class' => 'mits-news-card__image', 'alt' => get_the_title() ) ); ?>
		<?php else : ?>
            <div class="mits-news-card__image mits-news-card__image--placeholder"></div>
		<?php endif; ?>
        </div>
        <div class="mits-news-card__info">
          <time class="mits-news-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
            <?php echo esc_html( get_the_date() ); ?>
          </time>
          <p class="mits-news-card__title"><?php the_title(); ?></p>
        </div>
      </a>
	<?php endwhile; ?>
    </div>
	<?php
	the_posts_pagination( array(
		'class'     => 'mits-pagination',
		'prev_text' => '←',
		'next_text' => '→',
	) );
	?>
<?php else : ?>
      <p style="color:rgba(255,255,255,0.5);text-align:center;"><?php mits_te( 'news.empty' ); ?></p>
<?php endif; ?>
  </div>
</section>
<?php
mits_content_close( 'main' );
get_footer();
