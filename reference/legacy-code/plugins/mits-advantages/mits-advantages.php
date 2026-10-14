<?php
/**
 * Plugin Name: MITS Advantages
 * Description: CPT «mits_advantage» для акордеон-секції «Переваги» на головній сторінці.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: mits-advantages
 *
 * @package MITS_Advantages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════════════════════════
   1. CPT REGISTRATION
═══════════════════════════════════════════════════════════════════ */

add_action( 'init', function (): void {
    register_post_type(
        'mits_advantage',
        array(
            'labels'          => array(
                'name'               => 'Переваги',
                'singular_name'      => 'Перевага',
                'add_new'            => 'Додати перевагу',
                'add_new_item'       => 'Нова перевага',
                'edit_item'          => 'Редагувати перевагу',
                'all_items'          => 'Всі переваги',
                'search_items'       => 'Пошук переваг',
                'not_found'          => 'Переваг не знайдено',
                'not_found_in_trash' => 'Кошик порожній',
            ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => false,
            'show_in_rest'    => false,
            'supports'        => array( 'title', 'page-attributes' ),
            'rewrite'         => false,
            'capability_type' => 'post',
        )
    );
} );

/* ═══════════════════════════════════════════════════════════════════
   2. POLYLANG
═══════════════════════════════════════════════════════════════════ */

add_filter( 'pll_get_post_types', function ( array $types ): array {
    $types['mits_advantage'] = 'mits_advantage';
    return $types;
} );

/* ═══════════════════════════════════════════════════════════════════
   3. ADMIN MENU
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', function (): void {
    add_submenu_page(
        'mits-pages',
        'Переваги',
        'Переваги',
        'edit_posts',
        'edit.php?post_type=mits_advantage'
    );
}, 999 );

add_filter( 'parent_file', function ( string $parent ): string {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'mits_advantage' ) {
        return 'mits-pages';
    }
    return $parent;
} );

add_filter( 'submenu_file', function ( ?string $sub ): ?string {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'mits_advantage' ) {
        return 'edit.php?post_type=mits_advantage';
    }
    return $sub;
} );

/* ═══════════════════════════════════════════════════════════════════
   4. META BOX
═══════════════════════════════════════════════════════════════════ */

add_action( 'add_meta_boxes', function (): void {
    add_meta_box(
        'mits_adv_fields',
        'Деталі переваги',
        'mits_adv_meta_box_render',
        'mits_advantage',
        'normal',
        'high'
    );
} );

function mits_adv_meta_box_render( WP_Post $post ): void {
    wp_nonce_field( 'mits_adv_save', 'mits_adv_nonce' );
    $details = get_post_meta( $post->ID, '_adv_details', true );
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:160px"><label for="mits-adv-details">Розгорнутий текст</label></th>
            <td>
                <textarea id="mits-adv-details" name="mits_adv_details" rows="8"
                          style="width:100%"><?php echo esc_textarea( $details ); ?></textarea>
                <p class="description">Текст, який показується при розкритті пункту. Допустимий HTML: &lt;p&gt; &lt;strong&gt; &lt;em&gt; &lt;ul&gt; &lt;li&gt; &lt;a&gt;.</p>
            </td>
        </tr>
    </table>
    <?php
}

/* ═══════════════════════════════════════════════════════════════════
   5. SAVE META
═══════════════════════════════════════════════════════════════════ */

add_action( 'save_post_mits_advantage', function ( int $post_id ): void {
    if (
        ! isset( $_POST['mits_adv_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_adv_nonce'] ) ), 'mits_adv_save' )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        || ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }
    update_post_meta(
        $post_id,
        '_adv_details',
        wp_kses_post( wp_unslash( $_POST['mits_adv_details'] ?? '' ) )
    );
} );

/* ═══════════════════════════════════════════════════════════════════
   6. ADMIN COLUMNS
═══════════════════════════════════════════════════════════════════ */

add_filter( 'manage_mits_advantage_posts_columns', function ( array $cols ): array {
    $new = array();
    $new['cb']            = $cols['cb'] ?? '<input type="checkbox">';
    $new['title']         = $cols['title'] ?? 'Назва';
    $new['adv_details']   = 'Деталі';
    $new['adv_shortcode'] = 'Шорткод';
    $new['menu_order']    = 'Порядок';
    if ( isset( $cols['languages'] ) ) {
        $new['languages'] = $cols['languages'];
    }
    return $new;
}, 20 );

