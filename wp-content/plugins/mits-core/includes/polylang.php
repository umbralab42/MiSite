<?php
/**
 * Інтеграція з Polylang (безкоштовна версія): перекладні типи записів і
 * синхронізація лише тих полів, які не залежать від мови.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Усі типи записів сайту перекладаються.
 */
add_filter( 'pll_get_post_types', function ( $post_types, $is_settings ) {
	foreach ( array_keys( mits_post_type_definitions() ) as $type ) {
		$post_types[ $type ] = $type;
	}
	return $post_types;
}, 10, 2 );

/**
 * Поля, що копіюються в переклад і синхронізуються між мовами (не тексти).
 */
function mits_language_neutral_meta() {
	return array(
		'_mits_slide_gradient',
		'_mits_slide_line1_size',
		'_mits_slide_line2_size',
		'_mits_slide_social',
		'_act_icon_url',
		'_act_icon_bg',
		'_teacher_publications',
		'_teacher_photo_id',
		'_vstup_video_url',
	);
}

add_filter( 'pll_copy_post_metas', function ( $metas, $sync ) {
	// Тексти кожна мова має свої, тож копіюються лише «технічні» поля.
	$neutral = mits_language_neutral_meta();
	$metas   = array_values( array_filter( (array) $metas, function ( $key ) use ( $neutral ) {
		return in_array( $key, $neutral, true ) || '_thumbnail_id' === $key || '_wp_page_template' === $key;
	} ) );
	return array_values( array_unique( array_merge( $metas, $neutral ) ) );
}, 20, 2 );

/**
 * Ідентифікатори сторінок і слайдів у полях указують на переклад тією ж мовою.
 */
add_filter( 'pll_translate_post_meta', function ( $value, $key, $lang ) {
	$post_refs = array( '_edu_level_page_id', '_vstup_sub_hero_id', '_osvita_hero_id', '_osvita_sub_hero_id', '_kafedra_hero_id', '_vstup_hero_id' );
	if ( in_array( $key, $post_refs, true ) && function_exists( 'pll_get_post' ) && $value ) {
		$translated = pll_get_post( (int) $value, $lang );
		return $translated ? $translated : $value;
	}
	return $value;
}, 10, 3 );

/**
 * Рядки інтерфейсу, які редактор може змінити в «Мови → Переклади рядків».
 */
add_action( 'init', function () {
	if ( ! function_exists( 'pll_register_string' ) || ! function_exists( 'mits_ui_strings' ) ) {
		return;
	}
	foreach ( mits_ui_strings() as $key => $pair ) {
		pll_register_string( $key, $pair['uk'], 'МІТС', false );
	}
} );
