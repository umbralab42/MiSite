<?php
/**
 * Plugin Name: MITS Освіта
 * Description: CPT «mits_technology» (Технології), мета-поля для сторінок Освіта та підсторінок.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: mits-osvita
 *
 * @package MITS_Osvita
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════════════════════════
   1. CPT mits_technology — Технології
═══════════════════════════════════════════════════════════════════ */

add_action( 'init', function (): void {
    register_post_type(
        'mits_technology',
        array(
            'labels'          => array(
                'name'               => 'Технології',
                'singular_name'      => 'Технологія',
                'add_new'            => 'Додати технологію',
                'add_new_item'       => 'Нова технологія',
                'edit_item'          => 'Редагувати технологію',
                'all_items'          => 'Всі технології',
                'search_items'       => 'Пошук технологій',
                'not_found'          => 'Технологій не знайдено',
                'not_found_in_trash' => 'Кошик порожній',
            ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => false,
            'show_in_rest'    => true,
            'supports'        => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
            'rewrite'         => false,
            'capability_type' => 'post',
        )
    );
} );

/* ═══════════════════════════════════════════════════════════════════
   2. ADMIN MENU  (parent «mits-osvita» реєструється в functions.php)
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', function (): void {

    remove_submenu_page( 'mits-osvita', 'mits-osvita' );

    // ── Налаштування
    add_submenu_page(
        'mits-osvita',
        'Налаштування',
        'Налаштування',
        'edit_pages',
        'mits-osvita-settings',
        'mits_osvita_settings_page_render'
    );

    // ── Технології
    add_submenu_page(
        'mits-osvita',
        'Технології',
        'Технології',
        'edit_posts',
        'mits-osvita-tech',
        'mits_osvita_tech_page_render'
    );

    // ── Документи
    add_submenu_page(
        'mits-osvita',
        'Документи',
        'Документи',
        'edit_posts',
        'mits-osvita-docs',
        'mits_osvita_docs_page_render'
    );

    // ── Рівні навчання
    add_submenu_page(
        'mits-osvita',
        'Рівні навчання',
        'Рівні навчання',
        'edit_posts',
        'mits-osvita-levels',
        'mits_osvita_levels_page_render'
    );

}, 999 );

// Підсвічування «mits-osvita» при редагуванні сторінок Освіти та CPT технологій
add_filter( 'parent_file', function ( string $parent ): string {
    if ( ! is_admin() ) {
        return $parent;
    }
    $screen = get_current_screen();
    if ( ! $screen ) {
        return $parent;
    }

    if ( $screen->base === 'toplevel_page_mits-osvita-tech'
         || $screen->id === 'mits-osvita_page_mits-osvita-tech'
         || $screen->id === 'mits-osvita_page_mits-osvita-settings'
         || $screen->id === 'mits-osvita_page_mits-osvita-docs' ) {
        return 'mits-osvita';
    }

    if ( $screen->base === 'post' && $screen->post_type === 'page' ) {
        global $post;
        if ( $post ) {
            $template = get_post_meta( $post->ID, '_wp_page_template', true );
            if ( in_array( $template, array( 'template-mits-osvita.php', 'template-mits-osvita-sub.php' ), true ) ) {
                return 'mits-osvita';
            }
        }
    }

    return $parent;
} );

/* ═══════════════════════════════════════════════════════════════════
   3. META BOXES
═══════════════════════════════════════════════════════════════════ */

add_action( 'add_meta_boxes', function (): void {
    add_meta_box(
        'mits_osvita_sub_fields',
        'Параметри підрозділу Освіта',
        'mits_osvita_sub_box_render',
        'page',
        'normal',
        'high'
    );
    add_meta_box(
        'mits_technology_fields',
        'Посилання',
        'mits_technology_box_render',
        'mits_technology',
        'normal',
        'high'
    );
} );

function mits_technology_box_render( WP_Post $post ): void {
    wp_nonce_field( 'mits_technology_save', 'mits_technology_nonce' );
    $link = get_post_meta( $post->ID, '_tech_link', true );
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:160px"><label for="tech-link">Посилання (URL)</label></th>
            <td>
                <input type="url" id="tech-link" name="tech_link"
                       value="<?php echo esc_attr( $link ); ?>"
                       class="large-text"
                       placeholder="https://example.com">
                <p class="description">URL сайту або сторінки технології. Іконка стане клікабельною.</p>
            </td>
        </tr>
    </table>
    <?php
}

function mits_osvita_settings_page_render(): void {
    $osvita_page = get_page_by_path( 'освіта' );
    if ( ! $osvita_page ) {
        echo '<div class="wrap"><p>Сторінку «Освіта» не знайдено.</p></div>';
        return;
    }
    $post_id = $osvita_page->ID;

    // ── Handle save ──────────────────────────────────────────────
    if (
        isset( $_POST['mits_osvita_settings_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_osvita_settings_nonce'] ) ), 'mits_osvita_settings_save' )
        && current_user_can( 'edit_pages' )
    ) {
        update_post_meta( $post_id, '_osvita_hero_id', absint( $_POST['osvita_hero_id'] ?? 0 ) );
        update_post_meta( $post_id, '_osvita_slogan', sanitize_text_field( wp_unslash( $_POST['osvita_slogan'] ?? '' ) ) );
        update_post_meta( $post_id, '_osvita_desc', wp_kses_post( wp_unslash( $_POST['osvita_desc'] ?? '' ) ) );
        echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
    }

    $hero_id = (int) get_post_meta( $post_id, '_osvita_hero_id', true );
    $slogan  = get_post_meta( $post_id, '_osvita_slogan', true );
    $desc    = get_post_meta( $post_id, '_osvita_desc', true );
    ?>
    <div class="wrap">
    <h1>Освіта — Налаштування</h1>
    <form method="post">
        <?php wp_nonce_field( 'mits_osvita_settings_save', 'mits_osvita_settings_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th style="width:220px"><label for="osvita-hero-id">ID слайду Hero Slider</label></th>
                <td>
                    <input type="number" id="osvita-hero-id" name="osvita_hero_id" min="0"
                           value="<?php echo esc_attr( $hero_id ); ?>" style="width:120px">
                    <p class="description">ID запису типу «Hero Слайди». 0 = показати всі.</p>
                </td>
            </tr>
            <tr>
                <th><label for="osvita-slogan">Слоган</label></th>
                <td>
                    <input type="text" id="osvita-slogan" name="osvita_slogan"
                           value="<?php echo esc_attr( $slogan ); ?>" class="large-text">
                </td>
            </tr>
            <tr>
                <th><label>Короткий опис</label></th>
                <td>
                    <?php
                    wp_editor( $desc, 'osvita_desc', array(
                        'textarea_name' => 'osvita_desc',
                        'media_buttons' => false,
                        'teeny'         => false,
                        'textarea_rows' => 8,
                        'quicktags'     => true,
                    ) );
                    ?>
                </td>
            </tr>
        </table>
        <?php submit_button( 'Зберегти' ); ?>
    </form>
    </div>
    <?php
}

