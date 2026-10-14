<?php
/**
 * Plugin Name: MITS Вступ
 * Description: CPT «mits_edu_level» (Вступ — рівні), CPT «mits_faq» та мета-поля для сторінки Вступ.
 * Version:     2.0.0
 * Author:      MITS
 * Text Domain: mits-vstup
 *
 * @package MITS_Vstup
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════════════════════════
   1. CPT mits_edu_level — рівні освіти
═══════════════════════════════════════════════════════════════════ */

add_action( 'init', function (): void {
    register_post_type(
        'mits_edu_level',
        array(
            'labels'          => array(
                'name'               => 'Вступ — рівні',
                'singular_name'      => 'Рівень вступу',
                'add_new'            => 'Додати рівень',
                'add_new_item'       => 'Новий рівень',
                'edit_item'          => 'Редагувати рівень',
                'all_items'          => 'Всі рівні',
                'search_items'       => 'Пошук рівнів',
                'not_found'          => 'Рівнів не знайдено',
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

    /* ── CPT mits_faq ─────────────────────────────────────────── */
    register_post_type(
        'mits_faq',
        array(
            'labels'          => array(
                'name'               => 'FAQ',
                'singular_name'      => 'FAQ-питання',
                'add_new'            => 'Додати питання',
                'add_new_item'       => 'Нове питання',
                'edit_item'          => 'Редагувати питання',
                'all_items'          => 'Всі питання',
                'search_items'       => 'Пошук питань',
                'not_found'          => 'Питань не знайдено',
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
    $types['mits_edu_level'] = 'mits_edu_level';
    $types['mits_faq']       = 'mits_faq';
    return $types;
} );

/* ═══════════════════════════════════════════════════════════════════
   3. ADMIN MENU
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', function (): void {
    add_submenu_page(
        'mits-pages',
        'Вступ — рівні',
        'Вступ — рівні',
        'edit_posts',
        'edit.php?post_type=mits_edu_level'
    );
}, 999 );

add_filter( 'parent_file', function ( string $parent ): string {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'mits_edu_level' ) {
        return 'mits-pages';
    }
    if ( $screen && $screen->post_type === 'mits_faq' ) {
        return 'mits-content';
    }
    return $parent;
} );

add_filter( 'submenu_file', function ( ?string $sub ): ?string {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'mits_edu_level' ) {
        return 'edit.php?post_type=mits_edu_level';
    }
    if ( $screen && $screen->post_type === 'mits_faq' ) {
        return 'edit.php?post_type=mits_faq';
    }
    return $sub;
} );

/* ═══════════════════════════════════════════════════════════════════
   4. META BOXES
═══════════════════════════════════════════════════════════════════ */

add_action( 'add_meta_boxes', function (): void {
    // Edu level URL
    add_meta_box(
        'mits_edu_level_fields',
        'Налаштування рівня',
        'mits_edu_level_box_render',
        'mits_edu_level',
        'normal',
        'high'
    );
    // FAQ answer
    add_meta_box(
        'mits_faq_answer',
        'Відповідь',
        'mits_faq_answer_box_render',
        'mits_faq',
        'normal',
        'high'
    );
    // Vstup page settings
    add_meta_box(
        'mits_vstup_fields',
        'Параметри сторінки Вступ',
        'mits_vstup_page_box_render',
        'page',
        'normal',
        'high'
    );
    // Vstup sub-page settings (hero pin)
    add_meta_box(
        'mits_vstup_sub_fields',
        'Параметри підрозділу Вступ',
        'mits_vstup_sub_box_render',
        'page',
        'normal',
        'high'
    );
} );

function mits_edu_level_box_render( WP_Post $post ): void {
    wp_nonce_field( 'mits_edu_level_save', 'mits_edu_level_nonce' );
    $page_id     = (int) get_post_meta( $post->ID, '_edu_level_page_id', true );
    $description = get_post_meta( $post->ID, '_edu_level_description', true );
    // Load subjects: new JSON key, fallback to legacy numbered keys
    $subjects_json = get_post_meta( $post->ID, '_edu_level_subjects', true );
    if ( $subjects_json !== '' && $subjects_json !== false ) {
        $subjects = json_decode( $subjects_json, true );
        if ( ! is_array( $subjects ) ) {
            $subjects = array();
        }
    } else {
        $subjects = array();
        foreach ( array( '_edu_level_subject_1', '_edu_level_subject_2', '_edu_level_subject_3' ) as $_sk ) {
            $s = get_post_meta( $post->ID, $_sk, true );
            if ( $s !== '' ) {
                $subjects[] = $s;
            }
        }
    }
    $subject_alt   = get_post_meta( $post->ID, '_edu_level_subject_alt', true );
    $subjects_title = get_post_meta( $post->ID, '_edu_level_subjects_title', true );
    // Load tips: new JSON key, fallback to legacy numbered keys
    $tips_json = get_post_meta( $post->ID, '_edu_level_tips', true );
    if ( $tips_json !== '' && $tips_json !== false ) {
        $tips = json_decode( $tips_json, true );
        if ( ! is_array( $tips ) ) {
            $tips = array();
        }
    } else {
        // Migrate from legacy _edu_level_tip_1..5
        $tips = array();
        for ( $i = 1; $i <= 5; $i++ ) {
            $t = get_post_meta( $post->ID, '_edu_level_tip_' . $i, true );
            if ( $t !== '' ) {
                $tips[] = $t;
            }
        }
    }
    // Load algorithm: new field, no legacy fallback
    $algo_title  = get_post_meta( $post->ID, '_edu_level_algorithm_title', true );
    $tips_title  = get_post_meta( $post->ID, '_edu_level_tips_title', true );
    $steps_json  = get_post_meta( $post->ID, '_edu_level_steps', true );
    $steps       = array();
    if ( $steps_json !== '' && $steps_json !== false ) {
        $decoded = json_decode( $steps_json, true );
        if ( is_array( $decoded ) ) {
            $steps = $decoded;
        }
    }
    $algo_json  = get_post_meta( $post->ID, '_edu_level_algorithm', true );
    $algorithm  = array();
    if ( $algo_json !== '' && $algo_json !== false ) {
        $decoded = json_decode( $algo_json, true );
        if ( is_array( $decoded ) ) {
            $algorithm = $decoded;
        }
    }
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:180px"><label for="edu-level-page-id">Сторінка рівня</label></th>
            <td>
                <?php wp_dropdown_pages( array(
                    'name'              => 'edu_level_page_id',
                    'id'                => 'edu-level-page-id',
                    'selected'          => $page_id,
                    'show_option_none'  => '— не вибрано —',
                    'option_none_value' => '0',
                    'sort_column'       => 'menu_order',
                ) ); ?>
                <p class="description">Сторінка, на яку посилається кнопка на сторінці Вступ.</p>
            </td>
        </tr>
        <tr>
            <th><label for="edu-level-description">Короткий опис</label></th>
            <td>
                <?php wp_editor( $description, 'edu-level-description', array(
                    'textarea_name' => 'edu_level_description',
                    'textarea_rows' => 5,
                    'media_buttons' => false,
                    'teeny'         => false,
                    'quicktags'     => true,
                    'tinymce'       => array(
                        'toolbar1' => 'bold,italic,underline,strikethrough,|,alignleft,aligncenter,alignright,|,fontsizeselect,|,link,unlink,|,undo,redo',
                        'toolbar2' => '',
                        'fontsize_formats' => '10px 12px 14px 16px 18px 20px 24px 28px 32px 36px',
                    ),
                ) ); ?>
                <p class="description">Необов'язково. Відображається на сторінці Вступ.</p>
            </td>
        </tr>
        <tr>
            <th colspan="2" style="padding-top:16px;padding-bottom:4px">
                <strong style="font-size:13px">Кроки (після слайдера)</strong>
            </th>
        </tr>
        <tr>
            <td colspan="2" style="padding-top:0">
                <div id="mits-steps-list">
                    <?php foreach ( $steps as $_step ) : ?>
                    <div class="mits-step-row" style="display:flex;gap:8px;margin-bottom:6px;align-items:center">
                        <input type="text" name="edu_level_steps[]" class="large-text"
                               value="<?php echo esc_attr( $_step ); ?>" placeholder="Назва кроку">
                        <button type="button" class="button mits-remove-step" aria-label="Видалити крок"
                                style="flex-shrink:0">&#x2715;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="mits-add-step" class="button" style="margin-top:4px">+ Додати крок</button>
                <p class="description" style="margin-top:6px">Виводяться горизонтально з стрілками відразу після слайдера.</p>
            </td>
        </tr>
        <tr>
            <th colspan="2" style="padding-bottom:4px">
                <strong style="font-size:13px">Предмети вступних випробувань</strong>
            </th>
        </tr>
        <tr>
            <th><label for="edu-level-subjects-title">Заголовок секції</label></th>
            <td>
                <input type="text" id="edu-level-subjects-title" name="edu_level_subjects_title"
                       value="<?php echo esc_attr( $subjects_title ); ?>" class="large-text"
                       placeholder="Обов'язкові предмети НМТ:">
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top:0">
                <div id="mits-subjects-list">
                    <?php foreach ( $subjects as $_subj ) : ?>
                    <div class="mits-subject-row" style="display:flex;gap:8px;margin-bottom:6px;align-items:center">
                        <input type="text" name="edu_level_subjects[]" class="large-text"
                               value="<?php echo esc_attr( $_subj ); ?>" placeholder="Назва предмету">
                        <button type="button" class="button mits-remove-subject" aria-label="Видалити предмет"
                                style="flex-shrink:0">&#x2715;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="mits-add-subject" class="button" style="margin-top:4px">+ Додати предмет</button>
            </td>
        </tr>
        <tr>
            <th><label for="edu-level-subject-alt">Коментар до предметів</label></th>
            <td>
                <input type="text" id="edu-level-subject-alt" name="edu_level_subject_alt"
                       value="<?php echo esc_attr( $subject_alt ); ?>" class="large-text"
                       placeholder="Необов'язково">
                <p class="description">Необов'язково. Виводиться після списку предметів.</p>
            </td>
        </tr>
        <tr>
            <th colspan="2" style="padding-bottom:4px">
                <strong style="font-size:13px">Важливі поради</strong>
            </th>
        </tr>
        <tr>
            <th><label for="edu-level-tips-title">Заголовок секції</label></th>
            <td>
                <input type="text" id="edu-level-tips-title" name="edu_level_tips_title"
                       value="<?php echo esc_attr( $tips_title ); ?>" class="large-text"
                       placeholder="Важливо">
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top:0">
                <div id="mits-tips-list">
                    <?php foreach ( $tips as $_tip ) : ?>
                    <div class="mits-tip-row" style="display:flex;gap:8px;margin-bottom:6px;align-items:center">
                        <input type="text" name="edu_level_tips[]" class="large-text"
                               value="<?php echo esc_attr( $_tip ); ?>" placeholder="Текст поради">
                        <button type="button" class="button mits-remove-tip" aria-label="Видалити пораду"
                                style="flex-shrink:0">&#x2715;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="mits-add-tip" class="button" style="margin-top:4px">+ Додати пораду</button>
            </td>
        </tr>
        <tr>
            <th colspan="2" style="padding-top:16px;padding-bottom:4px">
                <strong style="font-size:13px">Алгоритм вступу</strong>
            </th>
        </tr>
        <tr>
            <th><label for="edu-level-algo-title">Заголовок секції</label></th>
            <td>
                <input type="text" id="edu-level-algo-title" name="edu_level_algorithm_title"
                       value="<?php echo esc_attr( $algo_title ); ?>" class="large-text"
                       placeholder="Алгоритм вступу на бакалавра">
                <p class="description">Виводиться як заголовок секції. Приклад: «Алгоритм вступу на бакалавра».</p>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top:0">
                <div id="mits-algo-list">
                    <?php foreach ( $algorithm as $_step ) : ?>
                    <div class="mits-algo-row" style="display:flex;gap:8px;margin-bottom:8px;align-items:flex-start">
                        <div class="mits-algo-editor" style="flex:1">
                            <div class="mits-algo-toolbar" style="display:flex;gap:3px;margin-bottom:3px">
                                <button type="button" class="button button-small mits-fmt-btn" data-tag="strong" title="Жирний" style="min-width:28px;font-weight:bold">B</button>
                                <button type="button" class="button button-small mits-fmt-btn" data-tag="em" title="Курсив" style="min-width:28px;font-style:italic">I</button>
                                <button type="button" class="button button-small mits-fmt-btn" data-tag="u" title="Підкреслений" style="min-width:28px;text-decoration:underline">U</button>
                                <button type="button" class="button button-small mits-fmt-btn" data-tag="a" title="Посилання" style="min-width:28px">&#x1F517;</button>
                                <button type="button" class="button button-small mits-fmt-btn" data-tag="br" title="Перенос рядка" style="min-width:34px">&#x21B5;</button>
                                <button type="button" class="button button-small mits-fmt-btn" data-tag="ul" title="Список" style="min-width:28px">&#x2022;&#x2013;</button>
                                <label class="button button-small mits-fmt-color-label" title="Колір тексту" style="min-width:36px;padding:0 4px;display:inline-flex;align-items:center;gap:3px;cursor:pointer"><b>A</b><input type="color" class="mits-color-input" value="#e040fb" style="width:16px;height:16px;padding:0;border:0;cursor:pointer;vertical-align:middle"></label>
                            </div>
                            <textarea name="edu_level_algorithm[]" class="large-text" rows="3"
                                      placeholder="Текст кроку"><?php echo esc_textarea( $_step ); ?></textarea>
                        </div>
                        <button type="button" class="button mits-remove-algo" aria-label="Видалити крок"
                                style="flex-shrink:0;margin-top:26px">&#x2715;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="mits-add-algo" class="button" style="margin-top:4px">+ Додати крок</button>
                <p class="description" style="margin-top:6px">Виділіть текст і натисніть <strong>B</strong>&nbsp;/<em>I</em>&nbsp;/<u>U</u>&nbsp;/&#x1F517;&nbsp;/&#x21B5;&nbsp;/&#x2022;&#x2013;&nbsp;/<b style="color:#e040fb">A</b> для форматування. Допустимий HTML: &lt;strong&gt; &lt;em&gt; &lt;u&gt; &lt;a&gt; &lt;br&gt; &lt;ul&gt;&lt;li&gt; &lt;span style&gt;.</p>
            </td>
        </tr>
    </table>
    <?php
}

function mits_faq_answer_box_render( WP_Post $post ): void {
    wp_nonce_field( 'mits_faq_save', 'mits_faq_nonce' );
    $answer = get_post_meta( $post->ID, '_faq_answer', true );
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:160px"><label for="mits-faq-answer">Текст відповіді</label></th>
            <td>
                <textarea id="mits-faq-answer" name="mits_faq_answer" rows="8"
                          style="width:100%"><?php echo esc_textarea( $answer ); ?></textarea>
                <p class="description">Допустимий HTML: &lt;p&gt; &lt;strong&gt; &lt;em&gt; &lt;ul&gt; &lt;li&gt; &lt;a&gt;.</p>
            </td>
        </tr>
    </table>
    <?php
}

function mits_vstup_page_box_render( WP_Post $post ): void {
    if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'template-mits-vstup.php' ) {
        echo '<p style="color:#999">Показується тільки для сторінок із шаблоном «MITS — Вступ».</p>';
        return;
    }
    wp_nonce_field( 'mits_vstup_page_save', 'mits_vstup_page_nonce' );
    $video_url = get_post_meta( $post->ID, '_vstup_video_url', true );
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:200px"><label for="vstup-video-url">Посилання на YouTube</label></th>
            <td>
                <input type="url" id="vstup-video-url" name="vstup_video_url"
                       value="<?php echo esc_attr( $video_url ); ?>" class="large-text"
                       placeholder="https://www.youtube.com/watch?v=...">
                <p class="description">Звичайне посилання на відео або youtu.be/... — буде автоматично перетворено в embed.</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="description" style="margin-top:4px">
                    Кнопки освітніх рівнів керуються через меню
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mits_edu_level' ) ); ?>">Вступ — рівні</a>.
                </p>
            </td>
        </tr>
    </table>
    <?php
}

/* ═══════════════════════════════════════════════════════════════════
   5. SAVE META
═══════════════════════════════════════════════════════════════════ */

add_action( 'save_post_mits_edu_level', function ( int $post_id ): void {
    if (
        ! isset( $_POST['mits_edu_level_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_edu_level_nonce'] ) ), 'mits_edu_level_save' )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        || ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }
    if ( isset( $_POST['edu_level_page_id'] ) ) {
        update_post_meta( $post_id, '_edu_level_page_id', absint( $_POST['edu_level_page_id'] ) );
    }
    if ( isset( $_POST['edu_level_description'] ) ) {
        update_post_meta( $post_id, '_edu_level_description', wp_kses_post( wp_unslash( $_POST['edu_level_description'] ) ) );
    }
    // Save comment field
    if ( isset( $_POST['edu_level_subject_alt'] ) ) {
        update_post_meta( $post_id, '_edu_level_subject_alt', sanitize_text_field( wp_unslash( $_POST['edu_level_subject_alt'] ) ) );
    }
    // Save subjects section title
    if ( isset( $_POST['edu_level_subjects_title'] ) ) {
        update_post_meta( $post_id, '_edu_level_subjects_title', sanitize_text_field( wp_unslash( $_POST['edu_level_subjects_title'] ) ) );
    }
    // Save steps as JSON array
    $steps = array();
    if ( isset( $_POST['edu_level_steps'] ) && is_array( $_POST['edu_level_steps'] ) ) {
        foreach ( $_POST['edu_level_steps'] as $step ) {
            $step = sanitize_text_field( wp_unslash( $step ) );
            if ( $step !== '' ) {
                $steps[] = $step;
            }
        }
    }
    update_post_meta( $post_id, '_edu_level_steps', wp_json_encode( $steps, JSON_UNESCAPED_UNICODE ) );
    // Save dynamic subjects as JSON array
    $subjects = array();
    if ( isset( $_POST['edu_level_subjects'] ) && is_array( $_POST['edu_level_subjects'] ) ) {
        foreach ( $_POST['edu_level_subjects'] as $s ) {
            $s = sanitize_text_field( wp_unslash( $s ) );
            if ( $s !== '' ) {
                $subjects[] = $s;
            }
        }
    }
    update_post_meta( $post_id, '_edu_level_subjects', wp_json_encode( $subjects, JSON_UNESCAPED_UNICODE ) );
    // Remove legacy numbered keys
    foreach ( array( '_edu_level_subject_1', '_edu_level_subject_2', '_edu_level_subject_3' ) as $_sk ) {
        delete_post_meta( $post_id, $_sk );
    }
    // Save dynamic tips as JSON array
    $tips = array();
    if ( isset( $_POST['edu_level_tips'] ) && is_array( $_POST['edu_level_tips'] ) ) {
        foreach ( $_POST['edu_level_tips'] as $t ) {
            $t = sanitize_text_field( wp_unslash( $t ) );
            if ( $t !== '' ) {
                $tips[] = $t;
            }
        }
    }
    update_post_meta( $post_id, '_edu_level_tips', wp_json_encode( $tips, JSON_UNESCAPED_UNICODE ) );
    // Remove legacy numbered keys
    for ( $i = 1; $i <= 5; $i++ ) {
        delete_post_meta( $post_id, '_edu_level_tip_' . $i );
    }
    // Save algorithm title
    if ( isset( $_POST['edu_level_algorithm_title'] ) ) {
        update_post_meta( $post_id, '_edu_level_algorithm_title', sanitize_text_field( wp_unslash( $_POST['edu_level_algorithm_title'] ) ) );
    }
    // Save tips title
    if ( isset( $_POST['edu_level_tips_title'] ) ) {
        update_post_meta( $post_id, '_edu_level_tips_title', sanitize_text_field( wp_unslash( $_POST['edu_level_tips_title'] ) ) );
    }
    // Save algorithm steps as JSON (basic HTML allowed)
    $algorithm = array();
    if ( isset( $_POST['edu_level_algorithm'] ) && is_array( $_POST['edu_level_algorithm'] ) ) {
        foreach ( $_POST['edu_level_algorithm'] as $step ) {
            $step = wp_kses_post( wp_unslash( $step ) );
            if ( trim( wp_strip_all_tags( $step ) ) !== '' ) {
                $algorithm[] = $step;
            }
        }
    }
    update_post_meta( $post_id, '_edu_level_algorithm', wp_slash( wp_json_encode( $algorithm, JSON_UNESCAPED_UNICODE ) ) );
} );

add_action( 'save_post_mits_faq', function ( int $post_id ): void {
    if (
        ! isset( $_POST['mits_faq_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_faq_nonce'] ) ), 'mits_faq_save' )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        || ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }
    update_post_meta(
        $post_id,
        '_faq_answer',
        wp_kses_post( wp_unslash( $_POST['mits_faq_answer'] ?? '' ) )
    );
} );

add_action( 'save_post_page', function ( int $post_id ): void {
    if (
        ! isset( $_POST['mits_vstup_page_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_vstup_page_nonce'] ) ), 'mits_vstup_page_save' )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        || ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }
    if ( isset( $_POST['vstup_video_url'] ) ) {
        $raw = trim( wp_unslash( $_POST['vstup_video_url'] ) );
        update_post_meta( $post_id, '_vstup_video_url', $raw ? mits_vstup_youtube_embed_url( $raw ) : '' );
    }
} );

