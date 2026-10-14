<?php
/**
 * Single template for the "news" post type.
 * Fields: image (thumbnail), title, body (editor), date (post date).
 *
 * @package Betheme
 */

get_header();
?>

<div id="Content">
<div class="content_wrapper clearfix">
<main class="sections_group">
<div class="entry-content">

<?php while ( have_posts() ) : the_post(); ?>

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
      <?php
        $mfn_builder = new Mfn_Builder_Front( get_the_ID() );
        $mfn_builder->show();
      ?>
    </div>

    <div class="mits-news-single__back">
      <a href="<?php echo esc_url( get_post_type_archive_link( 'news' ) ); ?>"
         class="mits-btn mits-btn--outline"><?php echo esc_html( mits_t( 'news.back' ) ); ?></a>
    </div>

  </div><!-- /.mits-container -->
</section>

<?php if ( comments_open() || get_comments_number() ) : ?>
  <div class="mits-container">
    <?php comments_template(); ?>
  </div>
<?php endif; ?>

<?php endwhile; ?>

</div>
</main>
</div>
</div>

<?php get_footer(); ?>