function mits_osvita_page_box_render( WP_Post $post ): void {
    if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'template-mits-osvita.php' ) {
        echo '<p style="color:#999">Показується тільки для сторінок із шаблоном «MITS — Освіта».</p>';
        return;
    }
    wp_nonce_field( 'mits_osvita_page_save', 'mits_osvita_page_nonce' );
    $hero_id = (int) get_post_meta( $post->ID, '_osvita_hero_id', true );
    $slogan  = get_post_meta( $post->ID, '_osvita_slogan', true );
    $desc    = get_post_meta( $post->ID, '_osvita_desc', true );
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:220px"><label for="osvita-hero-id">ID слайду Hero Slider</label></th>
            <td>
                <input type="number" id="osvita-hero-id" name="osvita_hero_id" min="0"
                       value="<?php echo esc_attr( $hero_id ); ?>" style="width:120px">
                <p class="description">ID запису типу «Hero Слайди». 0 = показати всі.</p>
            </td>
        </tr>
        <tr>
            <th><label for="osvita-slogan">Слоган</label></th>
            <td>
                <input type="text" id="osvita-slogan" name="osvita_slogan"
                       value="<?php echo esc_attr( $slogan ); ?>" class="large-text">
                <p class="description">Короткий слоган під заголовком секції.</p>
            </td>
        </tr>
        <tr>
            <th><label for="osvita-desc">Короткий опис</label></th>
            <td>
                <?php
                wp_editor( $desc, 'osvita_desc', array(
                    'textarea_name' => 'osvita_desc',
                    'media_buttons' => false,
                    'teeny'         => false,
                    'textarea_rows' => 6,
                    'quicktags'     => true,
                ) );
                ?>
            </td>
        </tr>
    </table>

    <?php
}

// Enqueue media + editor assets on our custom admin pages
add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
    if ( strpos( $hook, 'mits-osvita-tech' ) !== false
         || strpos( $hook, 'mits-osvita-settings' ) !== false
         || strpos( $hook, 'mits-osvita-docs' ) !== false
         || strpos( $hook, 'mits-osvita-levels' ) !== false ) {
        wp_enqueue_media();
    }
} );

