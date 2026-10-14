<?php
/**
 * Допоміжні функції теми.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

// Запасні варіанти, якщо плагін mits-core вимкнено: тема не падає, а показує порожні блоки.
if ( ! function_exists( 'mits_lang' ) ) {
	function mits_lang() {
		return function_exists( 'pll_current_language' ) && pll_current_language() ? pll_current_language() : 'uk';
	}
}
if ( ! function_exists( 'mits_is_en' ) ) {
	function mits_is_en() {
		return 'en' === mits_lang();
	}
}
if ( ! function_exists( 'mits_get_option' ) ) {
	function mits_get_option( $name, $default = '' ) {
		return get_option( $name, $default );
	}
}
if ( ! function_exists( 'mits_get_field' ) ) {
	function mits_get_field( $option, $key, $default = '' ) {
		return $default;
	}
}
if ( ! function_exists( 'mits_get_json_option' ) ) {
	function mits_get_json_option( $name ) {
		$v = json_decode( (string) get_option( $name, '' ), true );
		return is_array( $v ) ? $v : array();
	}
}
if ( ! function_exists( 'mits_get_meta_list' ) ) {
	function mits_get_meta_list( $post_id, $key ) {
		$v = get_post_meta( $post_id, $key, true );
		if ( is_array( $v ) ) {
			return $v;
		}
		$v = json_decode( (string) $v, true );
		return is_array( $v ) ? $v : array();
	}
}
if ( ! function_exists( 'mits_translated_id' ) ) {
	function mits_translated_id( $post_id ) {
		return (int) $post_id;
	}
}
if ( ! function_exists( 'mits_url' ) ) {
	function mits_url( $url ) {
		return $url;
	}
}
if ( ! function_exists( 'mits_icon' ) ) {
	function mits_icon( $name ) {
		return '';
	}
}
if ( ! function_exists( 'mits_social_links' ) ) {
	function mits_social_links( $order = array() ) {
		return '';
	}
}
if ( ! function_exists( 'mits_kses_inline' ) ) {
	function mits_kses_inline() {
		return wp_kses_allowed_html( 'post' );
	}
}

/**
 * Шаблони сторінок зі слайдером угорі.
 */
function mits_hero_templates() {
	return array(
		'template-mits-kafedra.php',
		'template-mits-vstup.php',
		'template-mits-vstup-sub.php',
		'template-mits-osvita.php',
		'template-mits-osvita-sub.php',
		'template-mits-international.php',
		'template-mits-nauka.php',
	);
}

/**
 * Чи є на поточній сторінці слайдер.
 */
function mits_page_has_hero() {
	if ( is_front_page() ) {
		return true;
	}
	if ( ! is_page() ) {
		return false;
	}
	return is_page_template( mits_hero_templates() ) || mits_is_level_page( get_queried_object_id() );
}

/**
 * Сторінки «Бакалавр», «Спеціаліст», «Магістр» у розділі «Вступ» (старі сторінки
 * зі стандартним шаблоном, які виводять слайдер і новини, як підсторінки вступу).
 *
 * @param int $post_id ID сторінки.
 */
function mits_is_level_page( $post_id ) {
	return (bool) get_post_meta( $post_id, '_mits_level_page', true );
}

/**
 * ID сторінки «Контакти» поточною мовою.
 */
function mits_contacts_page_id() {
	$id = (int) get_option( 'mits_contacts_page_id', 0 );
	if ( ! $id ) {
		$page = get_page_by_path( 'контакти' );
		$id   = $page ? $page->ID : 0;
	}
	return $id ? mits_translated_id( $id ) : 0;
}

/**
 * Адреса для перемикача мов: переклад поточної сторінки або головна мови.
 *
 * @param string $lang Код мови.
 */
function mits_lang_switcher_url( $lang ) {
	$home = function_exists( 'pll_home_url' ) ? pll_home_url( $lang ) : home_url( '/' );

	if ( function_exists( 'pll_get_post' ) && ( is_singular() || is_front_page() ) ) {
		$id         = is_front_page() ? (int) get_option( 'page_on_front' ) : get_queried_object_id();
		$translated = pll_get_post( $id, $lang );
		if ( $translated ) {
			return get_permalink( $translated );
		}
		return $home;
	}
	if ( is_post_type_archive() ) {
		$type = get_query_var( 'post_type' );
		$type = is_array( $type ) ? reset( $type ) : $type;
		if ( function_exists( 'PLL' ) && PLL()->links_model ) {
			$link = get_post_type_archive_link( $type );
			return PLL()->links_model->switch_language_in_link( $link, PLL()->model->get_language( $lang ) );
		}
	}
	return $home;
}

