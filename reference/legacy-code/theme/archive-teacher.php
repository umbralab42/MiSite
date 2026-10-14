<?php
/**
 * Archive template for the "teacher" post type.
 *
 * @package Betheme
 */

get_header();

$teachers = new WP_Query( array(
    'post_type'      => 'teacher',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'lang'           => function_exists( 'pll_current_language' ) ? pll_current_language() : '',
) );
?>

<div id="Content">
<div class="content_wrapper clearfix">
<main class="sections_group">
<div class="entry-content">

<section class="mits-section mits-teachers-archive">
  <div class="mits-container">
    <h1 class="mits-section__title"><?php esc_html_e( 'Викладачі', 'betheme' ); ?></h1>

    <?php if ( $teachers->have_posts() ) : ?>
    <div class="mits-teachers-grid">
      <?php while ( $teachers->have_posts() ) : $teachers->the_post();
        $position  = get_post_meta( get_the_ID(), '_teacher_position',    true );
        $grade     = get_post_meta( get_the_ID(), '_teacher_grade',       true );
        $pubs_url  = get_post_meta( get_the_ID(), '_teacher_publications', true );
        $photo_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
      ?>
      <div class="mits-teacher-card">
        <a class="mits-teacher-card__photo-wrap" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
          <?php if ( $photo_url ) : ?>
            <img class="mits-teacher-card__photo"
                 src="<?php echo esc_url( $photo_url ); ?>"
                 alt="<?php echo esc_attr( get_the_title() ); ?>">
          <?php else : ?>
            <div class="mits-teacher-card__photo mits-teacher-card__photo--placeholder"></div>
          <?php endif; ?>
        </a>
        <div class="mits-teacher-card__info">
          <p class="mits-teacher-card__name">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </p>
          <?php if ( $position ) : ?>
            <p class="mits-teacher-card__position"><?php echo esc_html( $position ); ?></p>
          <?php endif; ?>
          <?php if ( $grade ) : ?>
            <p class="mits-teacher-card__grade"><?php echo esc_html( $grade ); ?></p>
          <?php endif; ?>
          <?php if ( $pubs_url ) : ?>
            <a class="mits-teacher-card__link"
               href="<?php echo esc_url( $pubs_url ); ?>"
               target="_blank" rel="noopener"><?php esc_html_e( 'НАУКОВІ ПРАЦІ', 'betheme' ); ?></a>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php else : ?>
      <p style="color:rgba(255,255,255,0.5);text-align:center;"><?php esc_html_e( 'Викладачів ще не додано.', 'betheme' ); ?></p>
    <?php endif; ?>

  </div>
</section>

</div>
</main>
</div>
</div>

<?php get_footer(); ?>
