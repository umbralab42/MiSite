<?php
/**
 * Template Name: MITS — Вступ підрозділ
 *
 * Sub-page template for Bachelor's, Master's and PhD admission pages.
 * Which hero slider post to pin is controlled by the '_vstup_sub_hero_id' meta
 * field (set in WP-Admin via the "Параметри підрозділу Вступ" meta box).
 *
 * @package Betheme
 */

get_header();

$post_id       = get_the_ID();
$is_en         = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';
$hero_pin      = absint( get_post_meta( $post_id, '_vstup_sub_hero_id', true ) );

// Contacts page (translated if Polylang active)
$contacts_id = 24;
if ( function_exists( 'pll_get_post' ) ) {
    $translated = pll_get_post( $contacts_id );
    if ( $translated ) {
        $contacts_id = $translated;
    }
}
$contacts_page = get_post( $contacts_id );

$consult_text = $is_en ? 'Consultation'    : 'Консультація';
$news_title   = $is_en ? 'News'             : 'Новини';
$tips_title   = $is_en ? 'Important Tips'   : 'Важливі поради';

// Load tips from the mits_edu_level entry linked to this page
$_edu_lvl_q = new WP_Query( array(
    'post_type'      => 'mits_edu_level',
    'posts_per_page' => 1,
    'post_status'    => 'publish',
    'no_found_rows'  => true,
    'meta_query'     => array( array(
        'key'   => '_edu_level_page_id',
        'value' => $post_id,
        'type'  => 'NUMERIC',
    ) ),
) );
$_tips           = array();
$_algorithm      = array();
$_algo_title     = '';
$_subjects       = array();
$_subject_alt    = '';
$_subjects_title = '';
$_tips_title     = '';
$_steps          = array();
$_description    = '';
if ( $_edu_lvl_q->have_posts() ) {
    $_edu_lvl_q->the_post();
    $_eid      = get_the_ID();
    $_tips_raw = get_post_meta( $_eid, '_edu_level_tips', true );
    if ( $_tips_raw !== '' && $_tips_raw !== false ) {
        $decoded = json_decode( $_tips_raw, true );
        if ( is_array( $decoded ) ) {
            $_tips = $decoded;
        }
    } else {
        // Fallback: legacy numbered keys _edu_level_tip_1..5
        for ( $i = 1; $i <= 5; $i++ ) {
            $t = get_post_meta( $_eid, '_edu_level_tip_' . $i, true );
            if ( $t !== '' ) {
                $_tips[] = $t;
            }
        }
    }
    // Load subjects
    $_subjects_title = (string) get_post_meta( $_eid, '_edu_level_subjects_title', true );
    $_subject_alt    = (string) get_post_meta( $_eid, '_edu_level_subject_alt', true );
    $_subj_raw       = get_post_meta( $_eid, '_edu_level_subjects', true );
    if ( $_subj_raw !== '' && $_subj_raw !== false ) {
        $decoded = json_decode( $_subj_raw, true );
        if ( is_array( $decoded ) ) {
            $_subjects = $decoded;
        }
    }
    // Load tips title
    $_tips_title = (string) get_post_meta( $_eid, '_edu_level_tips_title', true );
    // Load steps
    $_steps_raw = get_post_meta( $_eid, '_edu_level_steps', true );
    if ( $_steps_raw !== '' && $_steps_raw !== false ) {
        $decoded = json_decode( $_steps_raw, true );
        if ( is_array( $decoded ) ) {
            $_steps = $decoded;
        }
    }
    // Load short description
    $_description = (string) get_post_meta( $_eid, '_edu_level_description', true );
    // Load algorithm
    $_algo_title = (string) get_post_meta( $_eid, '_edu_level_algorithm_title', true );
    $_algo_raw   = get_post_meta( $_eid, '_edu_level_algorithm', true );
    if ( $_algo_raw !== '' && $_algo_raw !== false ) {
        $decoded = json_decode( $_algo_raw, true );
        if ( is_array( $decoded ) ) {
            $_algorithm = $decoded;
        }
    }
    wp_reset_postdata();
}
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
  <section class="mits-section mits-vstup-sub-content">
    <div class="mits-container">
      <div class="mits-vstup-sub-content__card">
        <div class="mits-vstup-sub-content__body">
          <?php echo apply_filters( 'the_content', $_sub_content ); ?>
        </div>
      </div>
    </div><!-- /.mits-container -->
  </section>
  <?php endif; ?>

  <!-- ===================== КРОКИ ===================== -->
  <?php if ( ! empty( $_steps ) ) : ?>
  <div class="mits-steps">
    <div class="mits-container">
      <div class="mits-steps__flow">
        <?php foreach ( $_steps as $i => $_step ) : ?>
        <?php if ( $i > 0 ) : ?><span class="mits-steps__arrow" aria-hidden="true">&rarr;</span><?php endif; ?>
        <span class="mits-steps__item"><?php echo esc_html( $_step ); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ===================== КОРОТКИЙ ОПИС ===================== -->
  <?php if ( trim( strip_tags( $_description ) ) ) : ?>
  <section class="mits-section mits-vstup-sub-desc">
    <div class="mits-container">
      <?php echo wp_kses_post( $_description ); ?>
    </div>
  </section>
  <?php endif; ?>

