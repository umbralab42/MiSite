<?php
/**
 * Тема сайту кафедри МІТС НТУ «ХПІ».
 *
 * Класична PHP-тема без батьківської. Дані (типи записів, поля, налаштування блоків)
 * дає плагін mits-core; тема лише виводить їх у розмітці затвердженого дизайну
 * (reference/static-site/).
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

define( 'MITS_THEME_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/i18n.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/template-tags.php';

/**
 * Підтримка можливостей WordPress і меню.
 */
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'mits', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 80,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Темний вигляд у редакторі, щоб редактор бачив приблизно те саме, що й на сайті.
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'css/mits-components.css', 'css/editor.css' ) );

	register_nav_menus( array(
		'mits-primary' => __( 'Головна навігація (шапка і підвал)', 'mits' ),
	) );

	// Розміри, у яких були нарізані зображення старого сайту (імена файлів у статичній копії).
	add_image_size( 'be_thumbnail', 150, 150, true );
	add_image_size( 'mobile-srcset', 480, 0, false );
} );

/**
 * Розмітка зображень у srcset/sizes як у статичній копії: для мобільних — 480px.
 */
add_filter( 'wp_calculate_image_sizes', function ( $sizes, $size ) {
	$width = is_array( $size ) ? (int) $size[0] : 0;
	if ( $width > 0 ) {
		$mobile = min( 480, $width );
		if ( $width >= 768 ) {
			return sprintf( '(max-width:767px) %1$dpx, (max-width:%2$dpx) 100vw, %2$dpx', $mobile, $width );
		}
		return sprintf( '(max-width:767px) %1$dpx, %2$dpx', $mobile, $width );
	}
	return $sizes;
}, 10, 2 );

/**
 * Стилі й скрипти. Порядок підключення такий самий, як у статичній копії:
 * CF7 → шрифти/слайдер → базові правила (з betheme) → mits-* → налаштування → градієнти.
 */
add_action( 'wp_enqueue_scripts', function () {
	$uri = get_template_directory_uri();
	$ver = MITS_THEME_VERSION;
	$has_hero = mits_page_has_hero();

	if ( $has_hero ) {
		wp_enqueue_style( 'geist-font', 'https://fonts.googleapis.com/css2?family=Geist:wght@400;600;700;800&display=swap', array(), null );
		wp_enqueue_style( 'mits-hero', $uri . '/css/mits-hero.css', array( 'geist-font' ), $ver );
	}
	wp_enqueue_style( 'mits-base', $uri . '/css/mits-base.css', array(), $ver );
	wp_enqueue_style( 'mfn-fonts', 'https://fonts.googleapis.com/css?family=Montserrat:1,300,400,400italic,500,600,700,700italic|Geist:1,300,400,400italic,500,600,700,700italic|Outfit:1,300,400,400italic,500,600,700,700italic&display=swap', array(), null );
	wp_enqueue_style( 'mfn-font-button', 'https://fonts.googleapis.com/css?family=Inter:400,400&display=swap', array(), null );
	wp_enqueue_style( 'mits-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'mits-components', $uri . '/css/mits-components.css', array( 'mits-base' ), $ver );
	wp_enqueue_style( 'mits-front', $uri . '/css/mits-front.css', array( 'mits-components' ), $ver );
	wp_enqueue_style( 'mits-pages', $uri . '/css/mits-pages.css', array( 'mits-front' ), $ver );
	$last = 'mits-pages';
	if ( is_post_type_archive( 'teacher' ) || is_singular( 'teacher' ) ) {
		wp_enqueue_style( 'mits-teachers', $uri . '/css/mits-teachers.css', array( 'mits-pages' ), $ver );
		$last = 'mits-teachers';
	}
	if ( is_page_template( 'template-mits-nauka.php' ) ) {
		wp_enqueue_style( 'mits-nauka', $uri . '/css/mits-nauka.css', array( 'mits-pages' ), $ver );
		wp_enqueue_script( 'mits-nauka', $uri . '/js/mits-nauka.js', array(), $ver, true );
		$last = 'mits-nauka';
	}
	wp_enqueue_style( 'mits-settings', $uri . '/css/mits-settings.css', array( $last ), $ver );
	wp_enqueue_style( 'mits-theme', $uri . '/css/mits-theme.css', array( 'mits-settings' ), $ver );

	if ( $has_hero ) {
		// Класи градієнтів слайдів редагуються в адмінці (МІТС → Блоки → Слайдер).
		$css = function_exists( 'mits_get_option' ) ? (string) mits_get_option( 'mits_hero_custom_css', '' ) : '';
		if ( '' !== trim( $css ) ) {
			wp_add_inline_style( 'mits-theme', wp_strip_all_tags( $css ) );
		}
		wp_enqueue_script( 'mits-hero', $uri . '/js/mits-hero.js', array(), $ver, true );
	}

	wp_enqueue_script( 'mits-nav', $uri . '/js/mits-nav.js', array(), $ver, true );
	wp_enqueue_script( 'mits-accordion', $uri . '/js/mits-accordion.js', array(), $ver, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}, 20 );

/**
 * Класи body, від яких залежать стилі, перенесені з betheme.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( is_front_page() || is_page_template( 'template-mits-kafedra.php' ) ) {
		$classes[] = 'no-content-padding';
	}
	if ( is_front_page() || is_page_template( array( 'template-mits-kafedra.php', 'template-mits-vstup.php' ) ) ) {
		$classes[] = 'full-width-site';
	}
	if ( is_404() ) {
		$classes[] = 'default-404';
	}
	return $classes;
} );

/**
 * Емодзі WordPress не потрібні (дизайн використовує системні прапорці в перемикачі мов).
 */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
} );

/**
 * Заголовок вкладки: «Сторінка – МІТС», головна — «МІТС – опис».
 */
add_filter( 'document_title_separator', function () {
	return '&#8211;';
} );

/**
 * У меню підвалу пункти без атрибута id (щоб не дублювати id пунктів шапки).
 */
add_filter( 'nav_menu_item_id', function ( $id, $item, $args ) {
	return ! empty( $args->mits_footer ) ? '' : $id;
}, 10, 3 );

/**
 * Заголовок сторінки 404 мовою сайту (не залежить від мовного пакета WordPress).
 */
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_404() ) {
		$parts['title'] = mits_t( '404.doc_title' );
	}
	return $parts;
} );

/**
 * Назви архівів новин і викладачів мовою сторінки.
 */
add_filter( 'post_type_archive_title', function ( $title, $post_type ) {
	if ( 'news' === $post_type ) {
		return mits_t( 'news.title' );
	}
	if ( 'teacher' === $post_type ) {
		return mits_t( 'teachers.title' );
	}
	return $title;
}, 10, 2 );

/**
 * Архіви: викладачі — усі за полем «Порядок», новини — по 12, нові спершу.
 */
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'teacher' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
	} elseif ( $query->is_post_type_archive( 'news' ) ) {
		$query->set( 'posts_per_page', 12 );
		$query->set( 'meta_query', mits_visible_news_meta_query() );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
} );