/* ── Sub-page meta box (hero pin) ─────────────────────────────── */
function mits_vstup_sub_box_render( WP_Post $post ): void {
    if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'template-mits-vstup-sub.php'
         && ! in_array( $post->post_name, array( 'bakalavr', 'spetsialist', 'mahistr' ), true ) ) {
        echo '<p style="color:#999">Показується тільки для підсторінок Вступу (шаблон «MITS — Вступ підрозділ»).</p>';
        return;
    }
    wp_nonce_field( 'mits_vstup_sub_save', 'mits_vstup_sub_nonce' );
    $hero_id = get_post_meta( $post->ID, '_vstup_sub_hero_id', true );
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:220px"><label for="vstup-sub-hero-id">ID слайду Hero Slider</label></th>
            <td>
                <input type="number" id="vstup-sub-hero-id" name="vstup_sub_hero_id" min="0"
                       value="<?php echo esc_attr( $hero_id ); ?>" style="width:120px">
                <p class="description">ID запису типу «Hero Слайди» для цієї сторінки. 0 = показати всі слайди.</p>
            </td>
        </tr>
    </table>
    <?php
}

add_action( 'save_post_page', function ( int $post_id ): void {
    if (
        ! isset( $_POST['mits_vstup_sub_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_vstup_sub_nonce'] ) ), 'mits_vstup_sub_save' )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        || ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }
    update_post_meta( $post_id, '_vstup_sub_hero_id', absint( $_POST['vstup_sub_hero_id'] ?? 0 ) );
} );

