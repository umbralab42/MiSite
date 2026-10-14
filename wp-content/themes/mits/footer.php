<?php
/**
 * Підвал: форма «Консультація» (Contact Form 7) і навігація з копірайтом.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

$mits_site_name = get_bloginfo( 'name' );
$mits_logo_id   = get_theme_mod( 'custom_logo' );
$mits_logo_src  = $mits_logo_id ? wp_get_attachment_image_url( $mits_logo_id, 'full' ) : '';
$mits_form_id   = mits_contact_form_id();
?>
<!-- ===================== MITS FOOTER CONTACT FORM ===================== -->
<section class="mits-section mits-form-section mits-footer-form" id="contact-form">
  <div class="mits-container">
    <div class="mits-form-card">
      <h2 class="mits-section__title mits-section__title--small"><?php mits_te( 'footer.form_title' ); ?></h2>
<?php
if ( $mits_form_id && shortcode_exists( 'contact-form-7' ) ) {
	echo do_shortcode( '[contact-form-7 id="' . (int) $mits_form_id . '" html_title="' . esc_attr( mits_t( 'footer.form_title' ) ) . '"]' );
}
?>
    </div>
  </div>
</section>

<!-- ===================== MITS FOOTER NAV ===================== -->
<nav class="mits-nav mits-footer-nav" aria-label="<?php echo esc_attr( mits_t( 'nav.footer_label' ) ); ?>">
  <div class="mits-nav__inner">
    <a href="<?php echo esc_url( mits_home_url() ); ?>" class="mits-nav__logo" aria-label="<?php echo esc_attr( $mits_site_name ); ?> — <?php mits_te( 'nav.home_aria' ); ?>">
<?php if ( $mits_logo_src ) : ?>
              <img src="<?php echo esc_url( $mits_logo_src ); ?>" alt="<?php echo esc_attr( $mits_site_name ); ?>" width="80" height="80" />
<?php endif; ?>
          </a>

    <?php
	wp_nav_menu( array(
		'theme_location' => 'mits-primary',
		'menu_class'     => 'mits-nav__menu',
		'container'      => false,
		'items_wrap'     => '<ul id="mits-footer-menu" class="%2$s" role="list">%3$s</ul>',
		'fallback_cb'    => false,
		'depth'          => 2,
		'mits_footer'    => true,
	) );
	?>
    <!-- Language switcher -->
    <?php mits_language_switcher(); ?>

    <!-- Hamburger (mobile only) -->
    <button
      class="mits-nav__hamburger"
      id="mitsFooterHamburger"
      aria-expanded="false"
      aria-label="<?php echo esc_attr( mits_t( 'nav.hamburger_aria' ) ); ?>"
    >
      <span></span>
      <span></span>
      <span></span>
    </button>

    <a href="#contact-form" class="mits-btn mits-btn--primary mits-nav__cta"><?php mits_te( 'nav.cta' ); ?></a>
  </div>
  <div class="mits-footer-nav__copyright">
    <span>Copyright &copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> &middot; <?php echo esc_html( $mits_site_name ); ?> &middot; <?php mits_te( 'footer.copyright' ); ?></span>
    <span><?php mits_te( 'footer.designed_by' ); ?></span>
  </div>
</nav>

<?php mits_close_wrapper(); ?>

<?php wp_footer(); ?>
</body>
</html>
