<?php
/**
 * Template Name: MITS — Голосування
 *
 * Сторінка голосування за освітню програму.
 *
 * @package Betheme
 */

get_header();
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

<section class="mits-section mits-vote-page">
  <div class="mits-container">
    <h1 class="mits-vote-page__title">
      <?php echo esc_html( get_the_title() ?: ( function_exists( 'pll_current_language' ) && pll_current_language() === 'en' ? 'Voting' : 'Голосування' ) ); ?>
    </h1>
    <p class="mits-vote-page__msg">
      <?php
      $content = get_post_field( 'post_content', get_queried_object_id() );
      echo $content ? wp_kses_post( apply_filters( 'the_content', $content ) ) : esc_html( 'Голосування тимчасово недоступне.' );
      ?>
    </p>
  </div>
</section>

</div><!-- #mits-front -->
</div><!-- .entry-content -->
</div><!-- .sections_group -->
</div><!-- .content_wrapper -->
</div><!-- #Content -->

<?php get_footer(); ?>