/**
 * Convert any YouTube URL to an embed URL.
 * Handles: youtube.com/watch?v=, youtu.be/, youtube.com/embed/, youtube.com/shorts/
 */
function mits_vstup_youtube_embed_url( string $url ): string {
    $id = '';
    $parsed = wp_parse_url( $url );
    $host   = $parsed['host'] ?? '';
    $path   = $parsed['path'] ?? '';
    $query  = $parsed['query'] ?? '';
    if ( str_contains( $host, 'youtu.be' ) ) {
        $id = ltrim( $path, '/' );
    } elseif ( str_contains( $host, 'youtube.com' ) ) {
        if ( str_starts_with( $path, '/embed/' ) ) {
            return esc_url_raw( $url ); // already embed
        }
        if ( str_starts_with( $path, '/shorts/' ) ) {
            $id = explode( '/', ltrim( $path, '/' ) )[1] ?? '';
        } else {
            parse_str( $query, $params );
            $id = $params['v'] ?? '';
        }
    }
    if ( $id ) {
        $id = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $id );
        return 'https://www.youtube.com/embed/' . $id;
    }
    return esc_url_raw( $url );
}

/* ═══════════════════════════════════════════════════════════════════
   6b. ONE-TIME MIGRATION — repair "uXXXX" without backslash in JSON
       (caused by wp_json_encode without JSON_UNESCAPED_UNICODE + stripslashes)
═══════════════════════════════════════════════════════════════════ */
add_action( 'admin_init', function (): void {
    if ( get_option( 'mits_edu_level_json_repaired' ) ) {
        return;
    }
    $posts = get_posts( array(
        'post_type'      => 'mits_edu_level',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) );
    $decode_uXXXX = static function ( string $s ): string {
        return preg_replace_callback(
            '/u([0-9a-fA-F]{4})/',
            static function ( array $m ): string {
                return mb_chr( (int) hexdec( $m[1] ), 'UTF-8' );
            },
            $s
        ) ?? $s;
    };
    foreach ( $posts as $post_id ) {
        foreach ( array( '_edu_level_tips', '_edu_level_subjects' ) as $key ) {
            $raw = get_post_meta( $post_id, $key, true );
            if ( ! $raw ) {
                continue;
            }
            $items = json_decode( $raw, true );
            if ( ! is_array( $items ) ) {
                continue;
            }
            $repaired = array_map( $decode_uXXXX, $items );
            if ( $repaired !== $items ) {
                update_post_meta( $post_id, $key, wp_json_encode( $repaired, JSON_UNESCAPED_UNICODE ) );
            }
        }
    }
    update_option( 'mits_edu_level_json_repaired', '1' );
} );

