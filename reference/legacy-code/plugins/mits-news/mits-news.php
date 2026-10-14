<?php
/**
 * Plugin Name: MITS News
 * Description: Реєстрація типу запису «Новина» з підтримкою Polylang.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: betheme
 *
 * @package MITS_News
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Post Type ───────────────────────────────────────────────────── */

add_action( 'init', function () {
    register_post_type( 'news', array(
        'labels' => array(
            'name'               => __( 'Новини',                      'betheme' ),
            'singular_name'      => __( 'Новина',                      'betheme' ),
            'add_new'            => __( 'Додати новину',                'betheme' ),
            'add_new_item'       => __( 'Додати нову новину',           'betheme' ),
            'edit_item'          => __( 'Редагувати новину',            'betheme' ),
            'new_item'           => __( 'Нова новина',                  'betheme' ),
            'view_item'          => __( 'Переглянути новину',           'betheme' ),
            'view_items'         => __( 'Переглянути новини',           'betheme' ),
            'search_items'       => __( 'Знайти новини',                'betheme' ),
            'not_found'          => __( 'Новин не знайдено',            'betheme' ),
            'not_found_in_trash' => __( 'У кошику новин не знайдено',  'betheme' ),
            'all_items'          => __( 'Всі новини',                   'betheme' ),
            'menu_name'          => __( 'Новини',                       'betheme' ),
            'name_admin_bar'     => __( 'Новина',                       'betheme' ),
        ),
        'public'          => true,
        'has_archive'     => true,
        'rewrite'         => array( 'slug' => 'news', 'with_front' => false ),
        'menu_icon'       => 'dashicons-newspaper',
        'menu_position'   => 5,
        'supports'        => array( 'title', 'editor', 'thumbnail', 'author', 'revisions', 'comments' ),
        'show_in_rest'    => true,
        'taxonomies'      => array( 'category', 'post_tag' ),
    ) );
}, 10 );

/* ── Flush rewrite rules once after CPT registration ────────────── */

add_action( 'init', function () {
    if ( get_option( 'mits_news_cpt_flushed' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'mits_news_cpt_flushed', '1' );
    }
}, 20 );

/* ── Polylang: make "news" posts translatable ────────────────────── */

add_filter( 'pll_get_post_types', function ( array $post_types, bool $is_settings ): array {
    $post_types['news'] = 'news';
    return $post_types;
}, 10, 2 );

/* ── Auto-enable comments for all news posts (respects per-post override) ── */

add_filter( 'comments_open', function ( bool $open, int $post_id ): bool {
    if ( get_post_type( $post_id ) !== 'news' ) {
        return $open;
    }
    // Only force open if the post hasn't been explicitly closed via the edit screen.
    $post = get_post( $post_id );
    if ( $post && $post->comment_status === 'closed' ) {
        return false;
    }
    return true;
}, 10, 2 );
