<?php
/**
 * Template Name: MITS — Сторінка
 *
 * Page template with a centered Inter 700 title and dark theme.
 *
 * @package Betheme
 */

get_header();
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content mits-page">

  <h1 class="mits-page__title"><?php the_title(); ?></h1>

  <?php
    while ( have_posts() ) {
      the_post();
      the_content();
    }
  ?>

</div>
</div>
</div>
</div>

<?php get_footer(); ?>
