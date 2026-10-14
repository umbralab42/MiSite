<?php
/**
 * Plugin Name: MITS Activities
 * Description: CPT «mits_activity» для карток секції «Діяльність» — таблиця, редагування, Polylang.
 * Version:     2.0.0
 * Author:      MITS
 * Text Domain: mits-activities
 *
 * @package MITS_Activities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════════════════════════
   1. CPT REGISTRATION
═══════════════════════════════════════════════════════════════════ */

add_action( 'init', function (): void {
    register_post_type(
        'mits_activity',
        array(
            'labels'          => array(
                'name'               => 'Діяльність',
                'singular_name'      => 'Картка діяльності',
                'add_new'            => 'Додати картку',
                'add_new_item'       => 'Нова картка',
                'edit_item'          => 'Редагувати картку',
                'all_items'          => 'Всі картки',
                'search_items'       => 'Пошук карток',
                'not_found'          => 'Карток не знайдено',
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
   2. POLYLANG — register CPT so each card can have UK + EN versions
═══════════════════════════════════════════════════════════════════ */

add_filter( 'pll_get_post_types', function ( array $types ): array {
    $types['mits_activity'] = 'mits_activity';
    return $types;
} );

/* ═══════════════════════════════════════════════════════════════════
   2.5 SHORTCODE  [mits_activity id="X"]
═══════════════════════════════════════════════════════════════════ */

add_shortcode( 'mits_activity', function ( $atts ): string {
    $atts    = shortcode_atts( array( 'id' => 0 ), $atts );
    $post_id = (int) $atts['id'];
    if ( ! $post_id ) {
        return '';
    }
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'mits_activity' || $post->post_status !== 'publish' ) {
        return '';
    }

    $icon_url = get_post_meta( $post_id, '_act_icon_url', true );
    $icon_bg  = get_post_meta( $post_id, '_act_icon_bg',  true ) ?: 'transparent';
    $desc     = get_post_meta( $post_id, '_act_desc',     true );
    $btn      = get_post_meta( $post_id, '_act_btn',      true );
    $url      = get_post_meta( $post_id, '_act_url',      true );

    ob_start();
    ?>
    <div class="mits-card">
        <div class="mits-card__header">
            <?php if ( $icon_url ) : ?>
            <div class="mits-card__icon" style="background-color:<?php echo esc_attr( $icon_bg ); ?>">
                <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>">
            </div>
            <?php endif; ?>
            <h3><?php echo esc_html( $post->post_title ); ?></h3>
        </div>
        <?php if ( $desc ) : ?>
        <p><?php echo esc_html( $desc ); ?></p>
        <?php endif; ?>
        <?php if ( $btn && $url ) : ?>
        <a href="<?php echo esc_url( $url ); ?>" class="mits-btn mits-btn--outline"><?php echo esc_html( $btn ); ?></a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
} );

/* ═══════════════════════════════════════════════════════════════════
   3. ADMIN MENU under "MITS Content"
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', function (): void {
    add_submenu_page(
        'mits-pages',
        'Діяльність',
        'Діяльність',
        'edit_posts',
        'edit.php?post_type=mits_activity'
    );
}, 999 );

/* Keep MITS Content highlighted when editing an activity post */
add_filter( 'parent_file', function ( string $parent ): string {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'mits_activity' ) {
        return 'mits-pages';
    }
    return $parent;
} );

add_filter( 'submenu_file', function ( ?string $sub ): ?string {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'mits_activity' ) {
        return 'edit.php?post_type=mits_activity';
    }
    return $sub;
} );

/* ═══════════════════════════════════════════════════════════════════
   4. META BOX on edit screen
═══════════════════════════════════════════════════════════════════ */

add_action( 'add_meta_boxes', function (): void {
    add_meta_box(
        'mits_act_fields',
        'Параметри картки',
        'mits_act_meta_box_render',
        'mits_activity',
        'normal',
        'high'
    );
} );

function mits_act_meta_box_render( WP_Post $post ): void {
    wp_nonce_field( 'mits_act_save', 'mits_act_nonce' );

    $icon_url = get_post_meta( $post->ID, '_act_icon_url', true );
    $icon_bg  = get_post_meta( $post->ID, '_act_icon_bg',  true ) ?: '#58aba9';
    $desc     = get_post_meta( $post->ID, '_act_desc',     true );
    $btn      = get_post_meta( $post->ID, '_act_btn',      true ) ?: 'Детальніше';
    $url      = get_post_meta( $post->ID, '_act_url',      true ) ?: '#';
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:160px"><label>Іконка</label></th>
            <td>
                <div id="mits-act-icon-preview" style="margin-bottom:8px;">
                    <?php if ( $icon_url ) : ?>
                        <img src="<?php echo esc_url( $icon_url ); ?>"
                             style="max-width:72px;max-height:72px;border-radius:12px;">
                    <?php endif; ?>
                </div>
                <input type="hidden" id="mits_act_icon_url" name="mits_act_icon_url"
                       value="<?php echo esc_attr( $icon_url ); ?>">
                <button type="button" class="button" id="mits-act-upload-btn">
                    <?php echo $icon_url ? 'Замінити іконку' : 'Вибрати іконку'; ?>
                </button>
                <button type="button" class="button" id="mits-act-remove-btn"
                        style="margin-left:4px;<?php echo $icon_url ? '' : 'display:none;'; ?>">
                    Видалити
                </button>
                <p class="description">PNG або SVG, 80×80 px.</p>
            </td>
        </tr>
        <tr>
            <th><label for="mits_act_icon_bg">Колір фону іконки</label></th>
            <td>
                <input type="text" id="mits_act_icon_bg" name="mits_act_icon_bg"
                       value="<?php echo esc_attr( $icon_bg ); ?>"
                       class="mits-act-color"
                       data-default-color="<?php echo esc_attr( $icon_bg ); ?>">
            </td>
        </tr>
        <tr>
            <th><label for="mits_act_desc">Опис</label></th>
            <td>
                <textarea id="mits_act_desc" name="mits_act_desc"
                          rows="3" class="large-text"><?php echo esc_textarea( $desc ); ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="mits_act_btn">Текст кнопки</label></th>
            <td>
                <input type="text" id="mits_act_btn" name="mits_act_btn"
                       value="<?php echo esc_attr( $btn ); ?>" class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="mits_act_url">Посилання (URL)</label></th>
            <td>
                <input type="url" id="mits_act_url" name="mits_act_url"
                       value="<?php echo esc_attr( $url ); ?>" class="large-text">
            </td>
        </tr>
    </table>

    <script>
    jQuery(function ($) {
        $('.mits-act-color').wpColorPicker();

        $('#mits-act-upload-btn').on('click', function (e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Вибрати іконку',
                button: { text: 'Вибрати' },
                multiple: false
            });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                $('#mits_act_icon_url').val(att.url);
                $('#mits-act-icon-preview').html(
                    '<img src="' + att.url + '" style="max-width:72px;max-height:72px;border-radius:12px;">'
                );
                $('#mits-act-upload-btn').text('Замінити іконку');
                $('#mits-act-remove-btn').show();
            });
            frame.open();
        });

        $('#mits-act-remove-btn').on('click', function () {
            $('#mits_act_icon_url').val('');
            $('#mits-act-icon-preview').html('');
            $('#mits-act-upload-btn').text('Вибрати іконку');
            $(this).hide();
        });
    });
    </script>
    <?php
}

