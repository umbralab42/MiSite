<?php
/**
 * MITS navigation bar for the footer — same as header nav but without the dropdown slider.
 * Included via get_template_part('includes/mits-footer-nav') from footer.php.
 *
 * @package Betheme
 */

$mits_site_name = get_bloginfo( 'name' );
$mits_logo_id   = get_theme_mod( 'custom_logo' );
$mits_logo_src  = $mits_logo_id ? wp_get_attachment_image_url( $mits_logo_id, 'full' ) : '';

// ---------- Language detection via Polylang ----------
if ( ! isset( $mits_is_en ) ) {
	$mits_is_en = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';
}

// Menu slug: use EN menu when available, fall back to UA.
if ( ! isset( $mits_menu_slug ) ) {
	$mits_menu_slug = $mits_is_en && wp_get_nav_menu_object( 'main_menu_mits_en' ) ? 'main_menu_mits_en' : 'main_menu_mits_ua';
}

// CF7 form: look up by _locale meta so IDs work across local and remote envs.
$_cf7_locale  = $mits_is_en ? 'en' : 'uk';
$_cf7_results = get_posts( array(
	'post_type'      => 'wpcf7_contact_form',
	'post_status'    => 'publish',
	'posts_per_page' => 1,
	'meta_query'     => array( array( 'key' => '_locale', 'value' => $_cf7_locale ) ),
) );
$_cf7_id = $_cf7_results ? $_cf7_results[0]->ID : 0;

// Fallback: if no locale-tagged form found, use the first published form.
if ( ! $_cf7_id ) {
	$_cf7_fallback = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'post_status' => 'publish', 'posts_per_page' => 1 ) );
	if ( $_cf7_fallback ) $_cf7_id = $_cf7_fallback[0]->ID;
}

// CF7 form ID (not translatable — just a runtime value)
$mits_footer_form_id = (string) $_cf7_id;

// Language switcher URLs: Polylang translation → URL-prefix swap → language home fallback.
if ( ! isset( $mits_ua_url ) ) {
	$mits_ua_url = mits_lang_switcher_url( 'uk' );
	$mits_en_url = mits_lang_switcher_url( 'en' );
}
?>

<!-- ===================== MITS FOOTER CONTACT FORM ===================== -->
<section class="mits-section mits-form-section mits-footer-form" id="contact-form">
  <div class="mits-container">
    <div class="mits-form-card">
      <h2 class="mits-section__title mits-section__title--small"><?php mits_te( 'footer.form_title' ); ?></h2>
      <?php echo do_shortcode( '[contact-form-7 id="' . esc_attr( $mits_footer_form_id ) . '" title="' . esc_attr( mits_t( 'nav.cta' ) ) . '"]' ); ?>
    </div>
  </div>
</section>

<!-- ===================== MITS FOOTER NAV ===================== -->
<nav class="mits-nav mits-footer-nav" aria-label="Footer navigation">
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
        'items_wrap'     => '<ul id="mits-footer-menu" class="%2$s" role="list">%3$s</ul>',
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
      id="mitsFooterHamburger"
      aria-expanded="false"
      aria-label="<?php mits_te( 'nav.hamburger_aria' ); ?>"
    >
      <span></span>
      <span></span>
      <span></span>
    </button>

    <a href="#contact-form" class="mits-btn mits-btn--primary mits-nav__cta"><?php mits_te( 'nav.cta' ); ?></a>
  </div>
  <div class="mits-footer-nav__copyright">
    <span>Copyright &copy; <?php echo date( 'Y' ); ?> &middot; <?php echo esc_html( get_bloginfo( 'name' ) ); ?> &middot; All rights reserved and designed</span>
    <span>Designed by katrine.gnz</span>
  </div>
</nav>

<script>
(function () {
  var hamburger = document.getElementById('mitsFooterHamburger');
  var navMenu   = document.getElementById('mits-footer-menu');
  if (!hamburger || !navMenu) return;

  hamburger.addEventListener('click', function () {
    var isOpen = navMenu.classList.toggle('is-open');
    hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    hamburger.classList.toggle('is-open', isOpen);
  });

  /* Sub-menu click toggle */
  if (navMenu) {
    navMenu.querySelectorAll('li.menu-item-has-children > a').forEach(function (link) {
      link.addEventListener('click', function (e) {
        var li = link.parentElement;
        var isOpen = li.classList.toggle('is-open');
        /* close siblings */
        li.parentElement.querySelectorAll('li.is-open').forEach(function (other) {
          if (other !== li) other.classList.remove('is-open');
        });
        if (isOpen) e.preventDefault();
      });
    });
  }

  document.addEventListener('click', function (e) {
    if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
      navMenu.classList.remove('is-open');
      hamburger.setAttribute('aria-expanded', 'false');
      hamburger.classList.remove('is-open');
      navMenu.querySelectorAll('li.is-open').forEach(function (li) {
        li.classList.remove('is-open');
      });
    }
  });
})();
</script>
