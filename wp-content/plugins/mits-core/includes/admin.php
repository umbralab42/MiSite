<?php
/**
 * Адмінка: скрипти полів, підказки, прибирання зайвого.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	$ours   = $screen && ( 'toplevel_page_mits-blocks' === $screen->id || in_array( $screen->post_type, array_merge( array( 'page' ), array_keys( mits_post_type_definitions() ) ), true ) );
	if ( ! $ours ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'mits-admin', MITS_CORE_URL . 'assets/admin.css', array(), MITS_CORE_VERSION );
	wp_enqueue_script( 'mits-admin', MITS_CORE_URL . 'assets/admin.js', array(), MITS_CORE_VERSION, true );
} );

/**
 * Підказка на сторінках без власних полів: що редагується де.
 */
add_action( 'edit_form_after_title', function ( $post ) {
	if ( 'page' !== $post->post_type ) {
		return;
	}
	if ( (int) get_option( 'page_on_front' ) === $post->ID || ( function_exists( 'pll_get_post' ) && pll_get_post( (int) get_option( 'page_on_front' ), 'uk' ) === pll_get_post( $post->ID, 'uk' ) ) ) {
		echo '<div class="notice notice-info inline"><p>Головна сторінка складається з блоків: «Слайди головної», «МІТС: блоки → Головна», «Переваги», «Діяльність», «Новини», «МІТС: блоки → Контакти».</p></div>';
	}
} );

/**
 * Коментарі на сайті є лише в новинах; пункт меню лишається для модерації.
 * Прибираємо з меню «Записи» (тип post не використовується).
 */
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit.php' );
} );
