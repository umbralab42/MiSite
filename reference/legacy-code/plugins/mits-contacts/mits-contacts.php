<?php
/**
 * Plugin Name: MITS Contacts
 * Description: Контактна інформація та соціальні мережі МІТС — шорткоди, адмін-сторінка налаштувань, мультимовність.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: betheme
 *
 * @package MITS_Contacts
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════════════════════════
   HELPER — language-aware option getter
   ═══════════════════════════════════════════════════════════════════ */

if ( ! function_exists( 'mits_mod' ) ) {
    /**
     * Return an option value with Polylang language awareness.
     * Checks for a lang-suffixed key first (e.g. mits_address_en), falls back to the base key.
     */
    function mits_mod( string $key, string $default = '' ): string {
        $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'uk';
        $opts = get_option( 'mits_contacts', array() );
        if ( $lang !== 'uk' ) {
            $translated = $opts[ $key . '_' . $lang ] ?? '';
            if ( $translated !== '' ) {
                return $translated;
            }
        }
        return $opts[ $key ] ?? $default;
    }
}

/* ═══════════════════════════════════════════════════════════════════
   SVG ICON SHORTCODES — colors editable via admin › Контакти
   ═══════════════════════════════════════════════════════════════════ */

/**
 * Return editable icon background/fill color with fallback to default.
 */
function mits_icon_color( string $key, string $default ): string {
    $opts = get_option( 'mits_contacts', array() );
    $val  = $opts[ 'icon_bg_' . $key ] ?? '';
    return ( $val !== '' && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $val ) ) ? $val : $default;
}

add_shortcode( 'mits_icon_location', function () {
    $c = mits_icon_color( 'location', '#DF742E' );
    return str_replace( '#DF742E', $c,
        '<svg width="42" height="42" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18.229 20.8333C18.229 19.0376 18.9424 17.3154 20.2121 16.0456C21.4819 14.7759 23.2041 14.0625 24.9998 14.0625C26.7956 14.0625 28.5178 14.7759 29.7875 16.0456C31.0573 17.3154 31.7707 19.0376 31.7707 20.8333C31.7707 22.6291 31.0573 24.3513 29.7875 25.621C28.5178 26.8908 26.7956 27.6042 24.9998 27.6042C23.2041 27.6042 21.4819 26.8908 20.2121 25.621C18.9424 24.3513 18.229 22.6291 18.229 20.8333Z" fill="#DF742E"/><path fill-rule="evenodd" clip-rule="evenodd" d="M7.86259 18.4938C8.20913 14.3057 10.1172 10.401 13.2084 7.55413C16.2995 4.70729 20.3478 3.12637 24.5501 3.125H25.4501C29.6524 3.12637 33.7007 4.70729 36.7918 7.55413C39.883 10.401 41.7911 14.3057 42.1376 18.4938C42.5221 23.1696 41.0786 27.8124 38.1105 31.4458L28.1251 43.6583C27.7466 44.1216 27.2699 44.4949 26.7294 44.7512C26.189 45.0075 25.5983 45.1405 25.0001 45.1405C24.4019 45.1405 23.8112 45.0075 23.2708 44.7512C22.7303 44.4949 22.2536 44.1216 21.8751 43.6583L11.8918 31.4458C8.92293 27.8127 7.47863 23.1699 7.86259 18.4938ZM25.0001 10.9375C22.3756 10.9375 19.8585 11.9801 18.0027 13.8359C16.1469 15.6918 15.1043 18.2088 15.1043 20.8333C15.1043 23.4579 16.1469 25.9749 18.0027 27.8307C19.8585 29.6866 22.3756 30.7292 25.0001 30.7292C27.6246 30.7292 30.1417 29.6866 31.9975 27.8307C33.8533 25.9749 34.8959 23.4579 34.8959 20.8333C34.8959 18.2088 33.8533 15.6918 31.9975 13.8359C30.1417 11.9801 27.6246 10.9375 25.0001 10.9375Z" fill="#DF742E"/></svg>'
    );
} );

add_shortcode( 'mits_icon_phone', function () {
    $c = mits_icon_color( 'phone', '#E8768A' );
    return str_replace( '#E8768A', $c,
        '<svg width="42" height="42" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="22" height="22" rx="6" fill="#E8768A"/><path d="M15.51 13.06c-.84 0-1.66-.14-2.42-.38a.67.67 0 0 0-.69.16l-1.08 1.35c-1.94-.93-3.75-2.67-4.72-4.68l1.34-1.14c.18-.19.24-.46.16-.7-.25-.77-.38-1.59-.38-2.42 0-.37-.31-.68-.68-.68H4.82C4.45 4.57 4 4.74 4 5.24 4 12.03 8.97 17 15.76 17c.49 0 .68-.43.68-.81v-2.45c0-.37-.31-.68-.93-.68z" fill="white"/></svg>'
    );
} );

