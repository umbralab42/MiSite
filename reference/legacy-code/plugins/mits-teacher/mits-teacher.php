<?php
/**
 * Plugin Name: MITS Teacher
 * Description: Реєстрація типу запису «Викладач» з мета-полями, Polylang та адмін-колонками.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: betheme
 *
 * @package MITS_Teacher
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Post Type ───────────────────────────────────────────────────── */

add_action( 'init', function () {
    register_post_type( 'teacher', array(
        'labels' => array(
            'name'               => __( 'Викладачі',                 'betheme' ),
            'singular_name'      => __( 'Викладач',                  'betheme' ),
            'add_new'            => __( 'Додати викладача',           'betheme' ),
            'add_new_item'       => __( 'Додати нового викладача',    'betheme' ),
            'edit_item'          => __( 'Редагувати викладача',       'betheme' ),
            'new_item'           => __( 'Новий викладач',             'betheme' ),
            'view_item'          => __( 'Переглянути викладача',      'betheme' ),
            'search_items'       => __( 'Знайти викладача',           'betheme' ),
            'not_found'          => __( 'Викладачів не знайдено',     'betheme' ),
            'not_found_in_trash' => __( 'У кошику викладачів немає', 'betheme' ),
            'menu_name'          => __( 'Викладачі',                  'betheme' ),
        ),
        'public'          => true,
        'has_archive'     => true,
        'rewrite'         => array( 'slug' => 'teachers' ),
        'menu_icon'       => 'dashicons-welcome-learn-more',
        'menu_position'   => 5,
        'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
        'show_in_rest'    => true,
    ) );
}, 10 );

/* ── Flush rewrite rules once after CPT registration ────────────── */

add_action( 'init', function () {
    if ( get_option( 'mits_teacher_cpt_flushed' ) !== '1' ) {
        flush_rewrite_rules();
        update_option( 'mits_teacher_cpt_flushed', '1' );
    }
}, 20 );

/* ── Meta Box ────────────────────────────────────────────────────── */

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'teacher_details',
        __( 'Дані викладача', 'betheme' ),
        'mits_teacher_meta_box_html',
        'teacher',
        'normal',
        'high'
    );
} );

