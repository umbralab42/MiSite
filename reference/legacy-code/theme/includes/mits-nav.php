<?php
/**
 * MITS custom navigation bar, dropdown slider, and their JS.
 * Included via get_template_part('includes/mits-nav') from header.php.
 *
 * @package Betheme
 */

$mits_site_name = get_bloginfo( 'name' );
$mits_logo_id   = get_theme_mod( 'custom_logo' );
$mits_logo_src  = $mits_logo_id ? wp_get_attachment_image_url( $mits_logo_id, 'full' ) : '';

// ---------- Language detection via Polylang ----------
$mits_is_en = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';

// Menu slug: use EN menu when available, fall back to UA.
// Create 'main_menu_mits_en' via the setup script or WP admin when the EN menu is ready.
$mits_menu_slug = $mits_is_en && wp_get_nav_menu_object( 'main_menu_mits_en' ) ? 'main_menu_mits_en' : 'main_menu_mits_ua';

// Translated UI strings — see includes/mits-i18n.php
// mits_t('nav.cta'), mits_t('nav.home_aria'), etc.

// Language switcher URLs: Polylang translation → URL-prefix swap → language home fallback.
$mits_ua_url = mits_lang_switcher_url( 'uk' );
$mits_en_url = mits_lang_switcher_url( 'en' );
?>

<!-- ===================== MITS NAV ===================== -->
<nav class="mits-nav" aria-label="Main navigation">
  <div class="mits-nav__inner">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mits-nav__logo" aria-label="<?php echo esc_attr( $mits_site_name ); ?> — <?php mits_te( 'nav.home_aria' ); ?>">
      <?php if ( $mits_logo_src ) : ?>
        <img src="<?php echo esc_url( $mits_logo_src ); ?>" alt="<?php echo esc_attr( $mits_site_name ); ?>" width="80" height="80" />
      <?php endif; ?>
    </a>

    <?php
    wp_nav_menu( array(
        'menu'           => $mits_menu_slug,
        'theme_location' => 'mits-primary',
        'menu_class'     => 'mits-nav__menu',
        'container'      => false,
        'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
        'fallback_cb'    => false,
        'depth'          => 0,
    ) );
    ?>

    <!-- Language switcher -->
    <div class="mits-lang-switcher" role="navigation" aria-label="<?php echo $mits_is_en ? 'Language switcher' : 'Перемикач мови'; ?>">
      <a href="<?php echo $mits_ua_url; ?>" class="mits-lang-switcher__btn<?php echo ! $mits_is_en ? ' is-active' : ''; ?>" lang="uk" hreflang="uk" <?php echo ! $mits_is_en ? 'aria-current="true"' : ''; ?>>&#x1F1FA;&#x1F1E6;</a>
      <a href="<?php echo $mits_en_url; ?>" class="mits-lang-switcher__btn<?php echo $mits_is_en ? ' is-active' : ''; ?>" lang="en" hreflang="en" <?php echo $mits_is_en ? 'aria-current="true"' : ''; ?>>&#x1F1EC;&#x1F1E7;</a>
    </div>

    <!-- Hamburger (mobile only) -->
    <button
      class="mits-nav__hamburger"
      id="mitsHamburger"
      aria-expanded="false"
      aria-label="<?php mits_te( 'nav.hamburger_aria' ); ?>"
    >
      <span></span>
      <span></span>
      <span></span>
    </button>

    <a href="#contact-form" class="mits-btn mits-btn--primary mits-nav__cta"><?php mits_te( 'nav.cta' ); ?></a>
  </div>
</nav>

<!-- Arrow button — fixed at top -->
<button
  class="mits-nav__arrow"
  id="mitsDropdownArrow"
  aria-expanded="false"
  aria-controls="mitsDropdown"
  aria-label="<?php mits_te( 'nav.arrow_aria' ); ?>"
>
  <svg width="38" height="33" viewBox="0 0 76 66" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M56.0819 39.7483L40.2486 55.5816C39.9542 55.8784 39.6039 56.114 39.2181 56.2748C38.8322 56.4356 38.4183 56.5183 38.0002 56.5183C37.5822 56.5183 37.1683 56.4356 36.7824 56.2748C36.3965 56.114 36.0463 55.8784 35.7519 55.5816L19.9186 39.7483C19.6218 39.4539 19.3862 39.1037 19.2254 38.7178C19.0646 38.3319 18.9819 37.918 18.9819 37.5C18.9819 37.0819 19.0646 36.668 19.2254 36.2821C19.3862 35.8963 19.6218 35.546 19.9186 35.2516C20.213 34.9548 20.5632 34.7192 20.9491 34.5585C21.335 34.3977 21.7489 34.3149 22.1669 34.3149C22.5849 34.3149 22.9988 34.3977 23.3847 34.5585C23.7706 34.7192 24.1208 34.9548 24.4152 35.2516L38.0002 48.8683L51.5852 35.2516C52.1815 34.6553 52.9903 34.3203 53.8336 34.3203C54.2511 34.3203 54.6646 34.4026 55.0504 34.5624C55.4361 34.7222 55.7866 34.9564 56.0819 35.2516C56.6782 35.8479 57.0132 36.6567 57.0132 37.5C57.0132 37.9175 56.9309 38.331 56.7712 38.7168C56.6114 39.1025 56.3772 39.453 56.0819 39.7483Z" fill="#569DE6"/>
  </svg>
</button>