add_shortcode( 'mits_icon_email', function () {
    $c = mits_icon_color( 'email', '#946ff6' );
    return str_replace( '#946ff6', $c,
        '<svg width="42" height="42" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="22" height="22" rx="6" fill="#946ff6"/><path d="M16 7H6c-.75 0-1.33.59-1.33 1.33l-.01 6.67c0 .74.59 1.33 1.34 1.33h10c.74 0 1.33-.59 1.33-1.33V8.33C17.33 7.59 16.74 7 16 7zm0 2.67L11 12.33 6 9.67V8.33l5 2.67 5-2.67v1.34z" fill="white"/></svg>'
    );
} );

add_shortcode( 'mits_icon_tiktok', function () {
    $c = mits_icon_color( 'tiktok', '#df742e' );
    return str_replace( '#df742e', $c,
        '<svg width="42" height="42" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="26" height="26" rx="7" fill="#df742e"/><path d="M18 9.2a3.8 3.8 0 0 1-3.8-3.8H12v10.7a1.67 1.67 0 1 1-1.67-1.67c.15 0 .3.02.44.05V12.1a4.03 4.03 0 1 0 4.1 4.03v-5.56a6.15 6.15 0 0 0 3.57 1.07V9.34A3.8 3.8 0 0 1 18 9.2z" fill="white"/></svg>'
    );
} );

add_shortcode( 'mits_icon_youtube', function () {
    $c = mits_icon_color( 'youtube', '#e662a2' );
    return str_replace( '#e662a2', $c,
        '<svg width="42" height="42" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="26" height="26" rx="7" fill="#e662a2"/><path d="M19.6 9.6a1.73 1.73 0 0 0-1.22-1.22C17.3 8 13 8 13 8s-4.3 0-5.38.38A1.73 1.73 0 0 0 6.4 9.6C6 10.67 6 13 6 13s0 2.33.4 3.4a1.73 1.73 0 0 0 1.22 1.22C8.7 18 13 18 13 18s4.3 0 5.38-.38a1.73 1.73 0 0 0 1.22-1.22C20 15.33 20 13 20 13s0-2.33-.4-3.4zm-8.1 5.5V10.9l3.6 2.1-3.6 2.1z" fill="white"/></svg>'
    );
} );

add_shortcode( 'mits_icon_instagram', function () {
    $c = mits_icon_color( 'instagram', '#58aba9' );
    return str_replace( '#58aba9', $c,
        '<svg width="42" height="42" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="26" height="26" rx="7" fill="#58aba9"/><path d="M13 8c-1.36 0-1.53.01-2.06.03-1.58.07-2.46.96-2.53 2.53C8.01 11.1 8 11.27 8 12.63s.01 1.53.03 2.06c.07 1.57.95 2.46 2.53 2.53.53.02.7.03 2.06.03s1.53-.01 2.06-.03c1.57-.07 2.46-.96 2.53-2.53.02-.53.03-.7.03-2.06s-.01-1.53-.03-2.06C16.55 8.97 15.67 8.08 14.06 8c-.53-.02-.7-.03-2.06-.03zm0 1.35c1.34 0 1.5.01 2.02.03 1.35.06 1.77.5 1.83 1.83.02.52.03.68.03 2.02s-.01 1.5-.03 2.02c-.06 1.35-.49 1.77-1.83 1.83-.52.02-.68.03-2.02.03s-1.5-.01-2.02-.03c-1.35-.06-1.77-.49-1.83-1.83-.02-.52-.03-.68-.03-2.02s.01-1.5.03-2.02c.06-1.34.49-1.77 1.83-1.83.52-.02.68-.03 2.02-.03zm0 2.3a2.35 2.35 0 1 0 0 4.7 2.35 2.35 0 0 0 0-4.7zm0 3.88a1.52 1.52 0 1 1 0-3.05 1.52 1.52 0 0 1 0 3.05zm2.44-4.17a.55.55 0 1 0 0 1.1.55.55 0 0 0 0-1.1z" fill="white"/></svg>'
    );
} );