function mits_teacher_meta_box_html( $post ) {
    wp_nonce_field( 'mits_teacher_save', 'mits_teacher_nonce' );
    $position     = get_post_meta( $post->ID, '_teacher_position',    true );
    $grade        = get_post_meta( $post->ID, '_teacher_grade',       true );
    $description  = get_post_meta( $post->ID, '_teacher_description', true );
    $publications = get_post_meta( $post->ID, '_teacher_publications', true ) ?: '';
    $subjects_raw = get_post_meta( $post->ID, '_teacher_subjects',    true );
    $subjects     = is_array( $subjects_raw ) ? $subjects_raw : array();
    ?>
    <style>
        .mits-meta table { width:100%; border-collapse:collapse; }
        .mits-meta th { text-align:left; padding:6px 8px; width:160px; vertical-align:top; font-weight:600; }
        .mits-meta td { padding:6px 8px; }
        .mits-meta input[type=text], .mits-meta textarea { width:100%; }
        .mits-list-item { display:flex; gap:6px; margin-bottom:6px; }
        .mits-list-item input { flex:1; }
        .mits-remove-row { color:#b00; cursor:pointer; background:none; border:none; font-size:18px; line-height:1; }
        .mits-add-row { margin-top:4px; }
    </style>
    <div class="mits-meta">
    <table>
        <tr>
            <th><label for="teacher_description"><?php esc_html_e( 'Опис', 'betheme' ); ?></label></th>
            <td><textarea id="teacher_description" name="teacher_description" rows="5" style="width:100%;"><?php echo esc_textarea( $description ); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="teacher_position"><?php esc_html_e( 'Посада', 'betheme' ); ?></label></th>
            <td><input type="text" id="teacher_position" name="teacher_position"
                       value="<?php echo esc_attr( $position ); ?>"></td>
        </tr>
        <tr>
            <th><label for="teacher_grade"><?php esc_html_e( 'Звання', 'betheme' ); ?></label></th>
            <td><input type="text" id="teacher_grade" name="teacher_grade"
                       value="<?php echo esc_attr( $grade ); ?>"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Предмети', 'betheme' ); ?></th>
            <td>
                <div id="mits-subjects-list">
                <?php foreach ( $subjects as $s ) : ?>
                    <div class="mits-list-item">
                        <input type="text" name="teacher_subjects[]" value="<?php echo esc_attr( $s ); ?>">
                        <button type="button" class="mits-remove-row" title="<?php esc_attr_e( 'Видалити', 'betheme' ); ?>">&times;</button>
                    </div>
                <?php endforeach; ?>
                </div>
                <button type="button" class="button mits-add-row" data-target="mits-subjects-list" data-name="teacher_subjects[]"><?php esc_html_e( '+ Додати предмет', 'betheme' ); ?></button>
            </td>
        </tr>
        <tr>
            <th><label for="teacher_publications"><?php esc_html_e( 'Публікації (URL)', 'betheme' ); ?></label></th>
            <td><input type="url" id="teacher_publications" name="teacher_publications"
                       value="<?php echo esc_attr( $publications ); ?>" placeholder="https://"></td>
        </tr>
    </table>
    </div>
    <script>
    (function(){
        document.querySelectorAll('.mits-add-row').forEach(function(btn){
            btn.addEventListener('click', function(){
                var list = document.getElementById(btn.dataset.target);
                var row  = document.createElement('div');
                row.className = 'mits-list-item';
                row.innerHTML = '<input type="text" name="' + btn.dataset.name + '"><button type="button" class="mits-remove-row" title="<?php esc_attr_e( 'Видалити', 'betheme' ); ?>">&times;</button>';
                list.appendChild(row);
                row.querySelector('.mits-remove-row').addEventListener('click', function(){ row.remove(); });
            });
        });
        document.querySelectorAll('.mits-remove-row').forEach(function(btn){
            btn.addEventListener('click', function(){ btn.closest('.mits-list-item').remove(); });
        });
    })();
    </script>
    <?php
}

/* ── Save Meta ───────────────────────────────────────────────────── */

add_action( 'save_post_teacher', function ( $post_id ) {
    if ( ! isset( $_POST['mits_teacher_nonce'] ) ||
         ! wp_verify_nonce( $_POST['mits_teacher_nonce'], 'mits_teacher_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    update_post_meta( $post_id, '_teacher_description',
        sanitize_textarea_field( $_POST['teacher_description'] ?? '' ) );

    update_post_meta( $post_id, '_teacher_position',
        sanitize_text_field( $_POST['teacher_position'] ?? '' ) );

    update_post_meta( $post_id, '_teacher_grade',
        sanitize_text_field( $_POST['teacher_grade'] ?? '' ) );

    $subjects = array_filter( array_map( 'sanitize_text_field', (array) ( $_POST['teacher_subjects'] ?? array() ) ) );
    update_post_meta( $post_id, '_teacher_subjects', array_values( $subjects ) );

    update_post_meta( $post_id, '_teacher_publications',
        esc_url_raw( $_POST['teacher_publications'] ?? '' ) );
} );

/* ── Polylang: make "teacher" posts translatable ─────────────────── */

add_filter( 'pll_get_post_types', function ( array $post_types, bool $is_settings ): array {
    $post_types['teacher'] = 'teacher';
    return $post_types;
}, 10, 2 );

/* ── Admin: add Sort Order column to teacher list ────────────────── */

add_filter( 'manage_teacher_posts_columns', function ( array $columns ): array {
    $columns['teacher_order'] = __( 'Порядок', 'betheme' );
    return $columns;
} );

add_action( 'manage_teacher_posts_custom_column', function ( string $column, int $post_id ): void {
    if ( $column === 'teacher_order' ) {
        echo (int) get_post_field( 'menu_order', $post_id );
    }
}, 10, 2 );

add_filter( 'manage_edit-teacher_sortable_columns', function ( array $columns ): array {
    $columns['teacher_order'] = 'menu_order';
    return $columns;
} );
