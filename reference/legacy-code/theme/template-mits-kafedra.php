<?php
/**
 * Template Name: MITS — Кафедра
 *
 * Page template for the Department (Кафедра) page.
 *
 * @package Betheme
 */

get_header();

$post_id   = get_the_ID();
$is_en     = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';
$logo_id   = get_theme_mod( 'custom_logo' );
$logo_src  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';

// Specialty tiles — only from DB, no defaults
$specs = array();
for ( $i = 1; $i <= 3; $i++ ) {
    $val = get_post_meta( $post_id, '_kafedra_spec_' . $i, true );
    if ( $val !== '' && $val !== false ) {
        $specs[] = $val;
    }
}
$spec_subtitle = $is_en ? 'Trajectories' : 'Траєкторії';

// History link
$history_url  = get_post_meta( $post_id, '_kafedra_history_url', true ) ?: ( $is_en ? '/en/about-department/' : '/про-кафедру/' );
$history_text = get_post_meta( $post_id, '_kafedra_history_text', true ) ?: ( $is_en ? 'Department history' : 'Історія кафедри' );

// Section title
$nav_title = $is_en ? 'Education' : 'Навчання';

// Activity cards (same as front page)
$_act_lang   = $is_en ? 'en' : 'uk';
$_act_opts   = get_option( 'mits_activities', array() );
$_act_sfx    = $is_en ? '_en' : '';
$_act_title  = $_act_opts[ 'title' . $_act_sfx ] ?? ( $is_en ? 'Activity' : 'Діяльність' );
$_act_cta    = $_act_opts[ 'cta_text' . $_act_sfx ] ?? ( $is_en ? 'Study plan' : 'Навчальна програма' );
$_act_cta_url = $_act_opts['cta_url'] ?? '#';

$_act_query = new WP_Query( array(
    'post_type'      => 'mits_activity',
    'post_status'    => 'publish',
    'posts_per_page' => 2,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'lang'           => $_act_lang,
    'no_found_rows'  => true,
) );
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

  <!-- ===================== HERO SLIDER ===================== -->
  <?php if ( function_exists( 'mits_hero_render' ) ) mits_hero_render( '#contact-form', '', 192 ); ?>

  <!-- ===================== HERO ===================== -->
  <section class="mits-section mits-kafedra-hero">
    <div class="mits-container">
      <?php if ( $logo_src ) : ?>
      <div class="mits-kafedra-hero__logo">
        <img src="<?php echo esc_url( $logo_src ); ?>" alt="МІТС" width="120" height="120">
      </div>
      <?php endif; ?>
      <h1 class="mits-kafedra-hero__title">МІТС</h1>
    </div>
  </section>

  <!-- ===================== ОПИС ===================== -->
  <section class="mits-section mits-kafedra-about">
    <div class="mits-container">
      <div class="mits-kafedra-about__box">
        <?php
        while ( have_posts() ) {
            the_post();
            the_content();
        }
        ?>
      </div>
      <div class="mits-kafedra-about__link">
        <a href="<?php echo esc_url( $history_url ); ?>"><?php echo esc_html( $history_text ); ?></a>
      </div>
    </div>
  </section>

  <!-- ===================== СПЕЦІАЛІЗАЦІЇ ===================== -->
  <?php if ( ! empty( $specs ) ) : ?>
  <section class="mits-kafedra-specs">
    <div class="mits-container">
      <h2 class="mits-kafedra-specs__subtitle"><?php echo esc_html( $spec_subtitle ); ?></h2>
      <div class="mits-kafedra-specs__grid">
        <?php foreach ( $specs as $spec ) : ?>
        <div class="mits-kafedra-spec-tile">
          <p><?php echo esc_html( $spec ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== НАВЧАННЯ ===================== -->
  <section class="mits-section mits-kafedra-nav-title">
    <div class="mits-container">
      <h2 class="mits-kafedra-section-heading"><?php echo esc_html( strtoupper( $nav_title ) ); ?></h2>
    </div>
  </section>

  <!-- ===================== ДІЯЛЬНІСТЬ ===================== -->
  <section class="mits-section mits-activities mits-kafedra-activities">
    <div class="mits-container">
      <div class="mits-cards">
        <?php while ( $_act_query->have_posts() ) : $_act_query->the_post();
          $_id      = get_the_ID();
          $icon_url = get_post_meta( $_id, '_act_icon_url', true );
          $icon_bg  = get_post_meta( $_id, '_act_icon_bg',  true );
          $act_btn  = get_post_meta( $_id, '_act_btn',      true ) ?: ( $is_en ? 'More' : 'Детальніше' );
          $act_url  = get_post_meta( $_id, '_act_url',      true ) ?: '#';
          $act_desc = get_post_meta( $_id, '_act_desc',     true );
        ?>
        <div class="mits-card">
          <div class="mits-card__header">
            <?php if ( $icon_url || $icon_bg ) : ?>
            <div class="mits-card__icon"<?php if ( $icon_bg ) echo ' style="background-color:' . esc_attr( $icon_bg ) . ';"'; ?>>
              <?php if ( $icon_url ) : ?>
                <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
              <?php endif; ?>
            </div>
            <?php endif; ?>
            <h3><?php the_title(); ?></h3>
          </div>
          <?php if ( $act_desc ) : ?>
          <p><?php echo wp_kses_post( $act_desc ); ?></p>
          <?php endif; ?>
          <a href="<?php echo esc_url( $act_url ); ?>" class="mits-btn mits-btn--outline"><?php echo esc_html( $act_btn ); ?></a>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
      <?php if ( $_act_cta ) : ?>
      <div class="mits-activities__cta">
        <a href="<?php echo esc_url( $_act_cta_url ); ?>" class="mits-btn mits-btn--primary"><?php echo esc_html( $_act_cta ); ?></a>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ===================== ВИКЛАДАЧІ ===================== -->
  <?php
  $_tchr_lang  = $is_en ? 'en' : 'uk';
  $_tchr_query = new WP_Query( array(
      'post_type'      => 'teacher',
      'post_status'    => 'publish',
      'posts_per_page' => 2,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
      'lang'           => $_tchr_lang,
      'no_found_rows'  => true,
  ) );
  $_tchr_archive = get_post_type_archive_link( 'teacher' );
  $_tchr_heading = $is_en ? 'TEACHERS'     : 'ВИКЛАДАЧІ';
  $_tchr_all     = $is_en ? 'All teachers' : 'Всі викладачі';
  if ( $_tchr_query->have_posts() ) : ?>
  <section class="mits-section mits-kafedra-teachers">
    <div class="mits-container">
      <h2 class="mits-kafedra-section-heading"><?php echo esc_html( $_tchr_heading ); ?></h2>
      <div class="mits-teachers-grid mits-kafedra-teachers__grid">
        <?php while ( $_tchr_query->have_posts() ) : $_tchr_query->the_post();
          $position  = get_post_meta( get_the_ID(), '_teacher_position', true );
          $grade     = get_post_meta( get_the_ID(), '_teacher_grade',    true );
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
      <?php if ( $_tchr_archive ) : ?>
      <div class="mits-kafedra-teachers__all">
        <a href="<?php echo esc_url( $_tchr_archive ); ?>" class="mits-btn mits-btn--outline">
          <?php echo esc_html( $_tchr_all ); ?> →
        </a>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

</div>
</div>
</div>
</div>
</div>

<?php get_footer(); ?>