function mits_osvita_levels_page_render(): void {

    $_defaults = array(
        array( 'title' => 'Бакалавр',    'color' => '#4DC9C6', 'vote_url' => '', 'schedule' => array( 'name' => 'Розклад занять', 'url' => '' ), 'exams' => array( 'name' => 'Розклад екзаменів', 'url' => '' ), 'docs' => array() ),
        array( 'title' => 'Магістратура', 'color' => '#E87A2F', 'vote_url' => '', 'schedule' => array( 'name' => 'Розклад занять', 'url' => '' ), 'exams' => array( 'name' => 'Розклад екзаменів', 'url' => '' ), 'docs' => array() ),
        array( 'title' => 'Аспірантура',  'color' => '#D86FA0', 'vote_url' => '', 'schedule' => array( 'name' => 'Розклад занять', 'url' => '' ), 'exams' => array( 'name' => 'Розклад екзаменів', 'url' => '' ), 'docs' => array() ),
    );

    // ── Save ────────────────────────────────────────────────────
    if (
        isset( $_POST['mits_osvita_levels_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_osvita_levels_nonce'] ) ), 'mits_osvita_levels_save' )
        && current_user_can( 'edit_posts' )
    ) {
        $blocks = array();
        for ( $i = 0; $i < 3; $i++ ) {
            $title       = sanitize_text_field( wp_unslash( $_POST[ "level_title_{$i}" ]        ?? $_defaults[ $i ]['title'] ) );
            $color       = sanitize_hex_color( wp_unslash( $_POST[ "level_color_{$i}" ]         ?? $_defaults[ $i ]['color'] ) ) ?: $_defaults[ $i ]['color'];
            $vote_url    = esc_url_raw( wp_unslash( $_POST[ "level_vote_url_{$i}" ]       ?? '' ) );
            $sched_name  = sanitize_text_field( wp_unslash( $_POST[ "level_schedule_name_{$i}" ] ?? 'Розклад занять' ) );
            $sched_url   = esc_url_raw( wp_unslash( $_POST[ "level_schedule_url_{$i}" ]  ?? '' ) );
            $exams_name  = sanitize_text_field( wp_unslash( $_POST[ "level_exams_name_{$i}" ]    ?? 'Розклад екзаменів' ) );
            $exams_url   = esc_url_raw( wp_unslash( $_POST[ "level_exams_url_{$i}" ]     ?? '' ) );
            $doc_names   = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST[ "level_doc_name_{$i}" ] ?? array() ) ) );
            $doc_urls    = array_map( 'esc_url_raw',         array_map( 'wp_unslash', (array) ( $_POST[ "level_doc_url_{$i}" ]  ?? array() ) ) );
            $docs = array();
            foreach ( $doc_names as $j => $name ) {
                if ( trim( $name ) === '' && trim( $doc_urls[ $j ] ?? '' ) === '' ) continue;
                $docs[] = array( 'name' => $name, 'url' => $doc_urls[ $j ] ?? '' );
            }
            $blocks[] = array( 'title' => $title, 'color' => $color, 'vote_url' => $vote_url, 'schedule' => array( 'name' => $sched_name, 'url' => $sched_url ), 'exams' => array( 'name' => $exams_name, 'url' => $exams_url ), 'docs' => $docs );
        }
        update_option( 'mits_osvita_level_blocks', wp_json_encode( $blocks, JSON_UNESCAPED_UNICODE ) );
        echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
    }

    $saved  = json_decode( get_option( 'mits_osvita_level_blocks' ) ?: '[]', true );
    if ( ! is_array( $saved ) || count( $saved ) < 3 ) $saved = $_defaults;
    $blocks = array_replace_recursive( $_defaults, $saved );
    ?>
    <div class="wrap">
    <h1>Освіта — Рівні навчання</h1>
    <form method="post">
        <?php wp_nonce_field( 'mits_osvita_levels_save', 'mits_osvita_levels_nonce' ); ?>

        <?php for ( $i = 0; $i < 3; $i++ ) :
            $b = $blocks[ $i ];
        ?>
        <h2 style="margin-top:32px;border-top:1px solid #c3c4c7;padding-top:16px">
            Блок <?php echo $i + 1; ?>: <?php echo esc_html( $b['title'] ); ?>
        </h2>
        <table class="form-table" style="margin-top:0">
            <tr>
                <th style="width:180px"><label>Заголовок</label></th>
                <td><input type="text" name="level_title_<?php echo $i; ?>" value="<?php echo esc_attr( $b['title'] ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Колір заголовку</label></th>
                <td><input type="color" name="level_color_<?php echo $i; ?>" value="<?php echo esc_attr( $b['color'] ); ?>"></td>
            </tr>
            <tr>
                <th><label>URL «Проголосувати»</label></th>
                <td><input type="text" name="level_vote_url_<?php echo $i; ?>" value="<?php echo esc_attr( $b['vote_url'] ); ?>" class="regular-text" placeholder="/golosuvannia/"></td>
            </tr>
            <tr>
                <th><label>Розклад занять</label></th>
                <td>
                    <input type="text" name="level_schedule_name_<?php echo $i; ?>" value="<?php echo esc_attr( $b['schedule']['name'] ?? 'Розклад занять' ); ?>" class="regular-text" placeholder="Назва" style="margin-bottom:6px"><br>
                    <?php $surl = esc_url( $b['schedule']['url'] ?? '' ); $sbase = $surl ? basename( $surl ) : ''; ?>
                    <div class="osvita-file-pick" data-name-field="level_schedule_name_<?php echo $i; ?>">
                        <span class="osvita-file-pick__name" style="display:<?php echo $sbase ? 'block' : 'none'; ?>;font-size:12px;color:#aaa;margin-bottom:4px;word-break:break-all;"><?php echo esc_html( $sbase ); ?></span>
                        <input type="hidden" name="level_schedule_url_<?php echo $i; ?>" value="<?php echo $surl; ?>">
                        <button type="button" class="button osvita-file-pick__choose">Вибрати файл</button>
                        <button type="button" class="button osvita-file-pick__clear"<?php echo $surl ? '' : ' style="display:none"'; ?>>✕</button>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label>Розклад екзаменів</label></th>
                <td>
                    <input type="text" name="level_exams_name_<?php echo $i; ?>" value="<?php echo esc_attr( $b['exams']['name'] ?? 'Розклад екзаменів' ); ?>" class="regular-text" placeholder="Назва" style="margin-bottom:6px"><br>
                    <?php $eurl = esc_url( $b['exams']['url'] ?? '' ); $ebase = $eurl ? basename( $eurl ) : ''; ?>
                    <div class="osvita-file-pick" data-name-field="level_exams_name_<?php echo $i; ?>">
                        <span class="osvita-file-pick__name" style="display:<?php echo $ebase ? 'block' : 'none'; ?>;font-size:12px;color:#aaa;margin-bottom:4px;word-break:break-all;"><?php echo esc_html( $ebase ); ?></span>
                        <input type="hidden" name="level_exams_url_<?php echo $i; ?>" value="<?php echo $eurl; ?>">
                        <button type="button" class="button osvita-file-pick__choose">Вибрати файл</button>
                        <button type="button" class="button osvita-file-pick__clear"<?php echo $eurl ? '' : ' style="display:none"'; ?>>✕</button>
                    </div>
                </td>
            </tr>
        </table>

        <h3>Список документів</h3>
        <table class="wp-list-table widefat striped" id="level-docs-table-<?php echo $i; ?>">
            <thead><tr>
                <th style="width:300px">Файл / URL</th>
                <th>Назва</th>
                <th style="width:44px"></th>
            </tr></thead>
            <tbody class="level-docs-rows" data-block="<?php echo $i; ?>">
            <?php foreach ( $b['docs'] as $doc ) :
                $durl  = esc_url( $doc['url']  ?? '' );
            ?>
                <tr class="level-doc-row">
                    <td>
                        <div class="osvita-file-pick">
                            <input type="url" name="level_doc_url_<?php echo $i; ?>[]" value="<?php echo $durl; ?>"
                                   class="large-text" placeholder="https://" style="margin-bottom:6px">
                            <br>
                            <button type="button" class="button osvita-file-pick__choose">Вибрати файл</button>
                            <button type="button" class="button osvita-file-pick__clear"<?php echo $durl ? '' : ' style="display:none"'; ?>>✕</button>
                        </div>
                    </td>
                    <td><input type="text" name="level_doc_name_<?php echo $i; ?>[]" value="<?php echo esc_attr( $doc['name'] ?? '' ); ?>" class="regular-text level-doc-name" placeholder="Назва документу"></td>
                    <td><button type="button" class="button level-doc-remove">✕</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="button" class="button level-doc-add" data-block="<?php echo $i; ?>">+ Додати документ</button></p>

        <?php endfor; ?>

        <?php submit_button( 'Зберегти' ); ?>
    </form>
    </div>
    <script>
    (function(){
        function initFilePick(wrap) {
            var choose = wrap.querySelector('.osvita-file-pick__choose');
            var clear  = wrap.querySelector('.osvita-file-pick__clear');
            var label  = wrap.querySelector('.osvita-file-pick__name');
            var input  = wrap.querySelector('input[type="hidden"], input[type="url"]');
            var nameFieldName = wrap.dataset.nameField;
            var nameInp = nameFieldName ? document.querySelector('input[name="' + nameFieldName + '"]') : null;
            choose.addEventListener('click', function() {
                var frame = wp.media({
                    title: 'Вибрати документ',
                    button: { text: 'Вибрати' },
                    multiple: false
                });
                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    input.value = att.url;
                    if (label) { label.textContent = att.filename || att.url.split('/').pop(); label.style.display = 'block'; }
                    clear.style.display = 'inline-block';
                    if (nameInp && !nameInp.value) nameInp.value = att.title || '';
                    var row = wrap.closest('tr.level-doc-row');
                    if (row) { var rn = row.querySelector('.level-doc-name'); if (rn && !rn.value) rn.value = att.title || ''; }
                });
                frame.open();
            });
            clear.addEventListener('click', function() {
                input.value = '';
                if (label) { label.textContent = ''; label.style.display = 'none'; }
                clear.style.display = 'none';
            });
        }

        document.querySelectorAll('.osvita-file-pick').forEach(initFilePick);

        function makeRow(blockIdx) {
            return '<tr class="level-doc-row">'
                + '<td><div class="osvita-file-pick">'
                +   '<input type="url" name="level_doc_url_' + blockIdx + '[]" value="" class="large-text" placeholder="https://" style="margin-bottom:6px">'
                +   '<br><button type="button" class="button osvita-file-pick__choose">Вибрати файл</button>'
                +   ' <button type="button" class="button osvita-file-pick__clear" style="display:none">✕</button>'
                + '</div></td>'
                + '<td><input type="text" name="level_doc_name_' + blockIdx + '[]" value="" class="regular-text level-doc-name" placeholder="Назва документу"></td>'
                + '<td><button type="button" class="button level-doc-remove">✕</button></td>'
                + '</tr>';
        }
        document.querySelectorAll('.level-doc-add').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = this.dataset.block;
                var tbody = document.querySelector('.level-docs-rows[data-block="' + idx + '"]');
                tbody.insertAdjacentHTML('beforeend', makeRow(idx));
                initFilePick(tbody.lastElementChild.querySelector('.osvita-file-pick'));
            });
        });
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('level-doc-remove')) {
                e.target.closest('tr').remove();
            }
        });
    }());
    </script>
    <?php
}