/* ═══════════════════════════════════════════════════════════════════
   6. SEED — 3 рівні освіти (один раз при першому відкритті адмінки)
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_init', function (): void {
    if ( get_option( 'mits_edu_level_seeded' ) ) {
        return;
    }
    // Check if any levels already exist
    $existing = get_posts( array(
        'post_type'      => 'mits_edu_level',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( $existing ) {
        update_option( 'mits_edu_level_seeded', '1' );
        return;
    }

    $uk_levels = array(
        array( 'title' => 'БАКАЛАВРАТ',   'order' => 0 ),
        array( 'title' => 'МАГІСТРАТУРА', 'order' => 1 ),
        array( 'title' => 'АСПІРАНТУРА',  'order' => 2 ),
    );
    $en_levels = array(
        array( 'title' => 'BACHELOR',     'order' => 0 ),
        array( 'title' => 'MASTER',       'order' => 1 ),
        array( 'title' => 'POSTGRADUATE', 'order' => 2 ),
    );

    $uk_ids = array();
    foreach ( $uk_levels as $level ) {
        $id = wp_insert_post( array(
            'post_type'   => 'mits_edu_level',
            'post_title'  => $level['title'],
            'post_status' => 'publish',
            'menu_order'  => $level['order'],
        ) );
        if ( $id && ! is_wp_error( $id ) ) {
            if ( function_exists( 'pll_set_post_language' ) ) {
                pll_set_post_language( $id, 'uk' );
            }
            $uk_ids[ $level['order'] ] = $id;
        }
    }

    foreach ( $en_levels as $level ) {
        $id = wp_insert_post( array(
            'post_type'   => 'mits_edu_level',
            'post_title'  => $level['title'],
            'post_status' => 'publish',
            'menu_order'  => $level['order'],
        ) );
        if ( $id && ! is_wp_error( $id ) ) {
            if ( function_exists( 'pll_set_post_language' ) ) {
                pll_set_post_language( $id, 'en' );
            }
            if ( function_exists( 'pll_save_post_translations' ) && isset( $uk_ids[ $level['order'] ] ) ) {
                pll_save_post_translations( array(
                    'uk' => $uk_ids[ $level['order'] ],
                    'en' => $id,
                ) );
            }
        }
    }

    update_option( 'mits_edu_level_seeded', '1' );
} );

/* ═══════════════════════════════════════════════════════════════════
   7. ADMIN COLUMNS — edu levels
═══════════════════════════════════════════════════════════════════ */