</div><!-- #mits-front -->
</div><!-- .entry-content -->
</div><!-- .sections_group -->
</div><!-- .content_wrapper -->
</div><!-- #Content -->

<!-- ===================== ПРЕДМЕТИ ===================== -->
<?php if ( ! empty( $_subjects ) ) : ?>
<section class="mits-section mits-subjects">
  <div class="mits-container">
    <?php if ( $_subjects_title ) : ?>
    <p class="mits-subjects__label"><?php echo esc_html( $_subjects_title ); ?></p>
    <?php endif; ?>
    <div class="mits-subjects__grid">
      <?php foreach ( $_subjects as $_subj ) : ?>
      <span class="mits-subjects__item"><?php echo esc_html( $_subj ); ?></span>
      <?php endforeach; ?>
    </div>
    <?php if ( $_subject_alt ) : ?>
    <p class="mits-subjects__note"><?php echo esc_html( $_subject_alt ); ?></p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ===================== АЛГОРИТМ ВСТУПУ ===================== -->
<?php if ( ! empty( $_algorithm ) ) : ?>
<section class="mits-section mits-algo">
  <div class="mits-container">
    <div class="mits-algo__cta">
      <a href="#contact-form" class="mits-btn mits-btn--primary"><?php echo esc_html( $consult_text ); ?></a>
    </div>
    <h2 class="mits-algo__title"><?php echo esc_html( $_algo_title ?: ( $is_en ? 'Admission Algorithm' : 'Алгоритм вступу' ) ); ?></h2>
    <div class="mits-algo__card">
      <ol class="mits-algo__list">
        <?php foreach ( $_algorithm as $_step ) : ?>
        <li class="mits-algo__item"><div class="mits-algo__item-body"><?php echo wp_kses_post( $_step ); ?></div></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== ВАЖЛИВІ ПОРАДИ ===================== -->
<?php if ( ! empty( $_tips ) ) : ?>
<section class="mits-section mits-tips">
  <div class="mits-container">
    <h2 class="mits-tips__title"><?php echo esc_html( $_tips_title ?: ( $is_en ? 'Important Tips' : 'Важливо' ) ); ?></h2>
    <ul class="mits-tips__list">
      <?php foreach ( $_tips as $_tip ) : ?>
      <li class="mits-tips__item">
        <img class="mits-tips__check" src="<?php echo esc_url( get_template_directory_uri() . '/images/tick.png' ); ?>" width="32" height="32" alt="" aria-hidden="true">
        <span><?php echo esc_html( $_tip ); ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
    <div class="mits-tips__cta">
      <a href="#contact-form" class="mits-btn mits-btn--primary"><?php echo esc_html( $consult_text ); ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== НОВИНИ ===================== -->
<?php
$mits_news = new WP_Query( array(
    'post_type'      => 'news',
    'posts_per_page' => 3,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'no_found_rows'  => true,
) );
if ( $mits_news->have_posts() ) : ?>
<section class="mits-section mits-news">
  <div class="mits-container">
    <h2 class="mits-section__title"><?php echo esc_html( $news_title ); ?></h2>
    <div class="mits-news__grid">
      <?php while ( $mits_news->have_posts() ) : $mits_news->the_post(); ?>
      <div class="mits-news__item">
        <?php if ( has_post_thumbnail() ) : ?>
          <div class="mits-news__img"><?php the_post_thumbnail( 'medium_large' ); ?></div>
        <?php else : ?>
          <div class="mits-news__img mits-news__img--placeholder"></div>
        <?php endif; ?>
        <div class="mits-news__meta">
          <span class="mits-news__date"><?php echo get_the_date( 'd.m.Y' ); ?></span>
        </div>
        <h3 class="mits-news__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="mits-news__excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
      </div>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== КОНТАКТИ ===================== -->
<?php if ( $contacts_page ) : ?>
<section class="mits-section mits-contacts" id="contact-form">
  <div class="mits-container">
    <h2 class="mits-page__title"><?php echo esc_html( $contacts_page->post_title ); ?></h2>
    <?php echo apply_filters( 'the_content', $contacts_page->post_content ); ?>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