function mits_osvita_docs_page_render(): void {
    // ── Handle save ──────────────────────────────────────────────
    if (
        isset( $_POST['mits_osvita_docs_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_osvita_docs_nonce'] ) ), 'mits_osvita_docs_save' )
        && current_user_can( 'edit_posts' )
    ) {
        $title = sanitize_text_field( wp_unslash( $_POST['osvita_docs_title'] ?? '' ) );
        $desc  = wp_kses_post( wp_unslash( $_POST['osvita_docs_desc'] ?? '' ) );
        $urls  = array_map( 'esc_url_raw',      array_map( 'wp_unslash', (array) ( $_POST['osvita_doc_url']  ?? [] ) ) );
        $names = array_map(
            function( $v ) { return wp_kses( wp_unslash( $v ), array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) ); },
            (array) ( $_POST['osvita_doc_name'] ?? [] )
        );
        $items = [];
        foreach ( $urls as $i => $url ) {
            if ( trim( $url ) === '' && trim( $names[ $i ] ?? '' ) === '' ) continue;
            $items[] = [ 'url' => $url, 'name' => $names[ $i ] ?? '' ];
        }
        update_option( 'mits_osvita_docs_title', $title );
        update_option( 'mits_osvita_docs_desc',  $desc );
        update_option( 'mits_osvita_docs_items', wp_json_encode( $items, JSON_UNESCAPED_UNICODE ) );
        echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
    }

    $title = get_option( 'mits_osvita_docs_title', '' );
    $desc  = get_option( 'mits_osvita_docs_desc',  '' );
    $items = json_decode( get_option( 'mits_osvita_docs_items' ) ?: '[]', true );
    if ( ! is_array( $items ) ) $items = [];
    ?>
    <div class="wrap">
    <h1>Освіта — Документи</h1>
    <form method="post">
        <?php wp_nonce_field( 'mits_osvita_docs_save', 'mits_osvita_docs_nonce' ); ?>

        <table class="form-table" style="margin-top:0">
            <tr>
                <th style="width:160px"><label for="osvita-docs-title">Заголовок секції</label></th>
                <td><input type="text" id="osvita-docs-title" name="osvita_docs_title" value="<?php echo esc_attr( $title ); ?>" class="large-text" placeholder="Нормативні документи"></td>
            </tr>
        </table>

        <h2 style="margin-top:24px">Опис</h2>
        <?php
        wp_editor( $desc, 'osvita_docs_desc', array(
            'textarea_name' => 'osvita_docs_desc',
            'media_buttons' => false,
            'teeny'         => false,
            'textarea_rows' => 5,
            'quicktags'     => true,
        ) );
        ?>

        <h2 style="margin-top:32px">Список документів</h2>
        <table class="wp-list-table widefat striped" id="osvita-docs-table">
            <thead><tr>
                <th style="width:300px">Файл / URL</th>
                <th>Назва</th>
                <th style="width:44px"></th>
            </tr></thead>
            <tbody id="osvita-docs-rows">
            <?php foreach ( $items as $item ) :
                $furl  = esc_url( $item['url']  ?? '' );
                $fname = wp_kses( $item['name'] ?? '', array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) );
            ?>
                <tr class="osvita-doc-row">
                    <td>
                        <div class="osvita-file-pick">
                            <input type="url" name="osvita_doc_url[]" value="<?php echo $furl; ?>"
                                   class="large-text" placeholder="https://" style="margin-bottom:6px">
                            <br>
                            <button type="button" class="button osvita-file-pick__choose">Вибрати файл</button>
                            <button type="button" class="button osvita-file-pick__clear"<?php echo $furl ? '' : ' style="display:none"'; ?>>✕</button>
                        </div>
                    </td>
                    <td><textarea name="osvita_doc_name[]" rows="2" class="large-text" placeholder="Назва або HTML: текст &lt;a href=&quot;url&quot;&gt;посилання&lt;/a&gt; текст"><?php echo $fname; ?></textarea></td>
                    <td><button type="button" class="button osvita-doc-remove">✕</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="button" class="button" id="osvita-docs-add">+ Додати документ</button></p>
        <?php submit_button( 'Зберегти' ); ?>
    </form>
    </div>
    <script>
    (function(){
        function buildRow() {
            return '<tr class="osvita-doc-row">'
                + '<td><div class="osvita-file-pick">'
                +   '<input type="url" name="osvita_doc_url[]" value="" class="large-text" placeholder="https://" style="margin-bottom:6px">'
                +   '<br><button type="button" class="button osvita-file-pick__choose">Вибрати файл</button>'
                +   ' <button type="button" class="button osvita-file-pick__clear" style="display:none">✕</button>'
                + '</div></td>'
                + '<td><textarea name="osvita_doc_name[]" rows="2" class="large-text" placeholder="Назва або HTML: текст &lt;a href=&quot;url&quot;&gt;посилання&lt;/a&gt; текст"></textarea></td>'
                + '<td><button type="button" class="button osvita-doc-remove">✕</button></td>'
                + '</tr>';
        }

        function initRow( row ) {
            var choose = row.querySelector( '.osvita-file-pick__choose' );
            var clear  = row.querySelector( '.osvita-file-pick__clear' );
            var input  = row.querySelector( 'input[name="osvita_doc_url[]"]' );
            var nameTA = row.querySelector( 'textarea[name="osvita_doc_name[]"]' );

            choose.addEventListener( 'click', function() {
                var frame = wp.media({
                    title: 'Вибрати документ',
                    button: { text: 'Вибрати' },
                    multiple: false
                });
                frame.on( 'select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    input.value = att.url;
                    clear.style.display = 'inline-block';
                    if ( nameTA && ! nameTA.value ) {
                        nameTA.value = att.title || '';
                    }
                });
                frame.open();
            });

            clear.addEventListener( 'click', function() {
                input.value = '';
                clear.style.display = 'none';
            });
        }

        document.querySelectorAll( '.osvita-doc-row' ).forEach( initRow );

        document.getElementById( 'osvita-docs-add' ).addEventListener( 'click', function() {
            var tbody = document.getElementById( 'osvita-docs-rows' );
            tbody.insertAdjacentHTML( 'beforeend', buildRow() );
            initRow( tbody.lastElementChild );
        });

        document.addEventListener( 'click', function(e) {
            if ( e.target.classList.contains( 'osvita-doc-remove' ) ) {
                e.target.closest( 'tr' ).remove();
            }
        });
    }());
    </script>
    <?php
}