/* ═══════════════════════════════════════════════════════════════════
   5. SAVE META
═══════════════════════════════════════════════════════════════════ */

add_action( 'save_post_mits_activity', function ( int $post_id ): void {
    if ( ! isset( $_POST['mits_act_nonce'] ) ||
         ! wp_verify_nonce( sanitize_key( $_POST['mits_act_nonce'] ), 'mits_act_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    update_post_meta( $post_id, '_act_icon_url',
        esc_url_raw( wp_unslash( $_POST['mits_act_icon_url'] ?? '' ) ) );

    $icon_bg = sanitize_text_field( wp_unslash( $_POST['mits_act_icon_bg'] ?? '' ) );
    if ( preg_match( '/^#[0-9a-fA-F]{3,6}$/', $icon_bg ) ) {
        update_post_meta( $post_id, '_act_icon_bg', $icon_bg );
    }

    update_post_meta( $post_id, '_act_desc',
        wp_kses_post( wp_unslash( $_POST['mits_act_desc'] ?? '' ) ) );

    update_post_meta( $post_id, '_act_btn',
        sanitize_text_field( wp_unslash( $_POST['mits_act_btn'] ?? '' ) ) );

    update_post_meta( $post_id, '_act_url',
        esc_url_raw( wp_unslash( $_POST['mits_act_url'] ?? '' ) ) );
} );

/* ═══════════════════════════════════════════════════════════════════
   6. ADMIN COLUMNS
   Priority 20 — runs after Polylang adds its "languages" column.
═══════════════════════════════════════════════════════════════════ */

add_filter( 'manage_mits_activity_posts_columns', function ( array $cols ): array {
    $new = array();
    $new['cb']           = $cols['cb'] ?? '<input type="checkbox">';
    $new['act_icon']     = 'Іконка';
    $new['title']        = $cols['title'] ?? 'Назва';
    $new['act_desc']     = 'Опис';
    $new['act_btn']      = 'Кнопка';
    $new['act_url']      = 'Посилання';
    $new['act_shortcode'] = 'Шорткод';
    $new['menu_order']   = 'Порядок';
    if ( isset( $cols['languages'] ) ) {
        $new['languages'] = $cols['languages'];
    }
    return $new;
}, 20 );

add_action( 'manage_mits_activity_posts_custom_column', function ( string $col, int $post_id ): void {
    switch ( $col ) {
        case 'act_icon':
            $url = get_post_meta( $post_id, '_act_icon_url', true );
            $bg  = get_post_meta( $post_id, '_act_icon_bg',  true );
            if ( $url ) {
                $style = $bg
                    ? 'background:' . esc_attr( $bg ) . ';border-radius:8px;padding:3px;'
                    : '';
                printf(
                    '<img src="%s" style="width:40px;height:40px;object-fit:contain;%s">',
                    esc_url( $url ),
                    $style
                );
            } elseif ( $bg ) {
                printf(
                    '<div style="width:40px;height:40px;border-radius:8px;background:%s;"></div>',
                    esc_attr( $bg )
                );
            } else {
                echo '—';
            }
            break;

        case 'act_desc':
            $desc = get_post_meta( $post_id, '_act_desc', true );
            echo $desc ? esc_html( wp_trim_words( $desc, 8, '…' ) ) : '—';
            break;

        case 'act_btn':
            echo esc_html( get_post_meta( $post_id, '_act_btn', true ) ?: '—' );
            break;

        case 'act_url':
            $url = get_post_meta( $post_id, '_act_url', true );
            if ( $url && $url !== '#' ) {
                printf(
                    '<a href="%s" target="_blank" style="word-break:break-all">%s</a>',
                    esc_url( $url ),
                    esc_html( wp_trim_words( $url, 4, '…' ) )
                );
            } else {
                echo esc_html( $url ?: '—' );
            }
            break;

        case 'act_shortcode':
            if ( function_exists( 'pll_get_post_translations' ) ) {
                $translations = pll_get_post_translations( $post_id );
                foreach ( $translations as $lang => $translated_id ) {
                    $sc = '[mits_activity id="' . $translated_id . '"]';
                    printf(
                        '<div style="margin-bottom:4px;">'
                        . '<span style="font-size:11px;color:#888;font-weight:600;text-transform:uppercase;margin-right:5px;">%3$s</span>'
                        . '<code id="mits-asc-%1$d" style="background:#1e1e1e;color:#7ec8e3;padding:2px 6px;border-radius:4px;font-size:12px;cursor:pointer;user-select:all;" '
                        . 'title="Клікни, щоб скопіювати" onclick="navigator.clipboard.writeText(this.innerText).then(function(){var el=document.getElementById(\'mits-asc-%1$d\');el.style.color=\'#6dbf6d\';setTimeout(function(){el.style.color=\'#7ec8e3\';},1200);})">%2$s</code>'
                        . '</div>',
                        $translated_id,
                        esc_html( $sc ),
                        esc_html( $lang )
                    );
                }
            } else {
                $sc = '[mits_activity id="' . $post_id . '"]';
                printf(
                    '<code id="mits-asc-%1$d" style="background:#1e1e1e;color:#7ec8e3;padding:3px 7px;border-radius:4px;font-size:12px;cursor:pointer;user-select:all;" '
                    . 'title="Клікни, щоб скопіювати" onclick="navigator.clipboard.writeText(this.innerText).then(function(){var el=document.getElementById(\'mits-asc-%1$d\');el.style.color=\'#6dbf6d\';setTimeout(function(){el.style.color=\'#7ec8e3\';},1200);})">%2$s</code>',
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

add_filter( 'manage_edit-mits_activity_sortable_columns', function ( array $cols ): array {
    $cols['menu_order'] = 'menu_order';
    return $cols;
} );

/* Default sort by menu_order ASC in admin list */
add_action( 'pre_get_posts', function ( WP_Query $q ): void {
    if ( ! is_admin() || ! $q->is_main_query() ) {
        return;
    }
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || $screen->post_type !== 'mits_activity' ) {
        return;
    }
    if ( ! $q->get( 'orderby' ) ) {
        $q->set( 'orderby', 'menu_order' );
        $q->set( 'order', 'ASC' );
    }
} );

/* ═══════════════════════════════════════════════════════════════════
   7. ENQUEUE MEDIA + COLOR PICKER on edit screens
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'mits_activity' ) {
        return;
    }
    if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }
} );

/* ═══════════════════════════════════════════════════════════════════
   8. CREATE DEFAULT POSTS (UK + EN) on first admin load
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_init', function (): void {
    if ( get_option( 'mits_act_defaults_created' ) ) {
        return;
    }

    $count = wp_count_posts( 'mits_activity' );
    $total = (int) $count->publish + (int) $count->draft + (int) $count->private;
    if ( $total > 0 ) {
        update_option( 'mits_act_defaults_created', 1 );
        return;
    }

    $cards = array(
        array(
            'uk_title' => 'Вступ',
            'en_title' => 'Admission',
            'uk_desc'  => 'Вся інформація стосовно вступу на бакалаврат та магістратуру (умови, терміни, документи).',
            'en_desc'  => "All information about admission to bachelor's and master's programs (conditions, deadlines, documents).",
            'uk_btn'   => 'Детальніше',
            'en_btn'   => 'Learn more',
            'icon_bg'  => '#58aba9',
        ),
        array(
            'uk_title' => 'Освіта',
            'en_title' => 'Education',
            'uk_desc'  => 'Навчальний план, графік навчального процесу, розклад, лабораторії кафедри, архів файлів.',
            'en_desc'  => 'Curriculum, academic schedule, timetable, department labs, file archive.',
            'uk_btn'   => 'Детальніше',
            'en_btn'   => 'Learn more',
            'icon_bg'  => '#df742d',
        ),
        array(
            'uk_title' => 'Наука',
            'en_title' => 'Science',
            'uk_desc'  => 'Наукові напрямки, студентські наукові гуртки, конференції, міжнародна співпраця.',
            'en_desc'  => 'Research areas, student science clubs, conferences, international cooperation.',
            'uk_btn'   => 'Детальніше',
            'en_btn'   => 'Learn more',
            'icon_bg'  => '#e662a2',
        ),
    );

    foreach ( $cards as $i => $card ) {
        $uk_id = wp_insert_post( array(
            'post_title'  => $card['uk_title'],
            'post_status' => 'publish',
            'post_type'   => 'mits_activity',
            'menu_order'  => $i + 1,
        ) );
        if ( is_wp_error( $uk_id ) ) {
            continue;
        }
        update_post_meta( $uk_id, '_act_icon_bg', $card['icon_bg'] );
        update_post_meta( $uk_id, '_act_desc',    $card['uk_desc'] );
        update_post_meta( $uk_id, '_act_btn',     $card['uk_btn'] );
        update_post_meta( $uk_id, '_act_url',     '#' );
        if ( function_exists( 'pll_set_post_language' ) ) {
            pll_set_post_language( $uk_id, 'uk' );
        }

        $en_id = wp_insert_post( array(
            'post_title'  => $card['en_title'],
            'post_status' => 'publish',
            'post_type'   => 'mits_activity',
            'menu_order'  => $i + 1,
        ) );
        if ( is_wp_error( $en_id ) ) {
            continue;
        }
        update_post_meta( $en_id, '_act_icon_bg', $card['icon_bg'] );
        update_post_meta( $en_id, '_act_desc',    $card['en_desc'] );
        update_post_meta( $en_id, '_act_btn',     $card['en_btn'] );
        update_post_meta( $en_id, '_act_url',     '#' );
        if ( function_exists( 'pll_set_post_language' ) ) {
            pll_set_post_language( $en_id, 'en' );
        }

        if ( function_exists( 'pll_save_post_translations' ) ) {
            pll_save_post_translations( array( 'uk' => $uk_id, 'en' => $en_id ) );
        }
    }

    update_option( 'mits_act_defaults_created', 1 );
} );

/* ═══════════════════════════════════════════════════════════════════
   9. SETTINGS PAGE — section title + CTA button
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_init', function (): void {
    register_setting( 'mits_activities_group', 'mits_activities', 'mits_act_sanitize_opts' );
} );

function mits_act_sanitize_opts( array $input ): array {
    $clean = array();
    foreach ( array( 'title', 'title_en', 'cta_text', 'cta_text_en' ) as $f ) {
        $clean[ $f ] = sanitize_text_field( wp_unslash( $input[ $f ] ?? '' ) );
    }
    $clean['cta_url'] = esc_url_raw( wp_unslash( $input['cta_url'] ?? '' ) );
    return $clean;
}

/* Settings panel embedded directly on the CPT list screen via admin_notices.
   options.php redirects back to HTTP_REFERER (the list page) after save. */
add_action( 'admin_notices', function (): void {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'mits_activity' || $screen->base !== 'edit' ) {
        return;
    }

    $defaults = array(
        'title'       => 'Діяльність',
        'title_en'    => 'Activities',
        'cta_text'    => 'Навчальна програма',
        'cta_text_en' => 'Curriculum',
        'cta_url'     => '#',
    );
    $opts = wp_parse_args( get_option( 'mits_activities', array() ), $defaults );
    $v    = static fn ( string $k ): string => esc_attr( $opts[ $k ] ?? '' );

    if ( isset( $_GET['settings-updated'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>Налаштування збережено.</p></div>';
    }
    ?>
    <div class="notice notice-info" style="padding:16px 16px 12px;">
        <strong style="display:block;margin-bottom:10px;">Налаштування секції «Діяльність»</strong>
        <form method="post" action="options.php" style="display:flex;flex-wrap:wrap;gap:16px 24px;align-items:flex-end;">
            <?php settings_fields( 'mits_activities_group' ); ?>
            <label style="display:flex;flex-direction:column;gap:4px;font-weight:600;">
                Заголовок (UK)
                <input type="text" name="mits_activities[title]"
                       value="<?php echo $v( 'title' ); ?>" style="width:180px;font-weight:normal;">
            </label>
            <label style="display:flex;flex-direction:column;gap:4px;font-weight:600;">
                Title (EN)
                <input type="text" name="mits_activities[title_en]"
                       value="<?php echo $v( 'title_en' ); ?>" style="width:180px;font-weight:normal;">
            </label>
            <label style="display:flex;flex-direction:column;gap:4px;font-weight:600;">
                CTA-кнопка (UK)
                <input type="text" name="mits_activities[cta_text]"
                       value="<?php echo $v( 'cta_text' ); ?>" style="width:160px;font-weight:normal;">
            </label>
            <label style="display:flex;flex-direction:column;gap:4px;font-weight:600;">
                CTA-кнопка (EN)
                <input type="text" name="mits_activities[cta_text_en]"
                       value="<?php echo $v( 'cta_text_en' ); ?>" style="width:160px;font-weight:normal;">
            </label>
            <label style="display:flex;flex-direction:column;gap:4px;font-weight:600;">
                CTA URL
                <input type="url" name="mits_activities[cta_url]"
                       value="<?php echo $v( 'cta_url' ); ?>" style="width:240px;font-weight:normal;">
            </label>
            <div>
                <?php submit_button( 'Зберегти', 'secondary small', 'submit', false ); ?>
            </div>
        </form>
    </div>
    <?php
} );