add_action( 'manage_mits_advantage_posts_custom_column', function ( string $col, int $post_id ): void {
    switch ( $col ) {
        case 'adv_details':
            $d = get_post_meta( $post_id, '_adv_details', true );
            echo $d ? esc_html( wp_trim_words( wp_strip_all_tags( $d ), 10, '…' ) ) : '—';
            break;

        case 'adv_shortcode':
            if ( function_exists( 'pll_get_post_translations' ) ) {
                $translations = pll_get_post_translations( $post_id );
                foreach ( $translations as $lang => $tid ) {
                    $sc = '[mits_advantage id="' . $tid . '"]';
                    printf(
                        '<div style="margin-bottom:4px;">'
                        . '<span style="font-size:11px;color:#888;font-weight:600;text-transform:uppercase;margin-right:5px;">%3$s</span>'
                        . '<code id="mits-advsc-%1$d" style="background:#1e1e1e;color:#7ec8e3;padding:2px 6px;border-radius:4px;font-size:12px;cursor:pointer;user-select:all;" '
                        . 'title="Клікни, щоб скопіювати" onclick="navigator.clipboard.writeText(this.innerText).then(function(){var el=document.getElementById(\'mits-advsc-%1$d\');el.style.color=\'#6dbf6d\';setTimeout(function(){el.style.color=\'#7ec8e3\';},1200);})">%2$s</code>'
                        . '</div>',
                        $tid,
                        esc_html( $sc ),
                        esc_html( $lang )
                    );
                }
            } else {
                $sc = '[mits_advantage id="' . $post_id . '"]';
                printf(
                    '<code id="mits-advsc-%1$d" style="background:#1e1e1e;color:#7ec8e3;padding:3px 7px;border-radius:4px;font-size:12px;cursor:pointer;user-select:all;" '
                    . 'title="Клікни, щоб скопіювати" onclick="navigator.clipboard.writeText(this.innerText).then(function(){var el=document.getElementById(\'mits-advsc-%1$d\');el.style.color=\'#6dbf6d\';setTimeout(function(){el.style.color=\'#7ec8e3\';},1200);})">%2$s</code>',
                    $post_id,
                    esc_html( $sc )
                );
            }
            break;

        case 'menu_order':
            echo (int) get_post_field( 'menu_order', $post_id );
            break;
    }
}, 10, 2 );

add_filter( 'manage_edit-mits_advantage_sortable_columns', function ( array $cols ): array {
    $cols['menu_order'] = 'menu_order';
    return $cols;
} );

add_action( 'pre_get_posts', function ( WP_Query $q ): void {
    if ( ! is_admin() || ! $q->is_main_query() ) {
        return;
    }
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || $screen->post_type !== 'mits_advantage' ) {
        return;
    }
    if ( ! $q->get( 'orderby' ) ) {
        $q->set( 'orderby', 'menu_order' );
        $q->set( 'order', 'ASC' );
    }
} );