function mits_osvita_tech_page_render(): void {
    // ── Handle save ──────────────────────────────────────────────
    if (
        isset( $_POST['mits_osvita_tech_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_osvita_tech_nonce'] ) ), 'mits_osvita_tech_save' )
        && current_user_can( 'edit_posts' )
    ) {
        $imgs  = array_map( 'esc_url_raw', array_map( 'wp_unslash', (array) ( $_POST['osvita_tech_img'] ?? [] ) ) );
        $links = array_map( 'esc_url_raw', array_map( 'wp_unslash', (array) ( $_POST['osvita_tech_link'] ?? [] ) ) );
        $descs = array_map( 'wp_kses_post', array_map( 'wp_unslash', (array) ( $_POST['osvita_tech_desc'] ?? [] ) ) );
        $items = [];
        foreach ( $imgs as $i => $img ) {
            if ( trim( $img ) === '' ) continue;
            $items[] = [ 'img' => $img, 'link' => $links[ $i ] ?? '', 'desc' => $descs[ $i ] ?? '' ];
        }
        update_option( 'mits_osvita_tech_items', wp_json_encode( $items, JSON_UNESCAPED_UNICODE ) );
        echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
    }

    $tech_items = json_decode( get_option( 'mits_osvita_tech_items' ) ?: '[]', true );
    if ( ! is_array( $tech_items ) ) $tech_items = [];
    ?>
    <div class="wrap">
    <h1>Технології</h1>
    <form method="post">
        <?php wp_nonce_field( 'mits_osvita_tech_save', 'mits_osvita_tech_nonce' ); ?>
        <table class="wp-list-table widefat striped" id="osvita-tech-table" style="table-layout:fixed">
            <thead><tr>
                <th style="width:140px">Зображення</th>
                <th>Посилання (URL)</th>
                <th>Опис</th>
                <th style="width:44px"></th>
            </tr></thead>
            <tbody id="osvita-tech-rows">
            <?php foreach ( $tech_items as $item ) :
                $iurl = esc_url( $item['img'] ?? '' );
            ?>
                <tr class="osvita-tech-row">
                    <td>
                        <div class="osvita-imp">
                            <img class="osvita-imp__preview" src="<?php echo $iurl; ?>" alt=""<?php echo $iurl ? '' : ' style="display:none"'; ?>>
                            <input type="hidden" name="osvita_tech_img[]" value="<?php echo $iurl; ?>">
                            <br>
                            <button type="button" class="button osvita-imp__choose">Вибрати</button>
                            <button type="button" class="button osvita-imp__clear"<?php echo $iurl ? '' : ' style="display:none"'; ?>>✕</button>
                        </div>
                    </td>
                    <td><input type="url" name="osvita_tech_link[]" value="<?php echo esc_attr( $item['link'] ?? '' ); ?>" class="regular-text" placeholder="https://"></td>
                    <td><textarea name="osvita_tech_desc[]" rows="2" class="large-text"><?php echo esc_textarea( $item['desc'] ?? '' ); ?></textarea></td>
                    <td><button type="button" class="button osvita-tech-remove">✕</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="button" class="button" id="osvita-tech-add">+ Додати технологію</button></p>
        <?php submit_button( 'Зберегти' ); ?>
    </form>
    </div>
    <style>
    .osvita-imp { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
    .osvita-imp__preview { max-width: 120px; max-height: 60px; border: 1px solid #c3c4c7; border-radius: 3px; padding: 2px; background: #fff; display: block; }
    </style>
    <script>
    (function(){
        function buildRow() {
            return '<tr class="osvita-tech-row">'
                + '<td><div class="osvita-imp">'
                +   '<img class="osvita-imp__preview" src="" alt="" style="display:none">'
                +   '<input type="hidden" name="osvita_tech_img[]" value="">'
                +   '<br><button type="button" class="button osvita-imp__choose">Вибрати</button>'
                +   ' <button type="button" class="button osvita-imp__clear" style="display:none">✕</button>'
                + '</div></td>'
                + '<td><input type="url" name="osvita_tech_link[]" value="" class="regular-text" placeholder="https://"></td>'
                + '<td><textarea name="osvita_tech_desc[]" rows="2" class="large-text"></textarea></td>'
                + '<td><button type="button" class="button osvita-tech-remove">✕</button></td>'
                + '</tr>';
        }

        function initRow(row) {
            var choose  = row.querySelector('.osvita-imp__choose');
            var clear   = row.querySelector('.osvita-imp__clear');
            var preview = row.querySelector('.osvita-imp__preview');
            var input   = row.querySelector('input[name="osvita_tech_img[]"]');

            choose.addEventListener('click', function() {
                var frame = wp.media({
                    title: 'Вибрати зображення',
                    button: { text: 'Вибрати' },
                    multiple: false,
                    library: { type: 'image' }
                });
                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    input.value = att.url;
                    preview.src = att.url;
                    preview.style.display = 'block';
                    clear.style.display = 'inline-block';
                });
                frame.open();
            });

            clear.addEventListener('click', function() {
                input.value = '';
                preview.src = '';
                preview.style.display = 'none';
                clear.style.display = 'none';
            });
        }

        document.querySelectorAll('.osvita-tech-row').forEach(initRow);

        document.getElementById('osvita-tech-add').addEventListener('click', function() {
            var tbody = document.getElementById('osvita-tech-rows');
            tbody.insertAdjacentHTML('beforeend', buildRow());
            initRow(tbody.lastElementChild);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('osvita-tech-remove')) {
                e.target.closest('tr').remove();
            }
        });
    })();
    </script>
    <?php
}

