<?php
/**
 * Допоміжні функції: поточна мова, налаштування з урахуванням мови, JSON-поля.
 *
 * Домовленість для двомовних налаштувань: українське значення зберігається під
 * основним ключем (як на старому сайті), англійське — під тим самим ключем із
 * суфіксом «_en». Якщо англійського значення немає, показується українське.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Мови сайту: код Polylang => назва.
 */
function mits_languages() {
	return array(
		'uk' => 'Українська',
		'en' => 'English',
	);
}

/**
 * Поточна мова сторінки (uk за замовчуванням).
 */
if ( ! function_exists( 'mits_lang' ) ) {
	function mits_lang() {
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
			if ( $lang ) {
				return $lang;
			}
		}
		return 'uk';
	}
}

/**
 * Чи поточна мова англійська.
 */
if ( ! function_exists( 'mits_is_en' ) ) {
	function mits_is_en() {
		return 'en' === mits_lang();
	}
}

/**
 * Суфікс ключа для мови: '' для української, '_en' для англійської.
 *
 * @param string|null $lang Код мови; за замовчуванням поточна.
 */
function mits_lang_suffix( $lang = null ) {
	$lang = $lang ? $lang : mits_lang();
	return 'uk' === $lang ? '' : '_' . $lang;
}

/**
 * Налаштування з урахуванням мови: спершу «ключ_en», потім «ключ».
 *
 * @param string $name    Назва опції.
 * @param mixed  $default Значення за замовчуванням.
 * @param string $lang    Код мови (необов'язково).
 */
if ( ! function_exists( 'mits_get_option' ) ) {
	function mits_get_option( $name, $default = '', $lang = null ) {
		$suffix = mits_lang_suffix( $lang );
		if ( '' !== $suffix ) {
			$value = get_option( $name . $suffix, '' );
			if ( '' !== $value && false !== $value && array() !== $value ) {
				return $value;
			}
		}
		$value = get_option( $name, $default );
		return ( false === $value ) ? $default : $value;
	}
}

/**
 * Налаштування-JSON (списки й повторювані блоки) як масив, з урахуванням мови.
 *
 * @param string $name Назва опції.
 * @param string $lang Код мови (необов'язково).
 * @return array
 */
if ( ! function_exists( 'mits_get_json_option' ) ) {
	function mits_get_json_option( $name, $lang = null ) {
		return mits_json_decode( mits_get_option( $name, '', $lang ) );
	}
}

/**
 * Поле з налаштування-масиву (mits_contacts, mits_why…) з урахуванням мови.
 *
 * @param string $option  Назва опції-масиву.
 * @param string $key     Ключ поля.
 * @param string $default Значення за замовчуванням.
 * @param string $lang    Код мови (необов'язково).
 */
if ( ! function_exists( 'mits_get_field' ) ) {
	function mits_get_field( $option, $key, $default = '', $lang = null ) {
		$values = get_option( $option, array() );
		if ( ! is_array( $values ) ) {
			return $default;
		}
		$suffix = mits_lang_suffix( $lang );
		if ( '' !== $suffix && isset( $values[ $key . $suffix ] ) && '' !== $values[ $key . $suffix ] ) {
			return $values[ $key . $suffix ];
		}
		return isset( $values[ $key ] ) && '' !== $values[ $key ] ? $values[ $key ] : $default;
	}
}

/**
 * Безпечне декодування JSON у масив.
 *
 * @param mixed $value Рядок JSON або масив.
 * @return array
 */
function mits_json_decode( $value ) {
	if ( is_array( $value ) ) {
		return $value;
	}
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return array();
	}
	$decoded = json_decode( $value, true );
	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Мета-поле запису у вигляді масиву (поля-списки зберігаються як JSON, як на старому сайті).
 *
 * @param int    $post_id ID запису.
 * @param string $key     Ключ мета-поля.
 * @return array
 */
if ( ! function_exists( 'mits_get_meta_list' ) ) {
	function mits_get_meta_list( $post_id, $key ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( is_array( $value ) ) {
			return $value;
		}
		return mits_json_decode( $value );
	}
}

/**
 * ID запису у перекладі на поточну мову (або сам ID, якщо перекладу немає).
 *
 * @param int    $post_id ID запису.
 * @param string $lang    Код мови (необов'язково).
 */
if ( ! function_exists( 'mits_translated_id' ) ) {
	function mits_translated_id( $post_id, $lang = null ) {
		$post_id = (int) $post_id;
		if ( $post_id && function_exists( 'pll_get_post' ) ) {
			$translated = pll_get_post( $post_id, $lang ? $lang : mits_lang() );
			if ( $translated ) {
				return (int) $translated;
			}
		}
		return $post_id;
	}
}

/**
 * Перетворює відносну адресу «/шлях/» на адресу сайту, решту лишає як є.
 *
 * @param string $url Адреса з налаштувань.
 */
if ( ! function_exists( 'mits_url' ) ) {
	function mits_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}
		if ( '/' === $url[0] && ( ! isset( $url[1] ) || '/' !== $url[1] ) ) {
			return home_url( $url );
		}
		return $url;
	}
}

/**
 * Дозволений HTML для коротких текстів (описи, відповіді FAQ, пункти списків).
 */
if ( ! function_exists( 'mits_kses_inline' ) ) {
	function mits_kses_inline() {
		return array(
			'a'      => array( 'href' => true, 'target' => true, 'rel' => true, 'class' => true, 'title' => true ),
			'br'     => array(),
			'strong' => array(),
			'b'      => array(),
			'em'     => array(),
			'i'      => array(),
			'span'   => array( 'style' => true, 'class' => true ),
			'p'      => array( 'style' => true, 'class' => true ),
			'ul'     => array( 'class' => true ),
			'ol'     => array( 'class' => true ),
			'li'     => array( 'class' => true ),
			'h3'     => array( 'class' => true ),
			'h4'     => array( 'class' => true ),
		);
	}
}