<!-- ===================== DROPDOWN ===================== -->
<div class="mits-dropdown" id="mitsDropdown" aria-hidden="true">
  <div class="mits-dropdown__inner mits-container">

    <!-- Logo -->
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mits-dropdown__logo" aria-label="<?php echo esc_attr( $mits_site_name ); ?>">
      <?php if ( $mits_logo_src ) : ?>
        <img src="<?php echo esc_url( content_url( 'uploads/2026/03/МІТС_logo_header1.png' ) ); ?>"
             alt="<?php echo esc_attr( $mits_site_name ); ?>" height="96" style="width:auto;" />
      <?php endif; ?>
    </a>

    <!-- Top news -->
    <a href="<?php echo esc_url( home_url( $mits_is_en ? '/en/news/' : '/news/' ) ); ?>" class="mits-dropdown__topnews"><?php mits_te( 'nav.top_news' ); ?></a>

    <!-- Phones column -->
    <div class="mits-dropdown__phones-col">
      <?php echo do_shortcode( '[mits_icon_phone]' ); ?>
      <div>
        <?php
        foreach ( array( 'mits_phone_1' => '+380508678855', 'mits_phone_2' => '+380955809561', 'mits_phone_3' => '+380684038934' ) as $mod => $default ) :
            $phone = get_theme_mod( $mod, $default );
            if ( $phone ) :
        ?>
        <a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
        <?php endif; endforeach; ?>
      </div>
    </div>

    <!-- Email + social links -->
    <div class="mits-dropdown__contacts-col">
      <a href="mailto:<?php echo esc_attr( get_theme_mod( 'mits_email', 'vstup.cs@khpi.edu.ua' ) ); ?>" class="mits-dropdown__email">
        <?php echo do_shortcode( '[mits_icon_email]' ); ?>
        <?php echo esc_html( get_theme_mod( 'mits_email', 'vstup.cs@khpi.edu.ua' ) ); ?>
      </a>
      <div class="mits-dropdown__social">
        <?php echo do_shortcode( '[mits_social_tiktok][mits_social_youtube][mits_social_instagram][mits_social_telegram]' ); ?>
      </div>
    </div>

  </div>
</div><!-- #mitsDropdown -->

<script>
(function () {
  var arrow    = document.getElementById('mitsDropdownArrow');
  var dropdown = document.getElementById('mitsDropdown');
  if (!arrow || !dropdown) return;

  var dragStartY = null;
  var isDragging = false;
  var THRESHOLD  = 30;

  function openDD()  { dropdown.classList.add('is-open');    dropdown.setAttribute('aria-hidden','false'); arrow.setAttribute('aria-expanded','true');  }
  function closeDD() { dropdown.classList.remove('is-open'); dropdown.setAttribute('aria-hidden','true');  arrow.setAttribute('aria-expanded','false'); }
  function toggle()  { dropdown.classList.contains('is-open') ? closeDD() : openDD(); }

  arrow.addEventListener('mousedown', function(e) { dragStartY = e.clientY; isDragging = false; e.preventDefault(); });
  document.addEventListener('mousemove', function(e) {
    if (dragStartY === null) return;
    var d = e.clientY - dragStartY;
    if (Math.abs(d) > THRESHOLD) isDragging = true;
    if (d >  THRESHOLD && !dropdown.classList.contains('is-open')) openDD();
    if (d < -THRESHOLD &&  dropdown.classList.contains('is-open')) closeDD();
  });
  document.addEventListener('mouseup', function() {
    if (!isDragging && dragStartY !== null) toggle();
    dragStartY = null; isDragging = false;
  });

  arrow.addEventListener('touchstart', function(e) { dragStartY = e.touches[0].clientY; isDragging = false; }, { passive: true });
  arrow.addEventListener('touchmove', function(e) {
    if (dragStartY === null) return;
    var d = e.touches[0].clientY - dragStartY;
    if (Math.abs(d) > THRESHOLD) isDragging = true;
    if (d >  THRESHOLD && !dropdown.classList.contains('is-open')) openDD();
    if (d < -THRESHOLD &&  dropdown.classList.contains('is-open')) closeDD();
  }, { passive: true });
  arrow.addEventListener('touchend', function() {
    if (!isDragging && dragStartY !== null) toggle();
    dragStartY = null; isDragging = false;
  });

  document.addEventListener('click', function(e) {
    if (dropdown.classList.contains('is-open') && !dropdown.contains(e.target) && !arrow.contains(e.target)) closeDD();
  });

  // Sub-menu toggle on click (mobile / touch)
  document.querySelectorAll('.mits-nav__menu > li').forEach(function(li) {
    var sub = li.querySelector('.sub-menu');
    if (!sub) return;
    li.addEventListener('click', function(e) {
      // don't interfere with sub-menu links themselves
      if (e.target.closest('.sub-menu')) return;
      // on touch devices (no hover): toggle submenu, prevent navigation
      if (window.matchMedia('(hover: none)').matches) {
        e.preventDefault();
        var isOpen = li.classList.toggle('is-open');
        li.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      }
      // on desktop: let the link navigate; CSS hover handles the submenu
    });
  });
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.mits-nav__menu')) {
      document.querySelectorAll('.mits-nav__menu > li.is-open').forEach(function(li) {
        li.classList.remove('is-open');
      });
    }
  });
  var hamburger = document.getElementById('mitsHamburger');
  var navMenu   = document.querySelector('.mits-nav:not(.mits-footer-nav) .mits-nav__menu');
  if (hamburger && navMenu) {
    hamburger.addEventListener('click', function() {
      var isOpen = navMenu.classList.toggle('is-open');
      hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      hamburger.classList.toggle('is-open', isOpen);
    });
    document.addEventListener('click', function(e) {
      if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
        navMenu.classList.remove('is-open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.classList.remove('is-open');
      }
    });
  }
})();
</script>