add_shortcode( 'mits_icon_telegram', function () {
    $c = mits_icon_color( 'telegram', '#579de5' );
    return str_replace( '#579de5', $c,
        '<svg width="42" height="42" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="13" cy="13" r="13" fill="#579de5"/><path d="M6.5 12.7 18 8l-2.05 9.7c-.15.67-.55.84-1.1.52l-3-2.21-1.44 1.38c-.16.16-.3.3-.6.3l.22-3.07L16.5 9.8c.24-.22-.06-.34-.38-.12L7.3 14.68l-2.93-.92c-.64-.2-.65-.64.14-.95z" fill="white"/></svg>'
    );
} );

/* ═══════════════════════════════════════════════════════════════════
   CONTACT DATA SHORTCODES
   ═══════════════════════════════════════════════════════════════════ */

add_shortcode( 'mits_address', function () {
    return esc_html( mits_mod( 'mits_address', 'Харків: вул. Кирпичова, 2, НТУ «ХПІ», корпус У2, к.506.' ) );
} );

add_shortcode( 'mits_phones', function () {
    $phones = array(
        mits_mod( 'mits_phone_1', '+380508678855' ),
        mits_mod( 'mits_phone_2', '+380955809561' ),
        mits_mod( 'mits_phone_3', '+380684038934' ),
    );
    $out = '';
    foreach ( $phones as $p ) {
        if ( $p ) {
            $out .= '<a href="tel:' . esc_attr( preg_replace( '/[^+\d]/', '', $p ) ) . '">' . esc_html( $p ) . '</a>';
        }
    }
    return $out;
} );

add_shortcode( 'mits_email', function () {
    $email = mits_mod( 'mits_email', 'vstup.cs@khpi.edu.ua' );
    return '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
} );

add_shortcode( 'mits_link_tiktok', function () {
    return esc_url( mits_mod( 'mits_social_tiktok', 'https://www.tiktok.com/' ) );
} );
add_shortcode( 'mits_link_youtube', function () {
    return esc_url( mits_mod( 'mits_social_youtube', 'https://www.youtube.com/' ) );
} );
add_shortcode( 'mits_link_instagram', function () {
    return esc_url( mits_mod( 'mits_social_instagram', 'https://www.instagram.com/' ) );
} );
add_shortcode( 'mits_link_telegram', function () {
    return esc_url( mits_mod( 'mits_social_telegram', 'https://t.me/' ) );
} );

/* Google Maps iframe shortcode */
add_shortcode( 'mits_map_iframe', function ( $atts ) {
    $a = shortcode_atts( array( 'height' => '420', 'zoom' => '17' ), $atts );
    $lat = mits_mod( 'mits_map_lat', '49.99935' );
    $lng = mits_mod( 'mits_map_lng', '36.25215' );
    $src = 'https://maps.google.com/maps?q=' . urlencode( $lat . ',' . $lng ) . '&z=' . intval( $a['zoom'] ) . '&output=embed';
    return '<iframe src="' . esc_url( $src ) . '" width="100%" height="' . intval( $a['height'] ) . '" style="border:0;border-radius:12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
} );

/* Social icon anchors */
add_shortcode( 'mits_social_telegram', function () {
    $url = do_shortcode( '[mits_link_telegram]' );
    return '<a href="' . esc_url( $url ) . '" class="mits-dropdown__social-link mits-social-icon" aria-label="Telegram" target="_blank" rel="noopener">'
        . do_shortcode( '[mits_icon_telegram]' ) . '</a>';
} );
add_shortcode( 'mits_social_instagram', function () {
    $url = do_shortcode( '[mits_link_instagram]' );
    return '<a href="' . esc_url( $url ) . '" class="mits-dropdown__social-link mits-social-icon" aria-label="Instagram" target="_blank" rel="noopener">'
        . do_shortcode( '[mits_icon_instagram]' ) . '</a>';
} );
add_shortcode( 'mits_social_youtube', function () {
    $url = do_shortcode( '[mits_link_youtube]' );
    return '<a href="' . esc_url( $url ) . '" class="mits-dropdown__social-link mits-social-icon" aria-label="YouTube" target="_blank" rel="noopener">'
        . do_shortcode( '[mits_icon_youtube]' ) . '</a>';
} );
add_shortcode( 'mits_social_tiktok', function () {
    $url = do_shortcode( '[mits_link_tiktok]' );
    return '<a href="' . esc_url( $url ) . '" class="mits-dropdown__social-link mits-social-icon" aria-label="TikTok" target="_blank" rel="noopener">'
        . do_shortcode( '[mits_icon_tiktok]' ) . '</a>';
} );

