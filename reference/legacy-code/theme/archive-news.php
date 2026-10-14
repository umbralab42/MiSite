<?php
/**
 * Archive template for the "news" post type.
 *
 * @package Betheme
 */

get_header();

$news_query = new WP_Query( array(
    'post_type'      => 'news',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );
?>

<div id="Content">
<div class="content_wrapper clearfix">
<main class="sections_group">
<div class="entry-content">

<section class="mits-section mits-news-archive">
  <div class="mits-container">
    <h1 class="mits-section__title"><?php mits_te( 'news.title' ); ?></h1>

    <?php if ( $news_query->have_posts() ) : ?>
    <div class="mits-news-grid">
      <?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
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
          <?php if ( has_excerpt() ) : ?>
            <p class="mits-news-card__excerpt"><?php the_excerpt(); ?></p>
          <?php endif; ?>
        </div>
      </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php else : ?>
      <p style="color:rgba(255,255,255,0.5);text-align:center;"><?php mits_te( 'news.empty' ); ?></p>
    <?php endif; ?>

  </div>
</section>

</div>
</main>
</div>
</div>

<?php get_footer(); ?>
