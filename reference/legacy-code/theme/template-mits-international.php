<?php
/**
 * Template Name: MITS — Міжнародна діяльність
 *
 * Головна сторінка розділу «Міжнародна діяльність».
 *
 * @package Betheme
 */

get_header();

$is_en = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';

// ── Options ──────────────────────────────────────────────────────
$hero_id = (int) get_option( 'mits_intl_hero_id', 0 );
$photo  = get_option( 'mits_intl_photo', '' );
$text   = get_option( 'mits_intl_text',  '' );
$links  = json_decode( get_option( 'mits_intl_links' ) ?: '[]', true );
$stats  = json_decode( get_option( 'mits_intl_stats' ) ?: '[]', true );
$courses_slogan = get_option( 'mits_intl_courses_slogan', '' );
$courses_title  = get_option( 'mits_intl_courses_title',  '' );
$courses_photo  = get_option( 'mits_intl_courses_photo',  '' );
$courses_text   = get_option( 'mits_intl_courses_text',   '' );
$courses        = json_decode( get_option( 'mits_intl_courses' ) ?: '[]', true );
if ( ! is_array( $links ) ) $links = [];
$docs_title = get_option( 'mits_intl_docs_title', '' );
$docs       = json_decode( get_option( 'mits_intl_docs' ) ?: '[]', true );
if ( ! is_array( $docs ) ) $docs = [];if ( ! is_array( $stats ) ) $stats = [];
if ( ! is_array( $courses ) ) $courses = [];

// ── Contacts page ────────────────────────────────────────────────
$contacts_id = 24;
if ( function_exists( 'pll_get_post' ) ) {
	$translated = pll_get_post( $contacts_id );
	if ( $translated ) $contacts_id = $translated;
}
$contacts_page = get_post( $contacts_id );


