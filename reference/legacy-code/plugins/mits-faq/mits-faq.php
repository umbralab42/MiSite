<?php
/**
 * Plugin Name: MITS FAQ
 * Description: FAQ розділ з категоріями та шорткодом [mits_faq].
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: mits-faq
 *
 * @package MITS_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Post Type ───────────────────────────────────────────────────── */

add_action( 'init', function () {
    register_post_type( 'mits_faq', array(
        'labels' => array(
            'name'               => __( 'FAQ',                         'mits-faq' ),
            'singular_name'      => __( 'Питання FAQ',                 'mits-faq' ),
            'add_new'            => __( 'Додати питання',              'mits-faq' ),
            'add_new_item'       => __( 'Додати нове питання',         'mits-faq' ),
            'edit_item'          => __( 'Редагувати питання',          'mits-faq' ),
            'new_item'           => __( 'Нове питання',                'mits-faq' ),
            'view_item'          => __( 'Переглянути питання',         'mits-faq' ),
            'search_items'       => __( 'Знайти питання',              'mits-faq' ),
            'not_found'          => __( 'Питань не знайдено',          'mits-faq' ),
            'not_found_in_trash' => __( 'У кошику немає питань',      'mits-faq' ),
            'menu_name'          => __( 'FAQ',                         'mits-faq' ),
        ),
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'has_archive'     => false,
        'menu_icon'       => 'dashicons-editor-help',
        'menu_position'   => 7,
        'supports'        => array( 'title', 'page-attributes' ),
        'show_in_rest'    => true,
    ) );
}, 10 );

/* ── Taxonomy: FAQ Category ──────────────────────────────────────── */

add_action( 'init', function () {
    register_taxonomy( 'mits_faq_category', 'mits_faq', array(
        'labels' => array(
            'name'              => __( 'Категорії FAQ',          'mits-faq' ),
            'singular_name'     => __( 'Категорія FAQ',          'mits-faq' ),
            'all_items'         => __( 'Всі категорії',          'mits-faq' ),
            'add_new_item'      => __( 'Додати категорію',        'mits-faq' ),
            'new_item_name'     => __( 'Нова категорія',          'mits-faq' ),
            'edit_item'         => __( 'Редагувати категорію',    'mits-faq' ),
            'search_items'      => __( 'Знайти категорію',        'mits-faq' ),
            'not_found'         => __( 'Категорій не знайдено',   'mits-faq' ),
            'menu_name'         => __( 'Категорії',               'mits-faq' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => false,
    ) );
}, 10 );

/* ── Flush rewrite rules once ────────────────────────────────────── */

add_action( 'init', function () {
    if ( get_option( 'mits_faq_cpt_flushed' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'mits_faq_cpt_flushed', '1' );
    }
}, 20 );

/* ── Meta Box ────────────────────────────────────────────────────── */

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'mits_faq_details',
        __( 'Відповідь', 'mits-faq' ),
        'mits_faq_meta_box_html',
        'mits_faq',
        'normal',
        'high'
    );
} );