/* ═══════════════════════════════════════════════════════════════════
   7. SETTINGS (inline on list screen via admin_notices)
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_init', function (): void {
    register_setting( 'mits_advantages_group', 'mits_advantages', 'mits_adv_sanitize_opts' );
} );

function mits_adv_sanitize_opts( $input ): array {
    $clean = array();
    foreach ( array( 'title', 'title_en' ) as $f ) {
        $clean[ $f ] = isset( $input[ $f ] ) ? sanitize_text_field( wp_unslash( $input[ $f ] ) ) : '';
    }
    return $clean;
}

add_action( 'admin_notices', function (): void {
    $screen = get_current_screen();
    if ( ! $screen || $screen->base !== 'edit' || $screen->post_type !== 'mits_advantage' ) {
        return;
    }
    $opts = get_option( 'mits_advantages', array() );
    if ( isset( $_GET['settings-updated'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>Налаштування збережено.</p></div>';
    }
    ?>
    <div style="background:#1e1e1e;border:1px solid #333;border-radius:8px;padding:20px 24px;margin:16px 0;">
        <strong style="color:#fff;font-size:15px;display:block;margin-bottom:14px;">⚙ Налаштування секції «Переваги»</strong>
        <form method="post" action="options.php" style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-end;">
            <?php settings_fields( 'mits_advantages_group' ); ?>
            <label style="color:#ccc;font-size:13px;display:flex;flex-direction:column;gap:4px;">
                Заголовок (UK)
                <input type="text" name="mits_advantages[title]"
                       value="<?php echo esc_attr( $opts['title'] ?? 'Переваги' ); ?>"
                       style="width:220px;padding:6px 10px;border-radius:6px;border:1px solid #444;background:#111;color:#fff;">
            </label>
            <label style="color:#ccc;font-size:13px;display:flex;flex-direction:column;gap:4px;">
                Заголовок (EN)
                <input type="text" name="mits_advantages[title_en]"
                       value="<?php echo esc_attr( $opts['title_en'] ?? 'Advantages' ); ?>"
                       style="width:220px;padding:6px 10px;border-radius:6px;border:1px solid #444;background:#111;color:#fff;">
            </label>
            <?php submit_button( 'Зберегти', 'primary small', 'submit', false ); ?>
        </form>
    </div>
    <?php
} );

/* ═══════════════════════════════════════════════════════════════════
   8. SHORTCODE [mits_advantage id="X"]
═══════════════════════════════════════════════════════════════════ */

add_shortcode( 'mits_advantage', function ( $atts ): string {
    $atts    = shortcode_atts( array( 'id' => 0 ), $atts );
    $post_id = (int) $atts['id'];
    if ( ! $post_id ) {
        return '';
    }
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'mits_advantage' || $post->post_status !== 'publish' ) {
        return '';
    }
    $details = get_post_meta( $post_id, '_adv_details', true );
    ob_start();
    ?>
    <div class="mits-adv-item">
        <button class="mits-adv-item__header" aria-expanded="false"<?php if ( ! $details ) echo ' data-no-content'; ?>>
            <span><?php echo esc_html( $post->post_title ); ?></span>
            <?php if ( $details ) : ?>
            <span class="mits-adv-item__chevron">›</span>
            <?php endif; ?>
        </button>
        <?php if ( $details ) : ?>
        <div class="mits-adv-item__body">
            <div class="mits-adv-item__body-inner"><?php echo wp_kses_post( $details ); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
} );

/* ═══════════════════════════════════════════════════════════════════
   9. SEED DEFAULT POSTS (runs once)
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_init', function (): void {
    if ( get_option( 'mits_adv_defaults_created' ) ) {
        return;
    }
    if ( ! post_type_exists( 'mits_advantage' ) ) {
        return;
    }

    $defaults = array(
        1 => array( 'uk' => 'Можливість навчатися закордоном',       'en' => 'Opportunity to study abroad' ),
        2 => array( 'uk' => 'Майбутні професії після нашої кафедри', 'en' => 'Future careers after our department' ),
        3 => array( 'uk' => 'ПЗ в якому навчаємо працювати',         'en' => 'Software we teach to work with' ),
        4 => array( 'uk' => 'Навчаємо вирішувати реальні задачі',    'en' => 'We teach to solve real-world tasks' ),
        5 => array( 'uk' => 'Цікаві особливості лише у нас',         'en' => 'Unique features only with us' ),
    );

    $lang_set = function_exists( 'pll_set_post_language' );

    foreach ( $defaults as $order => $titles ) {
        $uk_id = wp_insert_post( array(
            'post_type'   => 'mits_advantage',
            'post_title'  => $titles['uk'],
            'post_status' => 'publish',
            'menu_order'  => $order,
        ) );
        $en_id = wp_insert_post( array(
            'post_type'   => 'mits_advantage',
            'post_title'  => $titles['en'],
            'post_status' => 'publish',
            'menu_order'  => $order,
        ) );

        if ( $lang_set && ! is_wp_error( $uk_id ) && ! is_wp_error( $en_id ) ) {
            pll_set_post_language( $uk_id, 'uk' );
            pll_set_post_language( $en_id, 'en' );
            pll_save_post_translations( array( 'uk' => $uk_id, 'en' => $en_id ) );
        }
    }

    update_option( 'mits_adv_defaults_created', 1 );
} );