function mits_osvita_sub_box_render( WP_Post $post ): void {
    $template  = get_post_meta( $post->ID, '_wp_page_template', true );
    $sub_slugs = array( 'загальні-положення', 'бакалавр', 'магістратура', 'аспірантура' );
    if ( $template !== 'template-mits-osvita-sub.php' && ! in_array( $post->post_name, $sub_slugs, true ) ) {
        echo '<p style="color:#999">Показується тільки для підсторінок Освіти (шаблон «MITS — Освіта підрозділ»).</p>';
        return;
    }
    wp_nonce_field( 'mits_osvita_sub_save', 'mits_osvita_sub_nonce' );
    $hero_id = (int) get_post_meta( $post->ID, '_osvita_sub_hero_id', true );
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:220px"><label for="osvita-sub-hero-id">ID слайду Hero Slider</label></th>
            <td>
                <input type="number" id="osvita-sub-hero-id" name="osvita_sub_hero_id" min="0"
                       value="<?php echo esc_attr( $hero_id ); ?>" style="width:120px">
                <p class="description">ID запису типу «Hero Слайди» для цієї підсторінки. 0 = показати всі слайди.</p>
            </td>
        </tr>
    </table>
    <?php
}

/* ═══════════════════════════════════════════════════════════════════
   4. SAVE META
═══════════════════════════════════════════════════════════════════ */

