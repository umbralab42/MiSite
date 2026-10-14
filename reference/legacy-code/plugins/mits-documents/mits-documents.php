<?php
/**
 * Plugin Name: MITS Documents
 * Description: Бібліотека документів (PDF) з переглядачем та шорткодами [mits_documents] і [mits_document_viewer id=""].
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: mits-documents
 *
 * @package MITS_Documents
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Post Type ───────────────────────────────────────────────────── */

add_action( 'init', function () {
    register_post_type( 'mits_document', array(
        'labels' => array(
            'name'               => __( 'Документи',                  'mits-documents' ),
            'singular_name'      => __( 'Документ',                   'mits-documents' ),
            'add_new'            => __( 'Додати документ',            'mits-documents' ),
            'add_new_item'       => __( 'Додати новий документ',      'mits-documents' ),
            'edit_item'          => __( 'Редагувати документ',        'mits-documents' ),
            'new_item'           => __( 'Новий документ',             'mits-documents' ),
            'view_item'          => __( 'Переглянути документ',       'mits-documents' ),
            'search_items'       => __( 'Знайти документи',           'mits-documents' ),
            'not_found'          => __( 'Документів не знайдено',     'mits-documents' ),
            'not_found_in_trash' => __( 'У кошику документів немає', 'mits-documents' ),
            'all_items'          => __( 'Всі документи',              'mits-documents' ),
            'menu_name'          => __( 'Документи',                  'mits-documents' ),
        ),
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'has_archive'     => false,
        'menu_icon'       => 'dashicons-media-document',
        'menu_position'   => 6,
        'supports'        => array( 'title', 'editor', 'page-attributes' ),
        'show_in_rest'    => true,
    ) );
}, 10 );

/* ── Taxonomy: Document Category ─────────────────────────────────── */

add_action( 'init', function () {
    register_taxonomy( 'mits_doc_category', 'mits_document', array(
        'labels' => array(
            'name'          => __( 'Категорії документів', 'mits-documents' ),
            'singular_name' => __( 'Категорія',            'mits-documents' ),
            'all_items'     => __( 'Всі категорії',        'mits-documents' ),
            'add_new_item'  => __( 'Додати категорію',     'mits-documents' ),
            'edit_item'     => __( 'Редагувати категорію', 'mits-documents' ),
            'menu_name'     => __( 'Категорії',            'mits-documents' ),
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
    if ( get_option( 'mits_documents_cpt_flushed' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'mits_documents_cpt_flushed', '1' );
    }
}, 20 );

/* ── Polylang: make translatable ─────────────────────────────────── */

add_filter( 'pll_get_post_types', function ( array $post_types, bool $is_settings ): array {
    $post_types['mits_document'] = 'mits_document';
    return $post_types;
}, 10, 2 );

/* ── Meta Box: PDF file ──────────────────────────────────────────── */

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'mits_doc_file',
        __( 'PDF файл', 'mits-documents' ),
        'mits_doc_file_meta_box_html',
        'mits_document',
        'side',
        'high'
    );
} );

function mits_doc_file_meta_box_html( WP_Post $post ): void {
    wp_nonce_field( 'mits_doc_save', 'mits_doc_nonce' );
    $file_url = get_post_meta( $post->ID, '_doc_file_url', true );
    ?>
    <div id="mits-doc-file-wrap">
        <input type="hidden" id="mits_doc_file_url" name="mits_doc_file_url"
               value="<?php echo esc_attr( $file_url ); ?>">

        <div id="mits-doc-preview" style="margin-bottom:10px; word-break:break-all; font-size:12px;">
            <?php if ( $file_url ) : ?>
                <a href="<?php echo esc_url( $file_url ); ?>" target="_blank">
                    📄 <?php echo esc_html( basename( $file_url ) ); ?>
                </a>
            <?php endif; ?>
        </div>

        <button type="button" class="button" id="mits-doc-upload-btn">
            <?php echo $file_url
                ? esc_html__( 'Замінити файл', 'mits-documents' )
                : esc_html__( 'Вибрати PDF',    'mits-documents' ); ?>
        </button>
        <?php if ( $file_url ) : ?>
            <button type="button" class="button" id="mits-doc-remove-btn" style="margin-left:4px;">
                <?php esc_html_e( 'Видалити', 'mits-documents' ); ?>
            </button>
        <?php endif; ?>
    </div>

    <script>
    (function ($) {
        var frame;

        $('#mits-doc-upload-btn').on('click', function (e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title:   'Вибрати PDF',
                button:  { text: 'Вибрати' },
                library: { type: 'application/pdf' },
                multiple: false,
            });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                $('#mits_doc_file_url').val(att.url);
                $('#mits-doc-preview').html(
                    '<a href="' + att.url + '" target="_blank">📄 ' + att.filename + '</a>'
                );
                $('#mits-doc-upload-btn').text('Замінити файл');
                if (!$('#mits-doc-remove-btn').length) {
                    $('<button type="button" class="button" id="mits-doc-remove-btn" style="margin-left:4px;">Видалити</button>')
                        .insertAfter('#mits-doc-upload-btn');
                    bindRemove();
                }
            });
            frame.open();
        });

        function bindRemove() {
            $(document).on('click', '#mits-doc-remove-btn', function () {
                $('#mits_doc_file_url').val('');
                $('#mits-doc-preview').html('');
                $('#mits-doc-upload-btn').text('Вибрати PDF');
                $(this).remove();
            });
        }
        bindRemove();
    }(jQuery));
    </script>
    <?php
}

