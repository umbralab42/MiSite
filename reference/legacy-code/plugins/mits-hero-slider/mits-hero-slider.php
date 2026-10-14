<?php
/**
 * Plugin Name: MITS Hero Slider
 * Description: Тип запису «Hero Слайд», рендеринг секції та підключення стилів/скриптів. Повністю незалежний від теми.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: betheme
 *
 * @package MITS_Hero_Slider
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MITS_HERO_SLIDER_URL',  plugin_dir_url( __FILE__ ) );
define( 'MITS_HERO_SLIDER_PATH', plugin_dir_path( __FILE__ ) );

/* ═══════════════════════════════════════════════════════════════════
   POST TYPE
   ═══════════════════════════════════════════════════════════════════ */

class Mits_Post_Type_Hero_Slide {

    public function __construct() {
        add_action( 'init',                      [ $this, 'register' ] );
        add_action( 'add_meta_boxes',            [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_mits_hero_slide', [ $this, 'save_meta' ] );
        add_action( 'save_post_mits_hero_slide', [ $this, 'save_custom_css' ] );
        add_action( 'admin_init',                [ $this, 'maybe_seed_custom_css' ] );
    }

    public function register() {
        register_post_type( 'mits_hero_slide', [
            'labels' => [
                'name'          => __( 'Hero Слайди', 'betheme' ),
                'singular_name' => __( 'Hero Слайд',  'betheme' ),
                'add_new'       => __( 'Новий слайд',   'betheme' ),
                'add_new_item'  => __( 'Додати слайд',  'betheme' ),
                'edit_item'     => __( 'Редагувати слайд', 'betheme' ),
                'all_items'     => __( 'Всі слайди',    'betheme' ),
                'menu_name'     => __( 'Hero Слайди',   'betheme' ),
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'supports'           => [ 'title', 'thumbnail', 'page-attributes' ],
            'menu_icon'          => 'dashicons-images-alt2',
            'menu_position'      => 20,
            'has_archive'        => false,
            'publicly_queryable' => false,
        ] );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'mits_hero_slide_meta',
            __( 'Вміст слайду', 'betheme' ),
            [ $this, 'render_meta_box' ],
            'mits_hero_slide',
            'normal',
            'high'
        );
        add_meta_box(
            'mits_hero_custom_css',
            __( 'Custom CSS (глобально для всіх слайдів)', 'betheme' ),
            [ $this, 'render_custom_css_box' ],
            'mits_hero_slide',
            'normal',
            'low'
        );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'mits_hero_slide_save', 'mits_hero_slide_nonce' );

        $line1      = get_post_meta( $post->ID, '_mits_slide_line1',      true );
        $line2      = get_post_meta( $post->ID, '_mits_slide_line2',      true );
        $line1_size = get_post_meta( $post->ID, '_mits_slide_line1_size', true );
        $line2_size = get_post_meta( $post->ID, '_mits_slide_line2_size', true );
        $subtitle   = get_post_meta( $post->ID, '_mits_slide_subtitle',   true );
        $social     = get_post_meta( $post->ID, '_mits_slide_social',     true );
        if ( '' === $social ) {
            $social = '[mits_social_telegram][mits_social_instagram][mits_social_youtube][mits_social_tiktok]';
        }
        $gradient = get_post_meta( $post->ID, '_mits_slide_gradient', true ) ?: 'gradient-orange-cyan';
        ?>
        <table class="form-table" style="max-width:620px">
            <tr>
                <th style="width:220px"><label><?php esc_html_e( 'Рядок 1', 'betheme' ); ?></label></th>
                <td>
                    <input type="text" name="mits_slide_line1" value="<?php echo esc_attr( $line1 ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Думай креативно', 'betheme' ); ?>">
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Розмір шрифту рядка 1 (px)', 'betheme' ); ?></label></th>
                <td>
                    <input type="number" name="mits_slide_line1_size" value="<?php echo esc_attr( $line1_size ); ?>" min="12" max="300" class="small-text" placeholder="72">
                    <p class="description"><?php esc_html_e( 'Залиште порожнім — береться з CSS (72px за замовчуванням).', 'betheme' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Рядок 2', 'betheme' ); ?></label></th>
                <td>
                    <input type="text" name="mits_slide_line2" value="<?php echo esc_attr( $line2 ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Вчись сучасно', 'betheme' ); ?>">
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Розмір шрифту рядка 2 (px)', 'betheme' ); ?></label></th>
                <td>
                    <input type="number" name="mits_slide_line2_size" value="<?php echo esc_attr( $line2_size ); ?>" min="12" max="300" class="small-text" placeholder="72">
                    <p class="description"><?php esc_html_e( 'Залиште порожнім — береться з CSS (72px за замовчуванням).', 'betheme' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Підзаголовок (необов\'язково)', 'betheme' ); ?></label></th>
                <td>
                    <input type="text" name="mits_slide_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Кафедра МУЛЬТИМЕДІЙНИХ ТА ІНТЕРНЕТ ТЕХНОЛОГІЙ І СИСТЕМ НТУ ХПІ', 'betheme' ); ?>">
                    <p class="description"><?php esc_html_e( 'Залиште порожнім — використається текст за замовчуванням.', 'betheme' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Соціальні мережі (shortcode)', 'betheme' ); ?></label></th>
                <td>
                    <input type="text" name="mits_slide_social" value="<?php echo esc_attr( $social ); ?>" class="large-text" placeholder="[mits_social_telegram][mits_social_instagram][mits_social_youtube][mits_social_tiktok]">
                    <p class="description"><?php esc_html_e( 'Shortcode або HTML. Виводиться між заголовком і кнопкою CTA.', 'betheme' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e( 'Клас градієнту', 'betheme' ); ?></label></th>
                <td>
                    <input type="text" name="mits_slide_gradient" value="<?php echo esc_attr( $gradient ); ?>" class="regular-text" placeholder="gradient-orange-cyan">
                    <p class="description"><?php esc_html_e( 'CSS-клас слайду. Додай новий клас у Custom CSS нижче — і вкажи його тут.', 'betheme' ); ?></p>
                </td>
            </tr>
        </table>
        <p class="description" style="margin-top:14px">
            <?php esc_html_e( 'Фото задається через «Зображення запису» праворуч. Порядок слайдів — через поле «Порядок» в «Атрибутах запису».', 'betheme' ); ?>
        </p>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['mits_hero_slide_nonce'] ) ||
             ! wp_verify_nonce( sanitize_key( $_POST['mits_hero_slide_nonce'] ), 'mits_hero_slide_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) )     return;

        foreach ( [ 'line1', 'line2', 'subtitle' ] as $key ) {
            $field = 'mits_slide_' . $key;
            if ( array_key_exists( $field, $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_' . $field,
                    sanitize_text_field( wp_unslash( $_POST[ $field ] ) )
                );
            }
        }

        if ( isset( $_POST['mits_slide_social'] ) ) {
            update_post_meta(
                $post_id,
                '_mits_slide_social',
                wp_kses_post( wp_unslash( $_POST['mits_slide_social'] ) )
            );
        }

        foreach ( [ 'line1_size', 'line2_size' ] as $key ) {
            $field = 'mits_slide_' . $key;
            if ( isset( $_POST[ $field ] ) ) {
                $val = absint( $_POST[ $field ] );
                if ( $val > 0 ) {
                    update_post_meta( $post_id, '_' . $field, $val );
                } else {
                    delete_post_meta( $post_id, '_' . $field );
                }
            }
        }

        if ( isset( $_POST['mits_slide_gradient'] ) ) {
            update_post_meta( $post_id, '_mits_slide_gradient', sanitize_html_class( wp_unslash( $_POST['mits_slide_gradient'] ) ) );
        }
    }

    public function render_custom_css_box( $post ): void {
        wp_nonce_field( 'mits_hero_css_save', 'mits_hero_css_nonce' );
        $css = get_option( 'mits_hero_custom_css', '' );
        ?>
        <p class="description" style="margin-bottom:10px">
            <?php esc_html_e( 'Додатковий CSS для хіро-секції на головній сторінці. Базові стилі — у файлі css/mits-hero.css. Зміни тут зберігаються глобально (однакові для всіх слайдів).', 'betheme' ); ?>
        </p>
        <textarea
            name="mits_hero_custom_css"
            id="mits_hero_custom_css"
            rows="16"
            style="width:100%;font-family:monospace;font-size:13px;background:#1e1e1e;color:#d4d4d4;border:1px solid #3c3c3c;border-radius:4px;padding:10px;resize:vertical"
            spellcheck="false"
        ><?php echo esc_textarea( $css ); ?></textarea>
        <?php
    }

    public function maybe_seed_custom_css(): void {
        if ( get_option( 'mits_hero_css_version' ) === '6' ) {
            return;
        }
        $css = <<<'CSS'
/* Hero gradient classes — edit values here to change slide colours.
   Choose a class in the "Клас градієнту" field of each slide. */
.mits-hero__line {
  display: block;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.gradient-orange-cyan  .mits-hero__line { background-image: linear-gradient(90deg, #e8612a, #d85090 40%, #8b72be 65%, #569de6 82%, #5bafa8); }
.gradient-blue-purple  .mits-hero__line { background-image: linear-gradient(90deg, #06518a, #2a6eaa 35%, #5a3f9e 65%, #936ef6); }
.gradient-green-teal   .mits-hero__line { background-image: linear-gradient(90deg, #11998e, #38ef7d 45%, #4ecdc4 70%, #44a08d); }
.gradient-pink-magenta .mits-hero__line { background-image: linear-gradient(90deg, #f953c6, #b91d73 40%, #8e44ad 65%, #3498db); }
CSS;
        update_option( 'mits_hero_custom_css', $css );
        update_option( 'mits_hero_css_version', '6' );
    }

    public function save_custom_css( $post_id ): void {
        if ( ! isset( $_POST['mits_hero_css_nonce'] ) ||
             ! wp_verify_nonce( sanitize_key( $_POST['mits_hero_css_nonce'] ), 'mits_hero_css_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) )     return;

        $css = isset( $_POST['mits_hero_custom_css'] )
            ? wp_strip_all_tags( wp_unslash( $_POST['mits_hero_custom_css'] ) )
            : '';

        update_option( 'mits_hero_custom_css', $css );
    }
}

new Mits_Post_Type_Hero_Slide();

/* ═══════════════════════════════════════════════════════════════════
   POLYLANG
   ═══════════════════════════════════════════════════════════════════ */

add_filter( 'pll_get_post_types', function ( array $post_types, bool $is_settings ): array {
    $post_types['mits_hero_slide'] = 'mits_hero_slide';
    return $post_types;
}, 10, 2 );

add_filter( 'pll_copy_post_metas', function ( array $metas, bool $sync ): array {
    $metas[] = '_mits_slide_gradient';
    $metas[] = '_mits_slide_subtitle';
    $metas[] = '_thumbnail_id';
    return $metas;
}, 10, 2 );

/* ═══════════════════════════════════════════════════════════════════
   ADMIN COLUMNS
   ═══════════════════════════════════════════════════════════════════ */

add_filter( 'manage_mits_hero_slide_posts_columns', function ( array $cols ): array {
    $new = [];
    foreach ( $cols as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['mits_hero_order'] = __( 'Порядок', 'betheme' );
        }
    }
    return $new;
} );

add_action( 'manage_mits_hero_slide_posts_custom_column', function ( string $col, int $post_id ): void {
    if ( $col !== 'mits_hero_order' ) return;
    echo (int) get_post_field( 'menu_order', $post_id );
}, 10, 2 );

add_filter( 'manage_edit-mits_hero_slide_sortable_columns', function ( array $cols ): array {
    $cols['mits_hero_order'] = 'menu_order';
    return $cols;
} );

/* ═══════════════════════════════════════════════════════════════════
   ASSETS  (font + CSS + JS — only on front page)
   ═══════════════════════════════════════════════════════════════════ */

add_action( 'wp_enqueue_scripts', function () {
    $is_vstup_page   = is_page( array( 'вступ', 'admission', 'd0-b2-d1-81-d1-82-d1-83-d0-bf' ) );
    $is_vstup_sub    = is_page_template( 'template-mits-vstup-sub.php' ) || is_page( array( 'bakalavr', 'spetsialist', 'mahistr' ) );
    $is_osvita       = is_page_template( 'template-mits-osvita.php' );
    $is_osvita_sub   = is_page_template( 'template-mits-osvita-sub.php' );
    $is_intl         = is_page_template( 'template-mits-international.php' );
    $is_nauka        = is_page_template( 'template-mits-nauka.php' );
    if ( ! is_front_page() && ! is_page( array( 9, 136 ) ) && ! is_page_template( 'template-mits-vstup.php' ) && ! $is_vstup_page && ! $is_vstup_sub && ! $is_osvita && ! $is_osvita_sub && ! $is_intl && ! $is_nauka ) return;

    wp_enqueue_style(
        'geist-font',
        'https://fonts.googleapis.com/css2?family=Geist:wght@400;600;700;800&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'mits-hero',
        MITS_HERO_SLIDER_URL . 'css/mits-hero.css',
        [ 'geist-font' ],
        '1.2'
    );
    wp_enqueue_script(
        'mits-hero',
        MITS_HERO_SLIDER_URL . 'js/mits-hero.js',
        [],
        '1.0',
        true
    );
} );

/* Output editable gradient CSS in <head> on front page or kafedra page */
add_action( 'wp_head', function () {
    $is_osvita     = is_page_template( 'template-mits-osvita.php' );
    $is_osvita_sub = is_page_template( 'template-mits-osvita-sub.php' );
    $is_intl       = is_page_template( 'template-mits-international.php' );
    $is_nauka      = is_page_template( 'template-mits-nauka.php' );
    if ( ! is_front_page() && ! is_page( array( 9, 136 ) ) && ! is_page_template( 'template-mits-vstup.php' ) && ! is_page( array( 'вступ', 'admission', 'd0-b2-d1-81-d1-82-d1-83-d0-bf' ) ) && ! is_page_template( 'template-mits-vstup-sub.php' ) && ! is_page( array( 'bakalavr', 'spetsialist', 'mahistr' ) ) && ! $is_osvita && ! $is_osvita_sub && ! $is_intl && ! $is_nauka ) return;
    $css = get_option( 'mits_hero_custom_css', '' );
    if ( $css === '' ) return;
    echo '<style id="mits-hero-custom">' . strip_tags( $css ) . '</style>' . "\n";
} );

/* ═══════════════════════════════════════════════════════════════════
   RENDER FUNCTION  —  called from front-page.php
   ═══════════════════════════════════════════════════════════════════ */

/**
 * Output the full hero slider <section>.
 * Also available as shortcode [mits_hero_slider].
 *
 * @param  string $cta_url  URL the CTA button links to. Default '#contact-form'.
 * @param  string $cta_text CTA button label.
 */
function mits_hero_render( string $cta_url = '#contact-form', string $cta_text = '', int $pin_post_id = 0 ): void {
    if ( $cta_text === '' ) {
        $cta_text = function_exists( 'mits_t' ) ? mits_t( 'nav.cta' ) : __( 'Консультація', 'betheme' );
    }

    $query_args = [
        'post_type'      => 'mits_hero_slide',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ];
    if ( $pin_post_id > 0 ) {
        $query_args['post__in']      = [ $pin_post_id ];
        $query_args['posts_per_page'] = 1;
    }
    $query = new WP_Query( $query_args );

    if ( ! $query->have_posts() ) {
        return;
    }
    ?>
    <section class="mits-hero">
      <div class="mits-hero__track">
        <?php if ( $query->have_posts() ) :
          $slide_i = 0;
          while ( $query->have_posts() ) : $query->the_post();
            $gradient   = get_post_meta( get_the_ID(), '_mits_slide_gradient',   true ) ?: 'gradient-orange-cyan';
            $line1      = get_post_meta( get_the_ID(), '_mits_slide_line1',      true );
            $line2      = get_post_meta( get_the_ID(), '_mits_slide_line2',      true );
            $line1_size = get_post_meta( get_the_ID(), '_mits_slide_line1_size', true );
            $line2_size = get_post_meta( get_the_ID(), '_mits_slide_line2_size', true );
            $social     = get_post_meta( get_the_ID(), '_mits_slide_social',     true );
            $subtitle   = get_post_meta( get_the_ID(), '_mits_slide_subtitle',   true )
                          ?: __( 'Кафедра МУЛЬТИМЕДІЙНИХ ТА ІНТЕРНЕТ ТЕХНОЛОГІЙ І СИСТЕМ НТУ ХПІ', 'betheme' );
            $img_url    = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        ?>
        <div class="mits-hero__slide <?php echo esc_attr( $gradient ); ?><?php echo $slide_i === 0 ? ' is-active' : ''; ?>">
          <div class="mits-hero__content">
            <p class="mits-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
            <h1 class="mits-hero__title">
              <span class="mits-hero__line"<?php echo $line1_size ? ' style="font-size:' . absint( $line1_size ) . 'px"' : ''; ?>><?php echo esc_html( $line1 ); ?></span>
              <span class="mits-hero__line"<?php echo $line2_size ? ' style="font-size:' . absint( $line2_size ) . 'px"' : ''; ?>><?php echo esc_html( $line2 ); ?></span>
            </h1>
            <?php if ( $social ) : ?>
            <div class="mits-hero__social"><?php echo do_shortcode( $social ); ?></div>
            <?php endif; ?>
          </div>
          <?php if ( $img_url ) : ?>
          <div class="mits-hero__image">
            <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
          </div>
          <?php endif; ?>
        </div>
        <?php $slide_i++; endwhile; wp_reset_postdata(); endif; ?>
      </div>
      <a href="<?php echo esc_url( $cta_url ); ?>" class="mits-btn mits-btn--primary mits-hero__cta"><?php echo esc_html( $cta_text ); ?></a>
      <?php if ( $query->post_count > 1 ) : ?>
      <div class="mits-hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Слайди', 'betheme' ); ?>">
        <?php for ( $i = 0; $i < $query->post_count; $i++ ) : ?>
        <button class="mits-hero__dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Слайд %d', 'betheme' ), $i + 1 ) ); ?>"></button>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </section>
    <?php
}

/* Shortcode wrapper — [mits_hero_slider] or [mits_hero_slider cta_url="#form" cta_text="Contact us"] */
add_shortcode( 'mits_hero_slider', function ( $atts ): string {
    $atts = shortcode_atts( [
        'cta_url'  => '#contact-form',
        'cta_text' => '',
    ], $atts, 'mits_hero_slider' );

    ob_start();
    mits_hero_render( $atts['cta_url'], $atts['cta_text'] );
    return ob_get_clean();
} );
