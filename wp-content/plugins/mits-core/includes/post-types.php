<?php
/**
 * Типи записів сайту. Ключі типів і полів такі самі, як на старому сайті
 * (content/content.json), щоб імпорт лягав без перетворень.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Опис типів записів: ключ => [назва в однині, у множині, налаштування].
 */
function mits_post_type_definitions() {
	return array(
		'news'            => array(
			'labels' => array( 'Новина', 'Новини', 'Усі новини', 'Додати новину' ),
			'args'   => array(
				'public'       => true,
				'has_archive'  => 'news',
				'rewrite'      => array( 'slug' => 'news', 'with_front' => false ),
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions' ),
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-megaphone',
				'menu_position' => 5,
			),
		),
		'teacher'         => array(
			'labels' => array( 'Викладач', 'Викладачі', 'Усі викладачі', 'Додати викладача' ),
			'args'   => array(
				'public'        => true,
				'has_archive'   => 'teachers',
				'rewrite'       => array( 'slug' => 'teachers', 'with_front' => false ),
				'supports'      => array( 'title', 'thumbnail', 'page-attributes', 'revisions' ),
				'show_in_rest'  => false,
				'menu_icon'     => 'dashicons-groups',
				'menu_position' => 6,
			),
		),
		'mits_hero_slide' => array(
			'labels' => array( 'Слайд', 'Слайди головної', 'Усі слайди', 'Додати слайд' ),
			'args'   => array(
				'supports'  => array( 'title', 'thumbnail', 'page-attributes' ),
				'menu_icon' => 'dashicons-images-alt2',
			),
		),
		'mits_activity'   => array(
			'labels' => array( 'Картка «Діяльність»', 'Діяльність', 'Усі картки', 'Додати картку' ),
			'args'   => array(
				'supports'  => array( 'title', 'page-attributes' ),
				'menu_icon' => 'dashicons-screenoptions',
			),
		),
		'mits_advantage'  => array(
			'labels' => array( 'Перевага', 'Переваги', 'Усі переваги', 'Додати перевагу' ),
			'args'   => array(
				'supports'  => array( 'title', 'page-attributes' ),
				'menu_icon' => 'dashicons-star-filled',
			),
		),
		'mits_edu_level'  => array(
			'labels' => array( 'Освітній рівень', 'Освітні рівні (Вступ)', 'Усі рівні', 'Додати рівень' ),
			'args'   => array(
				'supports'  => array( 'title', 'page-attributes' ),
				'menu_icon' => 'dashicons-welcome-learn-more',
			),
		),
		'mits_faq'        => array(
			'labels' => array( 'Питання FAQ', 'FAQ (Вступ)', 'Усі питання', 'Додати питання' ),
			'args'   => array(
				'supports'  => array( 'title', 'page-attributes' ),
				'menu_icon' => 'dashicons-editor-help',
			),
		),
	);
}

/**
 * Реєстрація типів записів.
 */
function mits_register_post_types() {
	foreach ( mits_post_type_definitions() as $type => $def ) {
		list( $singular, $plural, $all, $add ) = $def['labels'];
		$args = wp_parse_args( $def['args'], array(
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => false,
			'menu_position'       => 25,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		) );
		if ( ! empty( $args['public'] ) ) {
			$args['show_in_nav_menus']   = true;
			$args['exclude_from_search'] = false;
			$args['publicly_queryable']  = true;
		}
		$args['labels'] = array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			'all_items'          => $all,
			'add_new'            => $add,
			'add_new_item'       => $add,
			'edit_item'          => 'Редагувати: ' . mb_strtolower( $singular ),
			'new_item'           => $add,
			'view_item'          => 'Переглянути',
			'search_items'       => 'Шукати',
			'not_found'          => 'Нічого не знайдено',
			'not_found_in_trash' => 'У кошику порожньо',
			'featured_image'     => 'Зображення',
			'set_featured_image' => 'Вибрати зображення',
			'archives'           => $plural,
		);
		register_post_type( $type, $args );
	}
}
add_action( 'init', 'mits_register_post_types' );

/**
 * Порядок за полем «Порядок» у списках адмінки для впорядкованих типів.
 */
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$type = $query->get( 'post_type' );
	if ( in_array( $type, array( 'teacher', 'mits_hero_slide', 'mits_activity', 'mits_advantage', 'mits_edu_level', 'mits_faq' ), true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
	}
} );

/**
 * Колонка «Порядок» у списках адмінки.
 */
foreach ( array( 'teacher', 'mits_hero_slide', 'mits_activity', 'mits_advantage', 'mits_edu_level', 'mits_faq' ) as $mits_ordered_type ) {
	add_filter( "manage_{$mits_ordered_type}_posts_columns", function ( $cols ) {
		$cols['mits_order'] = 'Порядок';
		return $cols;
	} );
	add_action( "manage_{$mits_ordered_type}_posts_custom_column", function ( $col, $post_id ) {
		if ( 'mits_order' === $col ) {
			echo (int) get_post_field( 'menu_order', $post_id );
		}
	}, 10, 2 );
	add_filter( "manage_edit-{$mits_ordered_type}_sortable_columns", function ( $cols ) {
		$cols['mits_order'] = 'menu_order';
		return $cols;
	} );
}
unset( $mits_ordered_type );
