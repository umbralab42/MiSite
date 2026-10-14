<?php
/**
 * MITS centralised UI translations.
 *
 * Usage anywhere in the theme:
 *   mits_t( 'nav.cta' )          // returns string
 *   mits_te( 'nav.cta' )         // echoes string (escaped)
 *
 * Add a new language by adding another key to $mits_i18n below.
 * The fallback language is always 'uk' (Ukrainian).
 *
 * @package Betheme
 */

if ( defined( 'MITS_I18N_LOADED' ) ) {
    return;
}
define( 'MITS_I18N_LOADED', true );

global $mits_i18n_strings;

$mits_i18n_strings = array(

    // ── Navigation ────────────────────────────────────────────────
    'nav.home_aria'      => array( 'uk' => 'на головну',           'en' => 'Go to homepage' ),
    'nav.hamburger_aria' => array( 'uk' => 'Відкрити навігацію',   'en' => 'Open navigation' ),
    'nav.arrow_aria'     => array( 'uk' => 'Відкрити меню',        'en' => 'Open menu' ),
    'nav.cta'            => array( 'uk' => 'Консультація',         'en' => 'Consultation' ),
    'nav.top_news'       => array( 'uk' => 'Топ новин',            'en' => 'Top News' ),

    // ── Footer form ───────────────────────────────────────────────
    'footer.form_title'  => array( 'uk' => 'Контактна форма',      'en' => 'Contact Form' ),

    // ── News single ───────────────────────────────────────────────
    'news.back'          => array( 'uk' => '← Всі новини',         'en' => '← All News' ),

    // ── News archive ──────────────────────────────────────────────
    'news.title'         => array( 'uk' => 'Новини',               'en' => 'News' ),
    'news.empty'         => array( 'uk' => 'Новин ще не додано.',  'en' => 'No news yet.' ),

);

/**
 * Return a translated string for the current Polylang language.
 * Falls back to Ukrainian, then to the key itself.
 *
 * @param string $key  Dot-separated key, e.g. 'nav.cta'.
 * @return string
 */
function mits_t( string $key ): string {
    global $mits_i18n_strings;

    if ( ! isset( $mits_i18n_strings[ $key ] ) ) {
        return $key; // unknown key – return as-is so nothing breaks
    }

    $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'uk';

    $translations = $mits_i18n_strings[ $key ];

    return $translations[ $lang ] ?? $translations['uk'] ?? $key;
}

/**
 * Echo a translated string (HTML-escaped).
 *
 * @param string $key
 */
function mits_te( string $key ): void {
    echo esc_html( mits_t( $key ) );
}