/* ── Save Meta ───────────────────────────────────────────────────── */

add_action( 'save_post_mits_document', function ( int $post_id ): void {
    if ( ! isset( $_POST['mits_doc_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_doc_nonce'] ) ), 'mits_doc_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    $url = isset( $_POST['mits_doc_file_url'] )
        ? esc_url_raw( wp_unslash( $_POST['mits_doc_file_url'] ) )
        : '';
    update_post_meta( $post_id, '_doc_file_url', $url );
} );

/* ── Enqueue WP Media on document edit screen ────────────────────── */

add_action( 'admin_enqueue_scripts', function (): void {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'mits_document' ) {
        wp_enqueue_media();
    }
} );

/* ── Admin columns ───────────────────────────────────────────────── */

add_filter( 'manage_mits_document_posts_columns', function ( array $cols ): array {
    $new = array();
    foreach ( $cols as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['doc_file']      = __( 'PDF',      'mits-documents' );
            $new['doc_shortcode'] = __( 'Шорткод',  'mits-documents' );
        }
    }
    return $new;
} );

add_action( 'manage_mits_document_posts_custom_column', function ( string $col, int $post_id ): void {
    if ( $col === 'doc_file' ) {
        $url = get_post_meta( $post_id, '_doc_file_url', true );
        if ( $url ) {
            printf(
                '<a href="%s" target="_blank">📄 %s</a>',
                esc_url( $url ),
                esc_html( basename( $url ) )
            );
        } else {
            echo '—';
        }
    }

    if ( $col === 'doc_shortcode' ) {
        if ( function_exists( 'pll_get_post_translations' ) ) {
            $translations = pll_get_post_translations( $post_id );
            foreach ( $translations as $lang => $translated_id ) {
                $sc = '[mits_document_viewer id="' . $translated_id . '"]';
                printf(
                    '<div style="margin-bottom:4px;">'
                    . '<span style="font-size:11px;color:#888;font-weight:600;text-transform:uppercase;margin-right:5px;">%3$s</span>'
                    . '<code id="mits-sc-%1$d" style="background:#1e1e1e;color:#7ec8e3;padding:2px 6px;border-radius:4px;font-size:12px;cursor:pointer;user-select:all;" '
                    . 'title="Клікни, щоб скопіювати" onclick="navigator.clipboard.writeText(this.innerText).then(function(){var el=document.getElementById(\'mits-sc-%1$d\');el.style.color=\'#6dbf6d\';setTimeout(function(){el.style.color=\'#7ec8e3\';},1200);})">%2$s</code>'
                    . '</div>',
                    $translated_id,
                    esc_html( $sc ),
                    esc_html( $lang )
                );
            }
        } else {
            $sc = '[mits_document_viewer id="' . $post_id . '"]';
            printf(
                '<code id="mits-sc-%1$d" style="background:#1e1e1e;color:#7ec8e3;padding:3px 7px;border-radius:4px;font-size:12px;cursor:pointer;user-select:all;" '
                . 'title="Клікни, щоб скопіювати" onclick="navigator.clipboard.writeText(this.innerText).then(function(){var el=document.getElementById(\'mits-sc-%1$d\');el.style.color=\'#6dbf6d\';setTimeout(function(){el.style.color=\'#7ec8e3\';},1200);})">%2$s</code>',
                $post_id,
                esc_html( $sc )
            );
        }
    }
}, 10, 2 );

/* ── Shortcode: [mits_documents] ─────────────────────────────────── */
/*
 * Attrs:
 *   category=""   — slug of mits_doc_category
 *   limit=""      — max posts (-1 = all)
 *   orderby=""    — menu_order|date|title
 *   order=""      — ASC|DESC
 */

