<?php
/**
 * Plugin Name: MITS Blocks
 * Description: Gutenberg-блоки для МІТС shortcodes — відображає реальний контент прямо в редакторі.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: betheme
 *
 * @package MITS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MITS_BLOCKS_URL',  plugin_dir_url( __FILE__ ) );
define( 'MITS_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );

/* ── Block category ──────────────────────────────────────────────── */

add_filter( 'block_categories_all', function ( array $cats ): array {
    array_unshift( $cats, [
        'slug'  => 'mits',
        'title' => 'МІТС',
        'icon'  => null,
    ] );
    return $cats;
} );

/* ── Register blocks on init ─────────────────────────────────────── */

add_action( 'init', function () {

    wp_register_script(
        'mits-blocks-editor',
        MITS_BLOCKS_URL . 'js/editor.js',
        [
            'wp-blocks',
            'wp-element',
            'wp-server-side-render',
            'wp-block-editor',
            'wp-components',
        ],
        '1.0.0'
    );

    /* ── Address ── */
    register_block_type( 'mits/address', [
        'editor_script'   => 'mits-blocks-editor',
        'render_callback' => function (): string {
            return do_shortcode( '[mits_address]' );
        },
    ] );

    /* ── Phones ── */
    register_block_type( 'mits/phones', [
        'editor_script'   => 'mits-blocks-editor',
        'render_callback' => function (): string {
            return do_shortcode( '[mits_phones]' );
        },
    ] );

    /* ── Email ── */
    register_block_type( 'mits/email', [
        'editor_script'   => 'mits-blocks-editor',
        'render_callback' => function (): string {
            return do_shortcode( '[mits_email]' );
        },
    ] );

    /* ── Social icons ── */
    register_block_type( 'mits/social', [
        'editor_script'   => 'mits-blocks-editor',
        'render_callback' => function (): string {
            return do_shortcode(
                '[mits_social_telegram][mits_social_instagram][mits_social_youtube][mits_social_tiktok]'
            );
        },
    ] );

    /* ── FAQ (with category + count attributes) ── */
    register_block_type( 'mits/faq', [
        'editor_script' => 'mits-blocks-editor',
        'attributes'    => [
            'category' => [ 'type' => 'string', 'default' => '' ],
            'count'    => [ 'type' => 'string', 'default' => '' ],
        ],
        'render_callback' => function ( array $atts ): string {
            $sc = '[mits_faq';
            if ( ! empty( $atts['category'] ) ) {
                $sc .= ' category="' . esc_attr( $atts['category'] ) . '"';
            }
            $count = isset( $atts['count'] ) ? (int) $atts['count'] : 0;
            if ( $count > 0 ) {
                $sc .= ' count="' . $count . '"';
            }
            $sc .= ']';
            return do_shortcode( $sc );
        },
    ] );

} );
