<?php
/**
 * Template Name: MITS — Освіта підрозділ
 *
 * Шаблон підсторінок розділу «Освіта»:
 * Загальні положення, Бакалавр, Магістратура, Аспірантура.
 *
 * @package Betheme
 */

get_header();

$post_id  = get_the_ID();
$is_en    = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';
$hero_pin = absint( get_post_meta( $post_id, '_osvita_sub_hero_id', true ) );

// ── Parent (Освіта) page link ────────────────────────────────────
$parent_id   = wp_get_post_parent_id( $post_id );
$parent_page = $parent_id ? get_post( $parent_id ) : null;

// ── Sibling sub-pages (navigation) ───────────────────────────────
$_siblings = $parent_id ? get_posts( array(
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'post_parent'    => $parent_id,
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
) ) : array();

// ── Documents for this sub-section (optional, ordered) ───────────
$_docs_query = new WP_Query( array(
    'post_type'      => 'mits_document',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'no_found_rows'  => true,
) );

$docs_title   = $is_en ? 'Documents'    : 'Документи';
$consult_text = $is_en ? 'Consultation' : 'Консультація';
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

  <!-- ===================== HERO SLIDER ===================== -->
  <?php if ( function_exists( 'mits_hero_render' ) ) mits_hero_render( '#contact-form', '', $hero_pin ); ?>



  <!-- ===================== ЗМІСТ СТОРІНКИ ===================== -->
  <?php
  $_sub_content = '';
  if ( have_posts() ) {
      the_post();
      $_sub_content = get_the_content();
  }
  if ( trim( strip_tags( $_sub_content ) ) ) : ?>
  <section class="mits-section mits-osvita-sub-content">
    <div class="mits-container">
      <div class="mits-vstup-sub-content__card">
        <div class="mits-vstup-sub-content__body">
          <?php echo apply_filters( 'the_content', $_sub_content ); ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div><!-- #mits-front -->
</div><!-- .entry-content -->
</div><!-- .sections_group -->
</div><!-- .content_wrapper -->
</div><!-- #Content -->

<?php get_footer(); ?>