function mits_faq_meta_box_html( $post ) {
    wp_nonce_field( 'mits_faq_save', 'mits_faq_nonce' );
    $answer = get_post_meta( $post->ID, '_faq_answer', true );
    ?>
    <style>
        .mits-faq-meta textarea { width: 100%; }
        .mits-faq-meta label    { display: block; margin-bottom: 4px; font-weight: 600; }
        .mits-faq-meta p.hint   { color: #666; font-size: 12px; margin: 4px 0 0; }
    </style>
    <div class="mits-faq-meta">
        <p class="hint"><?php esc_html_e( 'Питання вводиться у полі «Заголовок» вище.', 'mits-faq' ); ?></p>
        <br>
        <label for="faq_answer"><?php esc_html_e( 'Відповідь', 'mits-faq' ); ?></label>
        <textarea id="faq_answer" name="faq_answer" rows="8"><?php echo esc_textarea( $answer ); ?></textarea>
    </div>
    <?php
}

/* ── Save Meta ───────────────────────────────────────────────────── */

add_action( 'save_post_mits_faq', function ( $post_id ) {
    if ( ! isset( $_POST['mits_faq_nonce'] ) ||
         ! wp_verify_nonce( $_POST['mits_faq_nonce'], 'mits_faq_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    update_post_meta(
        $post_id,
        '_faq_answer',
        wp_kses_post( $_POST['faq_answer'] ?? '' )
    );
} );

/* ── Polylang: make "mits_faq" posts & taxonomy translatable ─────── */

add_filter( 'pll_get_post_types', function ( array $post_types, bool $is_settings ): array {
    $post_types['mits_faq'] = 'mits_faq';
    return $post_types;
}, 10, 2 );

add_filter( 'pll_get_taxonomies', function ( array $taxonomies, bool $is_settings ): array {
    $taxonomies['mits_faq_category'] = 'mits_faq_category';
    return $taxonomies;
}, 10, 2 );

/* ── Admin: sort-order column ────────────────────────────────────── */

add_filter( 'manage_mits_faq_posts_columns', function ( array $columns ): array {
    $columns['faq_order'] = __( 'Порядок', 'mits-faq' );
    return $columns;
} );

add_action( 'manage_mits_faq_posts_custom_column', function ( string $column, int $post_id ): void {
    if ( $column === 'faq_order' ) {
        echo (int) get_post_field( 'menu_order', $post_id );
    }
}, 10, 2 );

add_filter( 'manage_edit-mits_faq_sortable_columns', function ( array $columns ): array {
    $columns['faq_order'] = 'menu_order';
    return $columns;
} );

/* ── Shortcode: [mits_faq] ───────────────────────────────────────── */
/*
 * Attributes:
 *   category  — slug of a mits_faq_category term (optional)
 *   count     — max number of items, default -1 (all)
 */

add_shortcode( 'mits_faq', function ( $atts ): string {
    $atts = shortcode_atts( array(
        'category' => '',
        'count'    => -1,
    ), $atts, 'mits_faq' );

    $args = array(
        'post_type'      => 'mits_faq',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $atts['count'],
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );

    if ( $atts['category'] !== '' ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'mits_faq_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['category'] ),
            ),
        );
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '';
    }

    ob_start();
    // Inline styles for block editor (ServerSideRender) — on front-end they are added via wp_head.
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        mits_faq_inline_css();
    }
    ?>
    <div class="mits-faq">
        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <?php
            $answer = get_post_meta( get_the_ID(), '_faq_answer', true );
            ?>
            <details class="mits-faq__item">
                <summary class="mits-faq__question"><?php the_title(); ?></summary>
                <?php if ( $answer ) : ?>
                    <div class="mits-faq__answer">
                        <?php echo wp_kses_post( wpautop( $answer ) ); ?>
                    </div>
                <?php endif; ?>
            </details>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
} );

/* ── Front-end styles (only when shortcode is present) ───────────── */

add_action( 'wp_enqueue_scripts', function () {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mits_faq' ) ) {
        wp_add_inline_style( 'mits-faq-inline', '' ); // register handle first
        wp_enqueue_style( 'mits-faq-inline', false, array(), null );
        add_action( 'wp_head', 'mits_faq_inline_css' );
    }
} );

function mits_faq_inline_css() {
    ?>
    <style id="mits-faq-css">
    .mits-faq { margin: 2em 0; }
    .mits-faq__item {
        border-bottom: 1px solid #e0e0e0;
        padding: 0;
    }
    .mits-faq__item:first-child { border-top: 1px solid #e0e0e0; }
    .mits-faq__question {
        list-style: none;
        cursor: pointer;
        padding: 1em 2em 1em 0;
        font-weight: 600;
        font-size: 1rem;
        position: relative;
        user-select: none;
    }
    .mits-faq__question::-webkit-details-marker { display: none; }
    .mits-faq__question::after {
        content: '+';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.4em;
        font-weight: 400;
        line-height: 1;
        transition: transform .2s;
    }
    .mits-faq__item[open] > .mits-faq__question::after {
        content: '−';
    }
    .mits-faq__answer {
        padding: 0 0 1em;
        line-height: 1.7;
    }
    .mits-faq__answer p:last-child { margin-bottom: 0; }
    </style>
    <?php
}