$links_title = $is_en ? 'Links' : 'Посилання';
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

  <!-- ===================== HERO SLIDER ===================== -->
  <?php if ( function_exists( 'mits_hero_render' ) ) mits_hero_render( '#contact-form', '', $hero_id ); ?>

  <!-- ===================== ПРО ВІДДІЛ: фото + текст ===================== -->
  <?php if ( $photo || $text ) : ?>
  <section class="mits-section mits-intl-about">
    <div class="mits-container">
      <div class="mits-intl-about__inner<?php echo ( $photo && $text ) ? ' mits-intl-about__inner--split' : ''; ?>">
        <?php if ( $photo ) : ?>
        <div class="mits-intl-about__photo">
          <img src="<?php echo esc_url( $photo ); ?>" alt="" class="mits-intl-about__img">
        </div>
        <?php endif; ?>
        <?php if ( $text || ! empty( $links ) ) : ?>
        <div class="mits-intl-about__text">
          <?php if ( $text ) : ?>
          <?php echo wp_kses_post( $text ); ?>
          <?php endif; ?>
          <?php if ( ! empty( $links ) ) : ?>
          <ul class="mits-intl-about__links">
            <?php foreach ( $links as $link ) :
              $lname = esc_html( $link['name'] ?? '' );
              $lurl  = esc_url( $link['url'] ?? '' );
              if ( ! $lname && ! $lurl ) continue;
            ?>
            <li>
              <?php if ( $lurl ) : ?>
              <a href="<?php echo $lurl; ?>" target="_blank" rel="noopener noreferrer">
                <?php echo $lname ?: $lurl; ?>
              </a>
              <?php else : ?>
              <span><?php echo $lname; ?></span>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== СТАТИСТИКА ===================== -->
  <?php
  $has_stats = ! empty( array_filter( $stats, fn( $item ) => empty( $item['divider'] ) && ( trim( $item['label'] ?? '' ) !== '' || trim( $item['value'] ?? '' ) !== '' ) ) );
  if ( $has_stats ) :
    $in_row    = false;
    $row_count = 0;
  ?>
  <section class="mits-section mits-intl-stats">
    <div class="mits-container">
      <?php foreach ( $stats as $item ) :
        if ( ! empty( $item['divider'] ) ) :
          if ( $in_row ) { echo '</div>'; $in_row = false; $row_count = 0; }
          ?><div class="mits-intl-stats__divider"></div><?php
        else :
          $val = trim( $item['value'] ?? '' );
          $lbl = trim( $item['label'] ?? '' );
          if ( $val === '' && $lbl === '' ) { continue; }
          if ( ! $in_row || $row_count >= 4 ) {
            if ( $in_row ) echo '</div>';
            echo '<div class="mits-intl-stats__row">';
            $in_row    = true;
            $row_count = 0;
          }
          $row_count++;
          ?>
          <div class="mits-intl-stat">
            <span class="mits-intl-stat__value"><?php echo esc_html( $val ); ?></span>
            <span class="mits-intl-stat__label"><?php echo esc_html( $lbl ); ?></span>
          </div>
          <?php
        endif;
      endforeach;
      if ( $in_row ) echo '</div>';
      ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== КУРСИ ===================== -->
  <?php if ( ! empty( $courses ) || $courses_slogan || $courses_title || $courses_photo || $courses_text ) : ?>
  <section class="mits-section mits-intl-courses">
    <div class="mits-container">
      <?php if ( $courses_photo || $courses_text ) : ?>
      <div class="mits-intl-courses__intro">
        <?php if ( $courses_photo ) : ?>
        <div class="mits-intl-courses__intro-photo">
          <img src="<?php echo esc_url( $courses_photo ); ?>" alt="" loading="lazy">
        </div>
        <?php endif; ?>
        <?php if ( $courses_text ) : ?>
        <div class="mits-intl-courses__intro-text">
          <?php echo wp_kses_post( $courses_text ); ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if ( $courses_slogan ) : ?>
      <p class="mits-intl-courses__slogan"><?php echo esc_html( $courses_slogan ); ?></p>
      <?php endif; ?>
      <?php if ( $courses_title ) : ?>
      <h2 class="mits-section__title"><?php echo esc_html( $courses_title ); ?></h2>
      <?php endif; ?>
      <?php if ( ! empty( $courses ) ) : ?>
      <div class="mits-advantages__accordion" id="mits-intl-courses-acc">
        <?php foreach ( $courses as $course ) :
          $cname = trim( $course['name'] ?? '' );
          $cdesc = trim( $course['desc'] ?? '' );
          if ( $cname === '' && $cdesc === '' ) continue;
        ?>
        <div class="mits-adv-item">
          <button class="mits-adv-item__header" aria-expanded="false"<?php if ( ! $cdesc ) echo ' data-no-content'; ?>>
            <span><?php echo esc_html( $cname ); ?></span>
            <?php if ( $cdesc ) : ?>
            <span class="mits-adv-item__chevron">›</span>
            <?php endif; ?>
          </button>
          <?php if ( $cdesc ) : ?>
          <div class="mits-adv-item__body">
            <div class="mits-adv-item__body-inner"><?php echo wp_kses_post( $cdesc ); ?></div>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <script>
      (function () {
        document.querySelectorAll('#mits-intl-courses-acc .mits-adv-item__header:not([data-no-content])').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var isOpen = this.getAttribute('aria-expanded') === 'true';
            document.querySelectorAll('#mits-intl-courses-acc .mits-adv-item__header').forEach(function (b) {
              b.setAttribute('aria-expanded', 'false');
            });
            if ( ! isOpen ) { this.setAttribute('aria-expanded', 'true'); }
          });
        });
      }());
      </script>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== ДОКУМЕНТИ ===================== -->
  <?php
  $has_docs = ! empty( array_filter( $docs, fn( $d ) => trim( $d['name'] ?? '' ) !== '' || trim( $d['url'] ?? '' ) !== '' ) );
  if ( $has_docs || $docs_title ) :
  ?>
  <section class="mits-section mits-intl-docs">
    <div class="mits-container">
      <?php if ( $docs_title ) : ?>
      <h2 class="mits-section__title"><?php echo esc_html( $docs_title ); ?></h2>
      <?php endif; ?>
      <ul class="mits-intl-docs__list">
        <?php foreach ( $docs as $doc ) :
          $dname = trim( $doc['name'] ?? '' );
          $durl  = trim( $doc['url']  ?? '' );
          if ( $dname === '' && $durl === '' ) continue;
        ?>
        <li class="mits-intl-docs__item">
          <a class="mits-intl-docs__a" href="<?php echo esc_url( $durl ); ?>" target="_blank" rel="noopener noreferrer">
            <span class="mits-intl-docs__icon">&#128196;</span>
            <span class="mits-intl-docs__name"><?php echo esc_html( $dname ); ?></span>
            <span class="mits-intl-docs__dl">&#8595;</span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
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
