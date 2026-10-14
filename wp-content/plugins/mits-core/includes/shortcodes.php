<?php
/**
 * Шорткоди контактів і соцмереж (ті самі назви, що на старому сайті,
 * бо вони вже є у вмісті сторінки «Контакти»).
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Типові кольори іконок (з дизайну) і ключі налаштувань для них.
 */
function mits_icon_default_colors() {
	return array(
		'location'  => '#DF742E',
		'phone'     => '#E8768A',
		'email'     => '#946ff6',
		'telegram'  => '#579de5',
		'instagram' => '#58aba9',
		'youtube'   => '#e662a2',
		'tiktok'    => '#df742e',
	);
}

/**
 * SVG-іконка з кольором фону з налаштувань.
 *
 * @param string $name Назва іконки.
 */
function mits_colored_icon( $name ) {
	$svg      = mits_icon( $name );
	$defaults = mits_icon_default_colors();
	if ( isset( $defaults[ $name ] ) ) {
		$color = mits_get_field( 'mits_contacts', 'icon_bg_' . $name, '' );
		if ( $color && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $color ) && strcasecmp( $color, $defaults[ $name ] ) !== 0 ) {
			$svg = str_ireplace( '"' . $defaults[ $name ] . '"', '"' . $color . '"', $svg );
		}
	}
	return $svg;
}

/**
 * Соцмережі: ключ => [підпис, ключ налаштування, адреса за замовчуванням].
 */
function mits_socials() {
	return array(
		'telegram'  => array( 'Telegram', 'mits_social_telegram', 'https://t.me/STEM_MITS' ),
		'instagram' => array( 'Instagram', 'mits_social_instagram', 'https://www.instagram.com/mits.khpi' ),
		'youtube'   => array( 'YouTube', 'mits_social_youtube', 'https://www.youtube.com/@mitskhpi' ),
		'tiktok'    => array( 'TikTok', 'mits_social_tiktok', 'https://www.tiktok.com/@mits.khpi' ),
	);
}

/**
 * Посилання-іконка соцмережі.
 *
 * @param string $network telegram|instagram|youtube|tiktok.
 */
function mits_social_link( $network ) {
	$socials = mits_socials();
	if ( ! isset( $socials[ $network ] ) ) {
		return '';
	}
	list( $label, $key, $default ) = $socials[ $network ];
	$url = mits_get_field( 'mits_contacts', $key, $default );
	if ( ! $url ) {
		return '';
	}
	return sprintf(
		'<a href="%1$s" class="mits-dropdown__social-link mits-social-icon" aria-label="%2$s" target="_blank" rel="noopener">%3$s</a>',
		esc_url( $url ),
		esc_attr( $label ),
		mits_colored_icon( $network )
	);
}

/**
 * Посилання на соцмережі в заданому порядку.
 *
 * @param array $order Порядок мереж.
 */
if ( ! function_exists( 'mits_social_links' ) ) {
	function mits_social_links( $order = array( 'telegram', 'instagram', 'youtube', 'tiktok' ) ) {
		return implode( '', array_map( 'mits_social_link', $order ) );
	}
}

/**
 * Телефони з налаштувань (порожні пропускаються).
 *
 * @return string[]
 */
function mits_phones() {
	$defaults = array( 1 => '+380508678855', 2 => '+380955809561', 3 => '+380684038934' );
	$phones   = array();
	foreach ( $defaults as $i => $default ) {
		$phone = mits_get_field( 'mits_contacts', 'mits_phone_' . $i, get_option( 'mits_contacts' ) ? '' : $default );
		if ( $phone ) {
			$phones[] = $phone;
		}
	}
	return $phones;
}

/**
 * Посилання tel: для телефону.
 *
 * @param string $phone Номер у будь-якому записі.
 */
function mits_tel_link( $phone ) {
	return sprintf( '<a href="tel:%1$s">%2$s</a>', esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ), esc_html( $phone ) );
}

/**
 * E-mail для відвідувачів.
 */
function mits_contact_email() {
	return mits_get_field( 'mits_contacts', 'mits_email', 'vstup.cs@khpi.edu.ua' );
}

add_action( 'init', function () {
	foreach ( array_keys( mits_icon_default_colors() ) as $icon ) {
		add_shortcode( 'mits_icon_' . $icon, function () use ( $icon ) {
			return mits_colored_icon( $icon );
		} );
	}
	foreach ( array_keys( mits_socials() ) as $network ) {
		add_shortcode( 'mits_social_' . $network, function () use ( $network ) {
			return mits_social_link( $network );
		} );
		add_shortcode( 'mits_link_' . $network, function () use ( $network ) {
			$socials = mits_socials();
			return esc_url( mits_get_field( 'mits_contacts', $socials[ $network ][1], $socials[ $network ][2] ) );
		} );
	}
	add_shortcode( 'mits_address', function () {
		return esc_html( mits_get_field( 'mits_contacts', 'mits_address', '' ) );
	} );
	add_shortcode( 'mits_phones', function () {
		return implode( '', array_map( 'mits_tel_link', mits_phones() ) );
	} );
	add_shortcode( 'mits_email', function () {
		$email = mits_contact_email();
		return $email ? sprintf( '<a href="mailto:%1$s">%2$s</a>', esc_attr( antispambot( $email ) ), esc_html( antispambot( $email ) ) ) : '';
	} );
} );