add_action( 'save_post_page', function ( int $post_id ): void {
    // Save sub-page settings
    if (
        isset( $_POST['mits_osvita_sub_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_osvita_sub_nonce'] ) ), 'mits_osvita_sub_save' )
        && ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        && current_user_can( 'edit_page', $post_id )
    ) {
        update_post_meta( $post_id, '_osvita_sub_hero_id', absint( $_POST['osvita_sub_hero_id'] ?? 0 ) );
    }
} );

add_action( 'save_post_mits_technology', function ( int $post_id ): void {
    if (
        ! isset( $_POST['mits_technology_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_technology_nonce'] ) ), 'mits_technology_save' )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        || ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }
    $link = isset( $_POST['tech_link'] ) ? esc_url_raw( wp_unslash( $_POST['tech_link'] ) ) : '';
    update_post_meta( $post_id, '_tech_link', $link );
} );

/* ═══════════════════════════════════════════════════════════════════
   5. FORCE TEMPLATE BY SLUG
═══════════════════════════════════════════════════════════════════ */

add_filter( 'template_include', function ( string $template ): string {
    if ( ! is_page() ) {
        return $template;
    }
    $post = get_queried_object();
    if ( ! ( $post instanceof WP_Post ) ) {
        return $template;
    }

    if ( $post->post_name === 'освіта' ) {
        $override = locate_template( 'template-mits-osvita.php' );
        return $override ?: $template;
    }

    $sub_slugs = array( 'загальні-положення', 'бакалавр', 'магістратура', 'аспірантура' );
    if ( in_array( $post->post_name, $sub_slugs, true ) ) {
        $override = locate_template( 'template-mits-osvita-sub.php' );
        return $override ?: $template;
    }

    if ( $post->post_name === 'golosuvannia' ) {
        $override = locate_template( 'template-mits-golosuvannia.php' );
        return $override ?: $template;
    }

    return $template;
} );

/* ═══════════════════════════════════════════════════════════════════
   6. AUTO-CREATE — сторінка Голосування
═══════════════════════════════════════════════════════════════════ */

add_action( 'init', function (): void {
    if ( get_option( 'mits_golosuvannia_page_created' ) ) {
        return;
    }
    $existing = get_page_by_path( 'golosuvannia' );
    if ( $existing ) {
        update_option( 'mits_golosuvannia_page_created', 1 );
        return;
    }
    $id = wp_insert_post( array(
        'post_title'   => 'Голосування',
        'post_name'    => 'golosuvannia',
        'post_content' => '<p>Голосування тимчасово недоступне.</p>',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => 1,
    ) );
    if ( $id && ! is_wp_error( $id ) ) {
        update_option( 'mits_golosuvannia_page_created', 1 );
    }
} );