add_shortcode( 'mits_documents', function ( $atts ): string {
    $atts = shortcode_atts( array(
        'category' => '',
        'limit'    => -1,
        'orderby'  => 'menu_order',
        'order'    => 'ASC',
    ), $atts, 'mits_documents' );

    $args = array(
        'post_type'      => 'mits_document',
        'post_status'    => 'publish',
        'posts_per_page' => intval( $atts['limit'] ),
        'orderby'        => sanitize_key( $atts['orderby'] ),
        'order'          => in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true )
                                ? strtoupper( $atts['order'] ) : 'ASC',
    );

    if ( $atts['category'] ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'mits_doc_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['category'] ),
            ),
        );
    }

    $docs = new WP_Query( $args );
    if ( ! $docs->have_posts() ) {
        return '';
    }

    wp_enqueue_style(
        'mits-documents',
        plugin_dir_url( __FILE__ ) . 'assets/documents.css',
        array(),
        '1.0.0'
    );
    wp_enqueue_script(
        'mits-documents',
        plugin_dir_url( __FILE__ ) . 'assets/documents.js',
        array(),
        '1.0.0',
        true
    );

    ob_start();
    ?>
    <div class="mits-docs">
    <?php
    while ( $docs->have_posts() ) :
        $docs->the_post();
        $file_url = get_post_meta( get_the_ID(), '_doc_file_url', true );
        $terms    = get_the_terms( get_the_ID(), 'mits_doc_category' );
        $cat      = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
        $desc     = get_the_content();
    ?>
        <div class="mits-doc-item">
            <div class="mits-doc-icon" aria-hidden="true">
                <svg width="36" height="44" viewBox="0 0 36 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 0H4C1.8 0 0 1.8 0 4v36C0 42.2 1.8 44 4 44h28c2.2 0 4-1.8 4-4V14L22 0z" fill="#2a2a2a"/>
                    <path d="M22 0v14h14L22 0z" fill="#3a3a3a"/>
                    <rect x="0" y="24" width="36" height="14" rx="2" fill="#df742d"/>
                    <text x="18" y="35" font-size="9" font-family="Arial,sans-serif" font-weight="700" fill="white" text-anchor="middle">PDF</text>
                </svg>
            </div>

            <div class="mits-doc-info">
                <?php if ( $cat ) : ?>
                    <span class="mits-doc-cat"><?php echo esc_html( $cat ); ?></span>
                <?php endif; ?>
                <h3 class="mits-doc-title"><?php the_title(); ?></h3>
                <?php if ( $desc ) : ?>
                    <p class="mits-doc-desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $desc ), 18 ) ); ?></p>
                <?php endif; ?>
            </div>

            <div class="mits-doc-actions">
                <?php if ( $file_url ) : ?>
                    <button class="mits-doc-btn mits-doc-btn--view"
                            data-file="<?php echo esc_attr( $file_url ); ?>"
                            data-title="<?php echo esc_attr( get_the_title() ); ?>">
                        Переглянути
                    </button>
                    <a class="mits-doc-btn mits-doc-btn--download"
                       href="<?php echo esc_url( $file_url ); ?>"
                       download
                       target="_blank">
                        Завантажити
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php
    endwhile;
    wp_reset_postdata();
    ?>
    </div>

    <div id="mits-doc-modal" class="mits-doc-modal" role="dialog" aria-modal="true"
         aria-labelledby="mits-doc-modal-title" hidden>
        <div class="mits-doc-modal__overlay"></div>
        <div class="mits-doc-modal__box">
            <div class="mits-doc-modal__header">
                <span class="mits-doc-modal__title" id="mits-doc-modal-title"></span>
                <button class="mits-doc-modal__close" aria-label="Закрити">&times;</button>
            </div>
            <div class="mits-doc-modal__body">
                <iframe class="mits-doc-modal__frame" src="" title="PDF viewer"></iframe>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean();
} );

/* ── Shortcode: [mits_document_viewer id="123"] ──────────────────── */
/*
 * Embeds a single PDF inline on the page (no modal).
 * Useful for dedicated document pages.
 */

add_shortcode( 'mits_document_viewer', function ( $atts ): string {
    $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'mits_document_viewer' );
    $id   = intval( $atts['id'] );
    if ( ! $id ) {
        return '';
    }
    $file_url = get_post_meta( $id, '_doc_file_url', true );
    if ( ! $file_url ) {
        return '';
    }
    wp_enqueue_style(
        'mits-documents',
        plugin_dir_url( __FILE__ ) . 'assets/documents.css',
        array(),
        '1.0.0'
    );
    $title = esc_attr( get_the_title( $id ) );
    return sprintf(
        '<div class="mits-doc-inline-viewer">'
            . '<iframe src="%1$s" class="mits-doc-inline-frame" title="%2$s"></iframe>'
            . '<div class="mits-doc-inline-fallback">'
                . '<p>Перегляд PDF недоступний на цьому пристрої.</p>'
                . '<div class="mits-doc-fallback-actions">'
                    . '<a class="mits-doc-btn mits-doc-btn--view" href="%1$s" target="_blank" rel="noopener">Відкрити PDF</a>'
                    . '<a class="mits-doc-btn mits-doc-btn--download" href="%1$s" download>Завантажити</a>'
                . '</div>'
            . '</div>'
        . '</div>',
        esc_url( $file_url ),
        $title
    );
} );