add_filter( 'manage_mits_edu_level_posts_columns', function ( array $cols ): array {
    $new = array();
    $new['cb']              = $cols['cb'] ?? '<input type="checkbox">';
    $new['title']           = $cols['title'] ?? 'Назва';
    $new['edu_level_url']   = 'URL';
    $new['menu_order']      = 'Порядок';
    if ( isset( $cols['languages'] ) ) {
        $new['languages'] = $cols['languages'];
    }
    return $new;
}, 20 );

add_action( 'manage_mits_edu_level_posts_custom_column', function ( string $col, int $post_id ): void {
    switch ( $col ) {
        case 'edu_level_url':
            $u = get_post_meta( $post_id, '_edu_level_url', true );
            echo $u
                ? '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener">' . esc_html( $u ) . '</a>'
                : '<span style="color:#999">—</span>';
            break;
        case 'menu_order':
            echo (int) get_post_field( 'menu_order', $post_id );
            break;
    }
}, 10, 2 );

add_filter( 'manage_edit-mits_edu_level_sortable_columns', function ( array $cols ): array {
    $cols['menu_order'] = 'menu_order';
    return $cols;
} );

/* ═══════════════════════════════════════════════════════════════════
   8. ADMIN COLUMNS — FAQ
═══════════════════════════════════════════════════════════════════ */

