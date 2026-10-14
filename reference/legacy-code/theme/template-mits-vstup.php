<?php
/**
 * Template Name: MITS — Вступ
 *
 * Page template for the Admission (Вступ) page.
 *
 * @package Betheme
 */

get_header();

$post_id = get_the_ID();
$is_en   = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';

// ── Education levels (CPT mits_edu_level) ────────────────────────
$_lvl_lang  = $is_en ? 'en' : 'uk';
$_lvl_query = new WP_Query( array(
    'post_type'      => 'mits_edu_level',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'lang'           => $_lvl_lang,
    'no_found_rows'  => true,
) );

// ── Vstup page meta ──────────────────────────────────────────────
$video_url = get_post_meta( $post_id, '_vstup_video_url', true );

// ── FAQ items ────────────────────────────────────────────────────
$_faq_lang  = $is_en ? 'en' : 'uk';
$_faq_query = new WP_Query( array(
    'post_type'      => 'mits_faq',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'lang'           => $_faq_lang,
    'no_found_rows'  => true,
) );

// ── Contacts page ────────────────────────────────────────────────
$contacts_id = 24;
if ( function_exists( 'pll_get_post' ) ) {
    $translated = pll_get_post( $contacts_id );
    if ( $translated ) $contacts_id = $translated;
}
$contacts_page = get_post( $contacts_id );

// ── I18n labels ──────────────────────────────────────────────────
$levels_title = $is_en ? 'EDUCATIONAL LEVELS'  : 'ОСВІТНІ РІВНІ';
$news_title   = $is_en ? 'News'                 : 'Новини';
$consult_text = $is_en ? 'Consultation'         : 'Консультація';
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

  <!-- ===================== HERO SLIDER ===================== -->
  <?php if ( function_exists( 'mits_hero_render' ) ) mits_hero_render( '#contact-form', '', 193 ); ?>

  <!-- ===================== ОСВІТНІ РІВНІ ===================== -->
  <section class="mits-section mits-vstup-levels">
    <div class="mits-container">
      <h2 class="mits-section__title"><?php echo esc_html( $levels_title ); ?></h2>
      <?php
      $_page_content = '';
      while ( have_posts() ) {
          the_post();
          $_page_content = get_the_content();
      }
      if ( trim( strip_tags( $_page_content ) ) ) : ?>
      <div class="mits-vstup-levels__desc">
        <?php echo apply_filters( 'the_content', $_page_content ); ?>
      </div>
      <?php endif; ?>
      <div class="mits-vstup-levels__buttons">
        <?php while ( $_lvl_query->have_posts() ) : $_lvl_query->the_post();
          $_page_id = (int) get_post_meta( get_the_ID(), '_edu_level_page_id', true );
          if ( $_page_id > 0 ) {
              // Same as menu: get_permalink() for the linked page
              $_lvl_url = get_permalink( $_page_id ) ?: '#';
          } else {
              // Fallback: legacy _edu_level_url (may contain /?page_id=X or a plain URL)
              $_lvl_raw = get_post_meta( get_the_ID(), '_edu_level_url', true );
              if ( $_lvl_raw && preg_match( '/[?&]page_id=(\d+)/', $_lvl_raw, $_m ) ) {
                  $_lvl_url = get_permalink( (int) $_m[1] ) ?: $_lvl_raw;
              } else {
                  $_lvl_url = $_lvl_raw ?: '#';
              }
          }
        ?>
        <a href="<?php echo esc_url( $_lvl_url ); ?>" class="mits-btn mits-btn--level">
          <?php the_title(); ?>
        </a>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>

  <!-- ===================== ВІДЕО ===================== -->
  <?php if ( $video_url ) : ?>
  <section class="mits-section mits-vstup-video">
    <div class="mits-container">
      <div class="mits-vstup-video__wrap">
        <iframe
          src="<?php echo esc_url( $video_url ); ?>"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"></iframe>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== FAQ ===================== -->
  <?php if ( $_faq_query->have_posts() ) : ?>
  <section class="mits-section mits-vstup-faq">
    <div class="mits-container">
      <h2 class="mits-section__title">FAQ</h2>
      <div class="mits-advantages__accordion">
        <?php while ( $_faq_query->have_posts() ) : $_faq_query->the_post();
          $_faq_answer = get_post_meta( get_the_ID(), '_faq_answer', true );
        ?>
        <div class="mits-adv-item">
          <button class="mits-adv-item__header" aria-expanded="false"<?php if ( ! $_faq_answer ) echo ' data-no-content'; ?>>
            <span><?php the_title(); ?></span>
            <?php if ( $_faq_answer ) : ?>
            <span class="mits-adv-item__chevron">›</span>
            <?php endif; ?>
          </button>
          <?php if ( $_faq_answer ) : ?>
          <div class="mits-adv-item__body">
            <div class="mits-adv-item__body-inner"><?php echo wp_kses_post( $_faq_answer ); ?></div>
          </div>
          <?php endif; ?>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
      <div class="mits-vstup-faq__cta">
        <a href="#contact-form" class="mits-btn mits-btn--primary"><?php echo esc_html( $consult_text ); ?></a>
      </div>
    </div>
  </section>
  <script>
  (function () {
    document.querySelectorAll('.mits-vstup-faq .mits-adv-item__header:not([data-no-content])').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var isOpen = this.getAttribute('aria-expanded') === 'true';
        document.querySelectorAll('.mits-vstup-faq .mits-adv-item__header').forEach(function (b) {
          b.setAttribute('aria-expanded', 'false');
        });
        if ( ! isOpen ) { this.setAttribute('aria-expanded', 'true'); }
      });
    });
  }());
  </script>
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

</div><!-- #mits-front -->
</div><!-- .entry-content -->
</div><!-- .sections_group -->
</div><!-- .content_wrapper -->
</div><!-- #Content -->

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