/* ═══════════════════════════════════════════════════════════════════
   ADMIN SETTINGS PAGE
   ═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', function () {
    add_submenu_page(
        'mits-settings',
        'МІТС — Контакти',
        'Контакти',
        'manage_options',
        'mits-contacts',
        'mits_contacts_render_page'
    );
}, 999 );

add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
    // Load color picker only on the Contacts settings page.
    if ( $hook !== 'mits-settings_page_mits-contacts' ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_add_inline_script(
        'wp-color-picker',
        'jQuery(function($){ $(".mits-icon-color-picker").wpColorPicker(); });'
    );
} );

add_action( 'admin_init', function () {
    register_setting( 'mits_contacts_group', 'mits_contacts', array(
        'type'              => 'array',
        'sanitize_callback' => 'mits_contacts_sanitize',
    ) );
} );

function mits_contacts_sanitize( $input ): array {
    $clean = array();
    $text_fields = array(
        'mits_address', 'mits_address_en',
        'mits_phone_1', 'mits_phone_1_en',
        'mits_phone_2', 'mits_phone_2_en',
        'mits_phone_3', 'mits_phone_3_en',
        'mits_map_lat', 'mits_map_lng',
    );
    foreach ( $text_fields as $f ) {
        $clean[ $f ] = isset( $input[ $f ] ) ? sanitize_text_field( $input[ $f ] ) : '';
    }

    $email_fields = array( 'mits_email', 'mits_email_en' );
    foreach ( $email_fields as $f ) {
        $clean[ $f ] = isset( $input[ $f ] ) ? sanitize_email( $input[ $f ] ) : '';
    }

    $url_fields = array(
        'mits_social_tiktok', 'mits_social_youtube',
        'mits_social_instagram', 'mits_social_telegram',
    );
    foreach ( $url_fields as $f ) {
        $clean[ $f ] = isset( $input[ $f ] ) ? esc_url_raw( $input[ $f ] ) : '';
    }

    $icon_bg_fields = array(
        'icon_bg_location', 'icon_bg_phone', 'icon_bg_email',
        'icon_bg_telegram', 'icon_bg_instagram', 'icon_bg_youtube', 'icon_bg_tiktok',
    );
    foreach ( $icon_bg_fields as $f ) {
        $val = sanitize_text_field( $input[ $f ] ?? '' );
        $clean[ $f ] = preg_match( '/^#[0-9a-fA-F]{3,6}$/', $val ) ? $val : '';
    }

    return $clean;
}

function mits_contacts_render_page(): void {
    $opts = get_option( 'mits_contacts', array() );
    ?>
    <div class="wrap">
        <h1>МІТС — Контактна інформація</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'mits_contacts_group' ); ?>

            <h2>Адреса та телефони (UK)</h2>
            <table class="form-table">
                <tr>
                    <th><label for="mits_address">Адреса</label></th>
                    <td><input type="text" id="mits_address" name="mits_contacts[mits_address]" value="<?php echo esc_attr( $opts['mits_address'] ?? '' ); ?>" class="large-text"></td>
                </tr>
                <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                <tr>
                    <th><label for="mits_phone_<?php echo $i; ?>">Телефон <?php echo $i; ?></label></th>
                    <td><input type="text" id="mits_phone_<?php echo $i; ?>" name="mits_contacts[mits_phone_<?php echo $i; ?>]" value="<?php echo esc_attr( $opts[ 'mits_phone_' . $i ] ?? '' ); ?>" class="regular-text"></td>
                </tr>
                <?php endfor; ?>
                <tr>
                    <th><label for="mits_email">Email</label></th>
                    <td><input type="email" id="mits_email" name="mits_contacts[mits_email]" value="<?php echo esc_attr( $opts['mits_email'] ?? '' ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>English translations</h2>
            <p class="description">Порожнє поле — буде використано українське значення.</p>
            <table class="form-table">
                <tr>
                    <th><label for="mits_address_en">Address (EN)</label></th>
                    <td><input type="text" id="mits_address_en" name="mits_contacts[mits_address_en]" value="<?php echo esc_attr( $opts['mits_address_en'] ?? '' ); ?>" class="large-text"></td>
                </tr>
                <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                <tr>
                    <th><label for="mits_phone_<?php echo $i; ?>_en">Phone <?php echo $i; ?> (EN)</label></th>
                    <td><input type="text" id="mits_phone_<?php echo $i; ?>_en" name="mits_contacts[mits_phone_<?php echo $i; ?>_en]" value="<?php echo esc_attr( $opts[ 'mits_phone_' . $i . '_en' ] ?? '' ); ?>" class="regular-text"></td>
                </tr>
                <?php endfor; ?>
                <tr>
                    <th><label for="mits_email_en">Email (EN)</label></th>
                    <td><input type="email" id="mits_email_en" name="mits_contacts[mits_email_en]" value="<?php echo esc_attr( $opts['mits_email_en'] ?? '' ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>Карта</h2>
            <table class="form-table">
                <tr>
                    <th><label for="mits_map_lat">Широта (lat)</label></th>
                    <td><input type="text" id="mits_map_lat" name="mits_contacts[mits_map_lat]" value="<?php echo esc_attr( $opts['mits_map_lat'] ?? '' ); ?>" class="regular-text" placeholder="49.99935"></td>
                </tr>
                <tr>
                    <th><label for="mits_map_lng">Довгота (lng)</label></th>
                    <td><input type="text" id="mits_map_lng" name="mits_contacts[mits_map_lng]" value="<?php echo esc_attr( $opts['mits_map_lng'] ?? '' ); ?>" class="regular-text" placeholder="36.25215"></td>
                </tr>
            </table>

            <h2>Соціальні мережі</h2>
            <table class="form-table">
                <?php
                $socials = array(
                    'mits_social_telegram'  => 'Telegram URL',
                    'mits_social_instagram' => 'Instagram URL',
                    'mits_social_youtube'   => 'YouTube URL',
                    'mits_social_tiktok'    => 'TikTok URL',
                );
                foreach ( $socials as $key => $label ) : ?>
                <tr>
                    <th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
                    <td><input type="url" id="<?php echo esc_attr( $key ); ?>" name="mits_contacts[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $opts[ $key ] ?? '' ); ?>" class="large-text"></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h2>Кольори іконок</h2>
            <p class="description">Колір фону / заливки кожної іконки.</p>
            <table class="form-table">
                <?php
                $icon_bg_defs = array(
                    'icon_bg_location'  => array( 'label' => 'Локація',   'default' => '#DF742E' ),
                    'icon_bg_phone'     => array( 'label' => 'Телефон',   'default' => '#E8768A' ),
                    'icon_bg_email'     => array( 'label' => 'Email',     'default' => '#946ff6' ),
                    'icon_bg_telegram'  => array( 'label' => 'Telegram',  'default' => '#579de5' ),
                    'icon_bg_instagram' => array( 'label' => 'Instagram', 'default' => '#58aba9' ),
                    'icon_bg_youtube'   => array( 'label' => 'YouTube',   'default' => '#e662a2' ),
                    'icon_bg_tiktok'    => array( 'label' => 'TikTok',    'default' => '#df742e' ),
                );
                foreach ( $icon_bg_defs as $key => $cfg ) :
                    $val = $opts[ $key ] ?? $cfg['default'];
                ?>
                <tr>
                    <th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $cfg['label'] ); ?></label></th>
                    <td>
                        <input type="text"
                               id="<?php echo esc_attr( $key ); ?>"
                               name="mits_contacts[<?php echo esc_attr( $key ); ?>]"
                               value="<?php echo esc_attr( $val ); ?>"
                               class="mits-icon-color-picker"
                               data-default-color="<?php echo esc_attr( $cfg['default'] ); ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════════════════════════
   MIGRATION — import theme_mod values into the options table (once)
   ═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_init', function () {
    if ( get_option( 'mits_contacts_migrated' ) ) {
        return;
    }

    $keys = array(
        'mits_address', 'mits_address_en',
        'mits_phone_1', 'mits_phone_1_en',
        'mits_phone_2', 'mits_phone_2_en',
        'mits_phone_3', 'mits_phone_3_en',
        'mits_email',   'mits_email_en',
        'mits_social_tiktok', 'mits_social_youtube',
        'mits_social_instagram', 'mits_social_telegram',
    );

    $opts = get_option( 'mits_contacts', array() );
    $changed = false;

    foreach ( $keys as $key ) {
        if ( ! empty( $opts[ $key ] ) ) {
            continue; // already has a value
        }
        $val = get_theme_mod( $key, '' );
        if ( $val !== '' ) {
            $opts[ $key ] = $val;
            $changed = true;
        }
    }

    if ( $changed ) {
        update_option( 'mits_contacts', $opts );
    }
    update_option( 'mits_contacts_migrated', '1' );
} );
