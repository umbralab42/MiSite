<?php
/**
 * Template Name: МІТС — Голосування
 *
 * Сторінка голосування за освітню програму (поки що — повідомлення).
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();
mits_content_open();
$mits_content = get_post_field( 'post_content', get_queried_object_id() );
?>
<section class="mits-section mits-vote-page">
  <div class="mits-container">
    <h1 class="mits-vote-page__title">
      <?php echo esc_html( get_the_title() ? get_the_title() : mits_t( 'vote.title' ) ); ?>
    </h1>
    <?php // Порожні абзаци навколо тексту — як у затвердженому дизайні (там абзац був вкладений в абзац). ?>
    <p class="mits-vote-page__msg"></p>
      <?php echo $mits_content ? apply_filters( 'the_content', $mits_content ) : '<p>' . esc_html( mits_t( 'vote.unavailable' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <p></p>
  </div>
</section>
<?php
mits_content_close( 'div', true );
get_footer();