/**
 * Посилання на сторінку за шляхом (наприклад «освіта/загальні-положення») поточною мовою.
 *
 * @param string $path Шлях української сторінки.
 */
function mits_page_link_by_path( $path ) {
	$page = get_page_by_path( $path );
	return $page ? get_permalink( mits_translated_id( $page->ID ) ) : '';
}

/**
 * Текст із переносами рядків як у старому шаблоні: HTML дозволено, переноси лишаються.
 *
 * @param string $text Текст.
 */
function mits_kses_text( $text ) {
	return wp_kses( (string) $text, mits_kses_inline() );
}

/**
 * Адреса головної поточною мовою.
 */
function mits_home_url() {
	return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
}

/**
 * Перемикач мов (прапорці UA / GB).
 */
function mits_language_switcher() {
	$current = mits_lang();
	$flags   = array(
		'uk' => '&#x1F1FA;&#x1F1E6;',
		'en' => '&#x1F1EC;&#x1F1E7;',
	);
	echo '<div class="mits-lang-switcher" role="navigation" aria-label="' . esc_attr( mits_t( 'nav.lang_switcher' ) ) . '">' . "\n";
	foreach ( $flags as $lang => $flag ) {
		$active = $lang === $current;
		printf(
			'      <a href="%1$s" class="mits-lang-switcher__btn%2$s" lang="%3$s" hreflang="%3$s" %4$s>%5$s</a>' . "\n",
			esc_url( mits_lang_switcher_url( $lang ) ),
			$active ? ' is-active' : '',
			esc_attr( $lang ),
			$active ? 'aria-current="true"' : '',
			$flag // phpcs:ignore WordPress.Security.EscapeOutput -- HTML-сутності прапорців.
		);
	}
	echo "    </div>\n";
}

/**
 * ID форми Contact Form 7 для поточної мови.
 *
 * Імпорт записує ID у опції mits_cf7_form_uk / mits_cf7_form_en; якщо їх немає,
 * береться форма з відповідною мовою (_locale), а потім будь-яка опублікована.
 */
function mits_contact_form_id() {
	$lang = mits_lang();
	$id   = (int) get_option( 'mits_cf7_form_' . $lang, 0 );
	if ( $id && 'wpcf7_contact_form' === get_post_type( $id ) ) {
		return $id;
	}
	$forms = get_posts( array(
		'post_type'      => 'wpcf7_contact_form',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_locale', 'value' => 'en' === $lang ? 'en_US' : 'uk' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	if ( ! $forms ) {
		$forms = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	}
	return $forms ? (int) $forms[0] : 0;
}

/**
 * Запис «Освітній рівень», що посилається на сторінку (з урахуванням перекладів).
 *
 * @param int $page_id ID сторінки.
 * @return int ID запису або 0.
 */
function mits_level_for_page( $page_id ) {
	$ids = array( (int) $page_id );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$ids = array_merge( $ids, array_map( 'intval', array_values( pll_get_post_translations( $page_id ) ) ) );
	}
	$found = get_posts( array(
		'post_type'      => 'mits_edu_level',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'lang'           => '',
		'meta_query'     => array( array( 'key' => '_edu_level_page_id', 'value' => array_unique( $ids ), 'compare' => 'IN', 'type' => 'NUMERIC' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	if ( ! $found ) {
		return 0;
	}
	// Перевага — запис мовою сторінки.
	$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page_id ) : '';
	foreach ( $found as $id ) {
		if ( ! $lang || ( function_exists( 'pll_get_post_language' ) && pll_get_post_language( $id ) === $lang ) ) {
			return (int) $id;
		}
	}
	return function_exists( 'pll_get_post' ) && pll_get_post( $found[0], $lang ) ? (int) pll_get_post( $found[0], $lang ) : (int) $found[0];
}

/**
 * Умова для запитів новин: без новин із позначкою «Не показувати у списках».
 */
function mits_visible_news_meta_query() {
	return array(
		array(
			'key'     => '_mits_hidden',
			'compare' => 'NOT EXISTS',
		),
	);
}