add_filter( 'manage_mits_faq_posts_columns', function ( array $cols ): array {
    $new = array();
    $new['cb']         = $cols['cb'] ?? '<input type="checkbox">';
    $new['title']      = $cols['title'] ?? 'Питання';
    $new['faq_answer'] = 'Відповідь';
    $new['menu_order'] = 'Порядок';
    if ( isset( $cols['languages'] ) ) {
        $new['languages'] = $cols['languages'];
    }
    return $new;
}, 20 );

add_action( 'manage_mits_faq_posts_custom_column', function ( string $col, int $post_id ): void {
    switch ( $col ) {
        case 'faq_answer':
            $a = get_post_meta( $post_id, '_faq_answer', true );
            echo $a ? esc_html( wp_trim_words( wp_strip_all_tags( $a ), 12, '…' ) ) : '—';
            break;
        case 'menu_order':
            echo (int) get_post_field( 'menu_order', $post_id );
            break;
    }
}, 10, 2 );

add_filter( 'manage_edit-mits_faq_sortable_columns', function ( array $cols ): array {
    $cols['menu_order'] = 'menu_order';
    return $cols;
} );

add_action( 'pre_get_posts', function ( WP_Query $q ): void {
    if ( ! is_admin() || ! $q->is_main_query() ) {
        return;
    }
    if ( in_array( $q->get( 'post_type' ), array( 'mits_edu_level', 'mits_faq' ), true )
         && $q->get( 'orderby' ) === 'menu_order'
    ) {
        $q->set( 'order', 'ASC' );
    }
} );

/* ═══════════════════════════════════════════════════════════════════
   Force template-mits-vstup.php by page slug (no DB dependency).
   Same approach as Кафедра identifies its pages by post_name.
═══════════════════════════════════════════════════════════════════ */
add_filter( 'template_include', function ( string $template ): string {
    if ( ! is_page() ) {
        return $template;
    }
    $post = get_queried_object();
    if ( ! ( $post instanceof WP_Post ) ) {
        return $template;
    }
    // Slugs for the Вступ/Admission page (UK + EN + legacy hex-dash encoding)
    $vstup_slugs = array( 'вступ', 'admission', 'd0-b2-d1-81-d1-82-d1-83-d0-bf' );
    if ( in_array( $post->post_name, $vstup_slugs, true ) ) {
        $override = locate_template( 'template-mits-vstup.php' );
        return $override ?: $template;
    }
    // Sub-pages: Бакалаврат / Магістратура / Аспірантура
    $sub_slugs = array( 'bakalavr', 'spetsialist', 'mahistr' );
    if ( in_array( $post->post_name, $sub_slugs, true ) ) {
        $override = locate_template( 'template-mits-vstup-sub.php' );
        return $override ?: $template;
    }
    return $template;
} );

