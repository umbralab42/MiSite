<?php
/**
 * WP-CLI: wp mits import content/content.json
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Команди сайту МІТС.
 */
class Mits_CLI_Command {

	/**
	 * Імпортує вміст старого сайту в чистий WordPress.
	 *
	 * Потрібні активні плагіни Polylang і Contact Form 7. Записи створюються з тими самими ID,
	 * що в старій базі; файли копіюються з reference/static-site/wp-content/uploads/;
	 * англійські тексти, яких немає в базі, беруться з reference/static-site/en/.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Шлях до content/content.json.
	 *
	 * [--static=<dir>]
	 * : Каталог статичної копії. За замовчуванням — reference/static-site поруч із content/.
	 *
	 * [--recipient=<email>]
	 * : Куди надсилати заявки з форми «Консультація». Без параметра — e-mail адміністратора сайту
	 *   (змінюється в адмінці: Контакт → Контактні форми → Лист).
	 *
	 * [--force]
	 * : Імпортувати повторно: спершу видаляються всі раніше імпортовані записи.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mits import content/content.json
	 *     wp mits import /project/content/content.json --static=/project/reference/static-site
	 *
	 * @param array $args       Аргументи.
	 * @param array $assoc_args Параметри.
	 */
	public function import( $args, $assoc_args ) {
		$file = realpath( $args[0] );
		if ( ! $file || ! is_readable( $file ) ) {
			WP_CLI::error( 'Файл не знайдено: ' . $args[0] );
		}
		$static = isset( $assoc_args['static'] ) ? $assoc_args['static'] : dirname( dirname( $file ) ) . '/reference/static-site';
		$static = realpath( $static );
		if ( ! $static || ! is_dir( $static . '/wp-content/uploads' ) ) {
			WP_CLI::error( 'Не знайдено статичну копію (каталог з wp-content/uploads). Вкажіть --static=<dir>.' );
		}
		if ( get_option( 'mits_import_done' ) ) {
			if ( empty( $assoc_args['force'] ) ) {
				WP_CLI::error( 'Імпорт уже виконано ' . get_option( 'mits_import_done' ) . '. Для повторного імпорту додайте --force (усі імпортовані записи буде видалено).' );
			}
			$this->purge();
		}
		$recipient = isset( $assoc_args['recipient'] ) ? sanitize_email( $assoc_args['recipient'] ) : '';
		if ( isset( $assoc_args['recipient'] ) && ! is_email( $recipient ) ) {
			WP_CLI::error( 'Неправильний e-mail у --recipient.' );
		}

		$importer = new Mits_Importer( $file, $static, array( 'recipient' => $recipient ), function ( $msg ) {
			WP_CLI::log( $msg );
		} );
		try {
			$importer->run();
		} catch ( Throwable $e ) {
			WP_CLI::error( $e->getMessage() );
		}
		WP_CLI::success( 'Імпорт завершено.' );
	}

	/**
	 * Видаляє раніше імпортовані записи (для --force).
	 */
	private function purge() {
		$ids = get_posts( array(
			'post_type'      => array_merge( Mits_Importer::POST_TYPES, array( 'attachment' ) ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'lang'           => '',
			'meta_query'     => array( array( 'key' => '_mits_import', 'compare' => 'EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		foreach ( array( 'main_menu_mits_ua', 'main_menu_mits_en' ) as $menu ) {
			if ( wp_get_nav_menu_object( $menu ) ) {
				wp_delete_nav_menu( $menu );
			}
		}
		delete_option( 'mits_import_done' );
		WP_CLI::log( 'Видалено раніше імпортованих записів: ' . count( $ids ) . '.' );
	}
}

WP_CLI::add_command( 'mits', 'Mits_CLI_Command' );
