<?php
/**
 * Захист сайту. Частина правил дублюється в .htaccess (deploy/htaccess), щоб
 * працювати й тоді, коли сервер ігнорує .htaccess.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/* ---------- XML-RPC вимкнено ---------- */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
add_filter( 'pings_open', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

/* ---------- Версія WordPress не розкривається ---------- */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* ---------- Перелічення користувачів ---------- */

// ?author=N та архіви авторів: 404 для відвідувачів.
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) {
		return;
	}
	if ( isset( $_GET['author'] ) || is_author() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}, 1 );
add_filter( 'author_link', function () {
	return home_url( '/' );
} );

// REST API: /wp/v2/users і пошук користувачів лише для тих, хто може редагувати записи.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( current_user_can( 'list_users' ) || current_user_can( 'edit_posts' ) ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
} );

// Імена авторів не потрапляють у oEmbed і карти сайту.
add_filter( 'oembed_response_data', function ( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
} );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

// Однакове повідомлення про помилку входу (не підказує, чи існує логін).
add_filter( 'login_errors', function () {
	return 'Неправильний логін або пароль.';
} );

/* ---------- Заголовки безпеки (дублюють .htaccess) ---------- */
add_action( 'send_headers', function () {
	if ( headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
} );

/* ---------- Автоматичні оновлення безпеки ядра й плагінів ---------- */
add_filter( 'allow_minor_auto_core_updates', '__return_true' );
add_filter( 'auto_update_plugin', function ( $update, $item ) {
	// Безкоштовні плагіни з wordpress.org, від яких залежить безпека й робота сайту.
	$auto = array( 'polylang', 'contact-form-7', 'limit-login-attempts-reloaded', 'two-factor' );
	if ( isset( $item->slug ) && in_array( $item->slug, $auto, true ) ) {
		return true;
	}
	return $update;
}, 10, 2 );

/* ---------- Каталог завантажень: заборона виконання скриптів ---------- */

/**
 * Записує .htaccess у wp-content/uploads, якщо його ще немає.
 */
function mits_protect_uploads_dir() {
	$uploads = wp_get_upload_dir();
	if ( empty( $uploads['basedir'] ) || ! wp_mkdir_p( $uploads['basedir'] ) ) {
		return;
	}
	$file = trailingslashit( $uploads['basedir'] ) . '.htaccess';
	if ( file_exists( $file ) ) {
		return;
	}
	$rules = "# Створено плагіном MITS Core: у каталозі завантажень скрипти не виконуються.\n"
		. "<FilesMatch \"\\.(?i:php[0-9]?|phtml|phar|pl|py|cgi|sh|shtml)$\">\n\tRequire all denied\n</FilesMatch>\n"
		. "<IfModule mod_php.c>\n\tphp_flag engine off\n</IfModule>\n"
		. "<IfModule mod_php8.c>\n\tphp_flag engine off\n</IfModule>\n"
		. "Options -Indexes -ExecCGI\n";
	file_put_contents( $file, $rules ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}
add_action( 'admin_init', function () {
	if ( get_option( 'mits_uploads_protected' ) !== MITS_CORE_VERSION ) {
		mits_protect_uploads_dir();
		update_option( 'mits_uploads_protected', MITS_CORE_VERSION, false );
	}
} );

/* ---------- Завантаження: лише звичні типи файлів ---------- */
add_filter( 'upload_mimes', function ( $mimes ) {
	unset( $mimes['swf'], $mimes['exe'], $mimes['htm|html'], $mimes['js'] );
	return $mimes;
} );

/* ---------- Обов'язковий двофакторний вхід для адміністраторів ---------- */

/**
 * Чи налаштовано другий фактор (плагін Two Factor) для користувача.
 *
 * @param int $user_id ID користувача.
 */
function mits_user_has_2fa( $user_id ) {
	return class_exists( 'Two_Factor_Core' ) && Two_Factor_Core::is_user_using_two_factor( $user_id );
}

// Якщо MITS_REQUIRE_2FA увімкнено, адміністратор без другого фактора бачить лише свій профіль.
add_action( 'admin_init', function () {
	if ( ! defined( 'MITS_REQUIRE_2FA' ) || ! MITS_REQUIRE_2FA || ! class_exists( 'Two_Factor_Core' ) || wp_doing_ajax() ) {
		return;
	}
	$user = wp_get_current_user();
	if ( ! $user->exists() || ! user_can( $user, 'manage_options' ) || mits_user_has_2fa( $user->ID ) ) {
		return;
	}
	global $pagenow;
	if ( in_array( $pagenow, array( 'profile.php', 'admin-post.php' ), true ) ) {
		return;
	}
	wp_safe_redirect( admin_url( 'profile.php#two-factor-options' ) );
	exit;
} );

add_action( 'admin_notices', function () {
	$user = wp_get_current_user();
	if ( ! class_exists( 'Two_Factor_Core' ) || ! $user->exists() || ! user_can( $user, 'manage_options' ) || mits_user_has_2fa( $user->ID ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>Увімкніть двофакторний вхід.</strong> Для адміністраторів він обов\'язковий: налаштуйте застосунок-автентифікатор (TOTP) у розділі «Two-Factor Options» нижче на сторінці профілю та збережіть резервні коди.</p></div>';
} );
