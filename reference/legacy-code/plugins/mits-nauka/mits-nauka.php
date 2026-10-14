<?php
/**
 * Plugin Name: MITS Наука
 * Description: Налаштування сторінки «Наука»: фото, текст, посилання, статистика, курси, документи.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: mits-nauka
 *
 * @package MITS_Nauka
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ═══════════════════════════════════════════════════════════════════
   1. ADMIN MENU
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', function (): void {

	// Батьківське меню
	add_menu_page(
		'MITS Наука',
		'MITS Наука',
		'edit_pages',
		'mits-nauka',
		'__return_null',
		'dashicons-welcome-learn-more',
		9
	);

	// Видалити авто-дублікат у підменю
	remove_submenu_page( 'mits-nauka', 'mits-nauka' );

	// Підсторінка «Налаштування»
	add_submenu_page(
		'mits-nauka',
		'Налаштування',
		'Налаштування',
		'edit_pages',
		'mits-nauka-settings',
		'mits_nauka_settings_page_render'
	);


}, 999 );

// Підсвічування меню при редагуванні сторінки з шаблоном
add_filter( 'parent_file', function ( string $parent ): string {
	if ( ! is_admin() ) {
		return $parent;
	}
	$screen = get_current_screen();
	if ( ! $screen ) {
		return $parent;
	}
	if ( $screen->base === 'post' && $screen->post_type === 'page' ) {
		global $post;
		if ( $post && get_post_meta( $post->ID, '_wp_page_template', true ) === 'template-mits-nauka.php' ) {
			return 'mits-nauka';
		}
	}
	return $parent;
} );

/* ═══════════════════════════════════════════════════════════════════
   2. ENQUEUE MEDIA ON SETTINGS PAGE
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_enqueue_scripts', function ( string $hook ): void {} );

/* ═══════════════════════════════════════════════════════════════════
   3. SETTINGS PAGE RENDER
═══════════════════════════════════════════════════════════════════ */

function mits_nauka_settings_page_render(): void {

	if (
		isset( $_POST['mits_nauka_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_nauka_nonce'] ) ), 'mits_nauka_save' )
		&& current_user_can( 'edit_pages' )
	) {
		update_option( 'mits_nauka_hero_id', absint( $_POST['nauka_hero_id'] ?? 0 ) );
		echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
	}

	$hero_id = (int) get_option( 'mits_nauka_hero_id', 0 );
	?>
	<div class="wrap">
	<h1>Наука — Налаштування</h1>
	<form method="post">
		<?php wp_nonce_field( 'mits_nauka_save', 'mits_nauka_nonce' ); ?>

		<h2 style="margin-top:24px">Hero Slider</h2>
		<table class="form-table" style="margin-top:0">
			<tr>
				<th style="width:180px"><label for="nauka-hero-id">ID слайду</label></th>
				<td>
					<input type="number" id="nauka-hero-id" name="nauka_hero_id" min="0"
					       value="<?php echo esc_attr( $hero_id ); ?>" style="width:120px">
					<p class="description">ID запису типу «Hero Слайди». 0 = показати всі слайди.</p>
					<?php
					if ( $hero_id ) {
						$slide = get_post( $hero_id );
						if ( $slide && $slide->post_type === 'mits_hero_slide' ) {
							echo '<p class="description" style="color:#46b450">✓ ' . esc_html( $slide->post_title ) . '</p>';
						} elseif ( $slide ) {
							echo '<p class="description" style="color:#d63638">⚠ Запис із цим ID не є Hero Слайдом.</p>';
						} else {
							echo '<p class="description" style="color:#d63638">⚠ Запис не знайдено.</p>';
						}
					}
					?>
				</td>
			</tr>
		</table>

		<?php submit_button( 'Зберегти' ); ?>
	</form>
	</div>
	<?php
}
