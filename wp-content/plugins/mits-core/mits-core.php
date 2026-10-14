<?php
/**
 * Plugin Name:       MITS Core
 * Description:       Вміст сайту кафедри МІТС: типи записів (новини, викладачі, слайди, блоки), поля, налаштування блоків, сторінка «Наука», шорткоди контактів, захист і WP-CLI імпорт.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Кафедра МІТС НТУ «ХПІ»
 * License:           GPL-2.0-or-later
 * Text Domain:       mits-core
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'MITS_CORE_VERSION', '1.0.0' );
define( 'MITS_CORE_FILE', __FILE__ );
define( 'MITS_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MITS_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once MITS_CORE_DIR . 'includes/helpers.php';
require_once MITS_CORE_DIR . 'includes/icons.php';
require_once MITS_CORE_DIR . 'includes/post-types.php';
require_once MITS_CORE_DIR . 'includes/fields.php';
require_once MITS_CORE_DIR . 'includes/meta-boxes.php';
require_once MITS_CORE_DIR . 'includes/settings.php';
require_once MITS_CORE_DIR . 'includes/shortcodes.php';
require_once MITS_CORE_DIR . 'includes/polylang.php';
require_once MITS_CORE_DIR . 'includes/security.php';
require_once MITS_CORE_DIR . 'includes/admin.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MITS_CORE_DIR . 'includes/class-mits-importer.php';
	require_once MITS_CORE_DIR . 'includes/cli.php';
}

register_activation_hook( __FILE__, function () {
	mits_register_post_types();
	flush_rewrite_rules();
	mits_protect_uploads_dir();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
