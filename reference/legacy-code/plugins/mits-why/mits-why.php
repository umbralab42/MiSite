<?php
/**
 * Plugin Name: MITS Why Us
 * Description: Редагування секції «Чому ми?» на головній сторінці — текст, статистика, мультимовність.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: mits-why
 *
 * @package MITS_Why
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Language-aware getter ───────────────────────────────────────── */

function mits_why_get( string $key, string $default = '' ): string {
    $lang     = function_exists( 'pll_current_language' ) ? pll_current_language() : 'uk';
    $opts     = get_option( 'mits_why', array() );
    $defaults = mits_why_defaults();

    if ( $lang !== 'uk' ) {
        $val = $opts[ $key . '_' . $lang ] ?? '';
        if ( $val !== '' ) {
            return $val;
        }
    }

    return $opts[ $key ] ?? $defaults[ $key ] ?? $default;
}

/* ── Defaults ────────────────────────────────────────────────────── */

function mits_why_defaults(): array {
    return array(
        'title'        => 'Чому ми?',
        'title_en'     => 'Why us?',
        'text'         => 'Сьогодні мультимедійні технології впроваджуються практично в усі сфери життєдіяльності суспільства – виробництво товарів для систем масової інформації та комунікації; візуалізація об\'єктів глобального інформаційного простору; розвиток електронної демократії, електронних послуг, інформаційних ринків; наростання інформаційного обміну за допомогою електронних соціальних мереж.',
        'text_en'      => '',
        'stat1_num'    => '800+',
        'stat1_label'  => 'Наукових публікацій',
        'stat1_label_en' => 'Scientific publications',
        'stat2_num'    => '15+',
        'stat2_label'  => 'Років досвіду',
        'stat2_label_en' => 'Years of experience',
        'stat3_num'    => '150+',
        'stat3_label'  => 'Студентів щорічно',
        'stat3_label_en' => 'Students per year',
        'stat4_num'    => '600+',
        'stat4_label'  => 'Випускників',
        'stat4_label_en' => 'Graduates',
    );
}

/* ── Admin menu ──────────────────────────────────────────────────── */

add_action( 'admin_menu', function (): void {
    add_submenu_page(
        'mits-pages',
        'МІТС — Чому ми?',
        'Чому ми?',
        'manage_options',
        'mits-why',
        'mits_why_render_page'
    );
}, 999 );

/* ── Settings registration ───────────────────────────────────────── */

add_action( 'admin_init', function (): void {
    register_setting( 'mits_why_group', 'mits_why', 'mits_why_sanitize' );
} );

function mits_why_sanitize( $input ): array {
    $clean    = array();
    $defaults = mits_why_defaults();

    foreach ( $defaults as $key => $_ ) {
        if ( $key === 'text' || $key === 'text_en' ) {
            $clean[ $key ] = isset( $input[ $key ] )
                ? wp_kses_post( wp_unslash( $input[ $key ] ) )
                : '';
        } else {
            $clean[ $key ] = isset( $input[ $key ] )
                ? sanitize_text_field( wp_unslash( $input[ $key ] ) )
                : '';
        }
    }

    return $clean;
}

/* ── Admin page render ───────────────────────────────────────────── */

function mits_why_render_page(): void {
    $d    = mits_why_defaults();
    $opts = wp_parse_args( get_option( 'mits_why', array() ), $d );

    $field = function ( string $key, string $label, string $type = 'text', bool $wide = false ) use ( $opts ): void {
        $id  = 'mits_why_' . $key;
        $val = esc_attr( $opts[ $key ] ?? '' );
        $cls = $wide ? 'large-text' : 'regular-text';
        echo '<tr>';
        echo '<th><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th>';
        if ( $type === 'textarea' ) {
            echo '<td><textarea id="' . esc_attr( $id ) . '" name="mits_why[' . esc_attr( $key ) . ']" rows="5" class="large-text">' . esc_textarea( $opts[ $key ] ?? '' ) . '</textarea></td>';
        } else {
            echo '<td><input type="text" id="' . esc_attr( $id ) . '" name="mits_why[' . esc_attr( $key ) . ']" value="' . $val . '" class="' . esc_attr( $cls ) . '"></td>';
        }
        echo '</tr>';
    };
    ?>
    <div class="wrap">
        <h1>МІТС — Чому ми?</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'mits_why_group' ); ?>

            <h2>Заголовок та текст (UK)</h2>
            <table class="form-table">
                <?php
                $field( 'title', 'Заголовок секції', 'text', true );
                $field( 'text',  'Текст опису',      'textarea' );
                ?>
            </table>

            <h2>English translations</h2>
            <p class="description">Порожнє поле — буде використано українське значення.</p>
            <table class="form-table">
                <?php
                $field( 'title_en', 'Title (EN)', 'text', true );
                $field( 'text_en',  'Text (EN)',  'textarea' );
                ?>
            </table>

            <h2>Статистика</h2>
            <p class="description">Цифри та підписи під ними (UK).</p>
            <table class="form-table">
                <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                    <tr>
                        <th><label for="mits_why_stat<?php echo $i; ?>_num">Цифра <?php echo $i; ?></label></th>
                        <td>
                            <input type="text" id="mits_why_stat<?php echo $i; ?>_num"
                                   name="mits_why[stat<?php echo $i; ?>_num]"
                                   value="<?php echo esc_attr( $opts[ 'stat' . $i . '_num' ] ?? '' ); ?>"
                                   class="small-text" style="width:100px;">
                            <input type="text"
                                   name="mits_why[stat<?php echo $i; ?>_label]"
                                   value="<?php echo esc_attr( $opts[ 'stat' . $i . '_label' ] ?? '' ); ?>"
                                   class="regular-text"
                                   placeholder="Підпис (UK)">
                            <input type="text"
                                   name="mits_why[stat<?php echo $i; ?>_label_en]"
                                   value="<?php echo esc_attr( $opts[ 'stat' . $i . '_label_en' ] ?? '' ); ?>"
                                   class="regular-text"
                                   placeholder="Label (EN)">
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>

            <?php submit_button( 'Зберегти зміни' ); ?>
        </form>
    </div>
    <?php
}