/* ═══════════════════════════════════════════════════════════════════
   JS for dynamic tips repeater (mits_edu_level edit screen only)
═══════════════════════════════════════════════════════════════════ */
add_action( 'admin_footer', function (): void {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'mits_edu_level' || $screen->base !== 'post' ) {
        return;
    }
    ?>
    <script>
    (function () {
        function makeRow( value ) {
            var div = document.createElement( 'div' );
            div.className = 'mits-tip-row';
            div.style.cssText = 'display:flex;gap:8px;margin-bottom:6px;align-items:center';
            var input = document.createElement( 'input' );
            input.type        = 'text';
            input.name        = 'edu_level_tips[]';
            input.className   = 'large-text';
            input.placeholder = 'Текст поради';
            input.value       = value || '';
            var btn = document.createElement( 'button' );
            btn.type      = 'button';
            btn.className = 'button mits-remove-tip';
            btn.setAttribute( 'aria-label', 'Видалити пораду' );
            btn.style.flexShrink = '0';
            btn.innerHTML = '&#x2715;';
            btn.addEventListener( 'click', function () { div.remove(); } );
            div.appendChild( input );
            div.appendChild( btn );
            return div;
        }

        function makeStepRow( value ) {
            var div = document.createElement( 'div' );
            div.className = 'mits-step-row';
            div.style.cssText = 'display:flex;gap:8px;margin-bottom:6px;align-items:center';
            var input = document.createElement( 'input' );
            input.type        = 'text';
            input.name        = 'edu_level_steps[]';
            input.className   = 'large-text';
            input.placeholder = 'Назва кроку';
            input.value       = value || '';
            var btn = document.createElement( 'button' );
            btn.type      = 'button';
            btn.className = 'button mits-remove-step';
            btn.setAttribute( 'aria-label', 'Видалити крок' );
            btn.style.flexShrink = '0';
            btn.innerHTML = '&#x2715;';
            btn.addEventListener( 'click', function () { div.remove(); } );
            div.appendChild( input );
            div.appendChild( btn );
            return div;
        }

        function makeSubjectRow( value ) {
            var div = document.createElement( 'div' );
            div.className = 'mits-subject-row';
            div.style.cssText = 'display:flex;gap:8px;margin-bottom:6px;align-items:center';
            var input = document.createElement( 'input' );
            input.type        = 'text';
            input.name        = 'edu_level_subjects[]';
            input.className   = 'large-text';
            input.placeholder = 'Назва предмету';
            input.value       = value || '';
            var btn = document.createElement( 'button' );
            btn.type      = 'button';
            btn.className = 'button mits-remove-subject';
            btn.setAttribute( 'aria-label', 'Видалити предмет' );
            btn.style.flexShrink = '0';
            btn.innerHTML = '&#x2715;';
            btn.addEventListener( 'click', function () { div.remove(); } );
            div.appendChild( input );
            div.appendChild( btn );
            return div;
        }

        function applyFormat( textarea, tag ) {
            var start = textarea.selectionStart;
            var end   = textarea.selectionEnd;
            var sel   = textarea.value.substring( start, end );
            var replacement;
            if ( tag === 'a' ) {
                var url = prompt( 'URL посилання:', 'https://' );
                if ( ! url ) { return; }
                replacement = '<a href="' + url.replace( /"/g, '&quot;' ) + '">' + ( sel || 'текст' ) + '</a>';
            } else if ( tag === 'br' ) {
                replacement = '<br>';
            } else if ( tag === 'ul' ) {
                var lines = sel ? sel.split( /\r?\n/ ).filter( function ( l ) { return l.trim() !== ''; } ) : [ 'пункт' ];
                replacement = '<ul>\n' + lines.map( function ( l ) { return '<li>' + l.trim() + '</li>'; } ).join( '\n' ) + '\n</ul>';
            } else {
                replacement = '<' + tag + '>' + ( sel || 'текст' ) + '</' + tag + '>';
            }
            textarea.value = textarea.value.substring( 0, start ) + replacement + textarea.value.substring( end );
            var newPos = start + replacement.length;
            textarea.setSelectionRange( newPos, newPos );
            textarea.focus();
        }

        function wireAlgoToolbar( container ) {
            container.querySelectorAll( '.mits-fmt-btn' ).forEach( function ( btn ) {
                btn.addEventListener( 'click', function () {
                    var ta = btn.closest( '.mits-algo-editor' ).querySelector( 'textarea' );
                    applyFormat( ta, btn.dataset.tag );
                } );
            } );
            var colorInp = container.querySelector( '.mits-color-input' );
            if ( colorInp ) {
                var _selStart = 0, _selEnd = 0, _cTa = null;
                colorInp.addEventListener( 'mousedown', function () {
                    _cTa = colorInp.closest( '.mits-algo-editor' ).querySelector( 'textarea' );
                    if ( _cTa ) { _selStart = _cTa.selectionStart; _selEnd = _cTa.selectionEnd; }
                } );
                colorInp.addEventListener( 'change', function () {
                    if ( ! _cTa ) { return; }
                    var sel = _cTa.value.substring( _selStart, _selEnd );
                    var rep = '<span style="color:' + colorInp.value + '">' + ( sel || 'текст' ) + '</span>';
                    _cTa.value = _cTa.value.substring( 0, _selStart ) + rep + _cTa.value.substring( _selEnd );
                    _cTa.setSelectionRange( _selStart + rep.length, _selStart + rep.length );
                    _cTa.focus();
                } );
            }
        }

        function makeAlgorithmRow( value ) {
            var div = document.createElement( 'div' );
            div.className = 'mits-algo-row';
            div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:flex-start';

            var editorDiv = document.createElement( 'div' );
            editorDiv.className = 'mits-algo-editor';
            editorDiv.style.cssText = 'flex:1';

            var toolbar = document.createElement( 'div' );
            toolbar.className = 'mits-algo-toolbar';
            toolbar.style.cssText = 'display:flex;gap:3px;margin-bottom:3px';

            var fmtBtns = [
                { tag: 'strong', label: 'B',            title: 'Жирний',        style: 'min-width:28px;font-weight:bold' },
                { tag: 'em',     label: 'I',            title: 'Курсив',        style: 'min-width:28px;font-style:italic' },
                { tag: 'u',      label: 'U',            title: 'Підкреслений',  style: 'min-width:28px;text-decoration:underline' },
                { tag: 'a',      label: '\uD83D\uDD17',  title: 'Посилання',     style: 'min-width:28px' },
                { tag: 'br',     label: '\u21B5',       title: 'Перенос рядка', style: 'min-width:34px' },
                { tag: 'ul',     label: '\u2022\u2013', title: 'Список',        style: 'min-width:28px' },
            ];
            fmtBtns.forEach( function ( cfg ) {
                var b = document.createElement( 'button' );
                b.type = 'button';
                b.className = 'button button-small mits-fmt-btn';
                b.dataset.tag = cfg.tag;
                b.title = cfg.title;
                b.style.cssText = cfg.style;
                b.innerHTML = cfg.label;
                toolbar.appendChild( b );
            } );
            var colorLabel = document.createElement( 'label' );
            colorLabel.className = 'button button-small mits-fmt-color-label';
            colorLabel.title = 'Колір тексту';
            colorLabel.style.cssText = 'min-width:36px;padding:0 4px;display:inline-flex;align-items:center;gap:3px;cursor:pointer';
            colorLabel.innerHTML = '<b>A</b>';
            var colorInpEl = document.createElement( 'input' );
            colorInpEl.type = 'color';
            colorInpEl.className = 'mits-color-input';
            colorInpEl.value = '#e040fb';
            colorInpEl.style.cssText = 'width:16px;height:16px;padding:0;border:0;cursor:pointer;vertical-align:middle';
            colorLabel.appendChild( colorInpEl );
            toolbar.appendChild( colorLabel );

            var ta = document.createElement( 'textarea' );
            ta.name        = 'edu_level_algorithm[]';
            ta.className   = 'large-text';
            ta.rows        = 3;
            ta.placeholder = 'Текст кроку';
            ta.value       = value || '';

            editorDiv.appendChild( toolbar );
            editorDiv.appendChild( ta );

            var btn = document.createElement( 'button' );
            btn.type      = 'button';
            btn.className = 'button mits-remove-algo';
            btn.setAttribute( 'aria-label', 'Видалити крок' );
            btn.style.cssText = 'flex-shrink:0;margin-top:26px';
            btn.innerHTML = '&#x2715;';
            btn.addEventListener( 'click', function () { div.remove(); } );

            div.appendChild( editorDiv );
            div.appendChild( btn );
            wireAlgoToolbar( div );
            return div;
        }

        document.addEventListener( 'DOMContentLoaded', function () {
            // Wire up remove buttons for server-rendered rows
            document.querySelectorAll( '.mits-remove-tip' ).forEach( function ( btn ) {
                btn.addEventListener( 'click', function () {
                    btn.closest( '.mits-tip-row' ).remove();
                } );
            } );
            document.querySelectorAll( '.mits-remove-subject' ).forEach( function ( btn ) {
                btn.addEventListener( 'click', function () {
                    btn.closest( '.mits-subject-row' ).remove();
                } );
            } );
            document.querySelectorAll( '.mits-remove-step' ).forEach( function ( btn ) {
                btn.addEventListener( 'click', function () {
                    btn.closest( '.mits-step-row' ).remove();
                } );
            } );

            var addStepBtn = document.getElementById( 'mits-add-step' );
            var stepList   = document.getElementById( 'mits-steps-list' );
            if ( addStepBtn && stepList ) {
                addStepBtn.addEventListener( 'click', function () {
                    stepList.appendChild( makeStepRow( '' ) );
                } );
            }

            var addTipBtn  = document.getElementById( 'mits-add-tip' );
            var tipList    = document.getElementById( 'mits-tips-list' );
            if ( addTipBtn && tipList ) {
                addTipBtn.addEventListener( 'click', function () {
                    tipList.appendChild( makeRow( '' ) );
                } );
            }

            var addSubjBtn = document.getElementById( 'mits-add-subject' );
            var subjList   = document.getElementById( 'mits-subjects-list' );
            if ( addSubjBtn && subjList ) {
                addSubjBtn.addEventListener( 'click', function () {
                    subjList.appendChild( makeSubjectRow( '' ) );
                } );
            }

            document.querySelectorAll( '.mits-remove-algo' ).forEach( function ( btn ) {
                btn.addEventListener( 'click', function () {
                    btn.closest( '.mits-algo-row' ).remove();
                } );
            } );
            var algoListInit = document.getElementById( 'mits-algo-list' );
            if ( algoListInit ) {
                wireAlgoToolbar( algoListInit );
            }
            var addAlgoBtn = document.getElementById( 'mits-add-algo' );
            var algoList   = document.getElementById( 'mits-algo-list' );
            if ( addAlgoBtn && algoList ) {
                addAlgoBtn.addEventListener( 'click', function () {
                    algoList.appendChild( makeAlgorithmRow( '' ) );
                } );
            }
        } );
    }());
    </script>
    <?php
} );

