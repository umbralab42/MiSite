<?php
/**
 * Шапка: навігація, кнопка-стрілка й випадна панель з контактами.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

$mits_site_name = get_bloginfo( 'name' );
$mits_logo_id   = get_theme_mod( 'custom_logo' );
$mits_logo_src  = $mits_logo_id ? wp_get_attachment_image_url( $mits_logo_id, 'full' ) : '';
$mits_dd_logo   = mits_get_option( 'mits_dropdown_logo', '' );
$mits_is_en     = mits_is_en();
$mits_email     = function_exists( 'mits_contact_email' ) ? mits_contact_email() : '';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: dark)">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ===================== MITS NAV ===================== -->
<nav class="mits-nav" aria-label="<?php echo esc_attr( mits_t( 'nav.label' ) ); ?>">
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
		'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
		'fallback_cb'    => false,
		'depth'          => 2,
	) );
	?>
    <!-- Language switcher -->
    <?php mits_language_switcher(); ?>
    <!-- Hamburger (mobile only) -->
    <button
      class="mits-nav__hamburger"
      id="mitsHamburger"
      aria-expanded="false"
      aria-label="<?php echo esc_attr( mits_t( 'nav.hamburger_aria' ) ); ?>"
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
  aria-label="<?php echo esc_attr( mits_t( 'nav.arrow_aria' ) ); ?>"
>
  <?php echo mits_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput -- статичний SVG. ?>
</button>
<!-- ===================== DROPDOWN ===================== -->
<div class="mits-dropdown" id="mitsDropdown" aria-hidden="true">
  <div class="mits-dropdown__inner mits-container">
    <!-- Logo -->
    <a href="<?php echo esc_url( mits_home_url() ); ?>" class="mits-dropdown__logo" aria-label="<?php echo esc_attr( $mits_site_name ); ?>">
<?php if ( $mits_dd_logo ) : ?>
              <img src="<?php echo esc_url( mits_url( $mits_dd_logo ) ); ?>"
             alt="<?php echo esc_attr( $mits_site_name ); ?>" height="96" style="width:auto;" />
<?php endif; ?>
          </a>
    <!-- Top news -->
    <a href="<?php echo esc_url( (string) get_post_type_archive_link( 'news' ) ); ?>" class="mits-dropdown__topnews"><?php mits_te( 'nav.top_news' ); ?></a>
    <!-- Phones column -->
    <div class="mits-dropdown__phones-col">
      <?php echo mits_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <div>
<?php foreach ( ( function_exists( 'mits_phones' ) ? mits_phones() : array() ) as $mits_phone ) : ?>
                <a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $mits_phone ) ); ?>"><?php echo esc_html( $mits_phone ); ?></a>
<?php endforeach; ?>
              </div>
    </div>
    <!-- Email + social links -->
    <div class="mits-dropdown__contacts-col">
<?php if ( $mits_email ) : ?>
      <a href="mailto:<?php echo esc_attr( $mits_email ); ?>" class="mits-dropdown__email">
        <?php echo mits_icon( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo esc_html( $mits_email ); ?>
      </a>
<?php endif; ?>
      <div class="mits-dropdown__social">
        <?php echo mits_social_links( array( 'tiktok', 'youtube', 'instagram', 'telegram' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
    </div>
  </div>
</div><!-- #mitsDropdown -->

<div id="Wrapper">
