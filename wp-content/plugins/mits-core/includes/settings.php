<?php
/**
 * Сторінка «МІТС → Блоки сайту»: блоки, що зберігаються в опціях mits_* (як на старому сайті).
 *
 * Поле з 'tr' => true має дві версії: українську (основний ключ) і англійську (ключ + «_en»).
 * Поле з 'in' => 'mits_why' зберігається як ключ усередині опції-масиву mits_why.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Право, потрібне для редагування блоків (редактори теж можуть).
 */
function mits_settings_capability() {
	return 'edit_pages';
}

/**
 * Вкладки й поля сторінки налаштувань.
 */
function mits_settings_tabs() {
	$name_url = array(
		array( 'key' => 'name', 'label' => 'Назва', 'type' => 'text' ),
		array( 'key' => 'url', 'label' => 'Посилання', 'type' => 'url' ),
	);
	return array(
		'contacts'      => array(
			'title'  => 'Контакти',
			'help'   => 'Виводяться в розділі «Контакти» (шорткоди [mits_address], [mits_phones], [mits_email], [mits_social_…]), у випадному меню шапки й на слайдах.',
			'fields' => array(
				array( 'key' => 'mits_address', 'in' => 'mits_contacts', 'label' => 'Адреса', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'mits_phone_1', 'in' => 'mits_contacts', 'label' => 'Телефон 1', 'type' => 'text' ),
				array( 'key' => 'mits_phone_2', 'in' => 'mits_contacts', 'label' => 'Телефон 2', 'type' => 'text' ),
				array( 'key' => 'mits_phone_3', 'in' => 'mits_contacts', 'label' => 'Телефон 3', 'type' => 'text' ),
				array( 'key' => 'mits_email', 'in' => 'mits_contacts', 'label' => 'E-mail для відвідувачів', 'type' => 'text', 'help' => 'Показується на сайті. Адреса, на яку приходять заявки з форми, задається в «Контакт → Контактні форми».' ),
				array( 'key' => 'mits_social_telegram', 'in' => 'mits_contacts', 'label' => 'Telegram', 'type' => 'url' ),
				array( 'key' => 'mits_social_instagram', 'in' => 'mits_contacts', 'label' => 'Instagram', 'type' => 'url' ),
				array( 'key' => 'mits_social_youtube', 'in' => 'mits_contacts', 'label' => 'YouTube', 'type' => 'url' ),
				array( 'key' => 'mits_social_tiktok', 'in' => 'mits_contacts', 'label' => 'TikTok', 'type' => 'url' ),
				array( 'key' => 'icon_bg_location', 'in' => 'mits_contacts', 'label' => 'Колір іконки адреси', 'type' => 'color' ),
				array( 'key' => 'icon_bg_phone', 'in' => 'mits_contacts', 'label' => 'Колір іконки телефону', 'type' => 'color' ),
				array( 'key' => 'icon_bg_email', 'in' => 'mits_contacts', 'label' => 'Колір іконки e-mail', 'type' => 'color' ),
				array( 'key' => 'mits_dropdown_logo', 'label' => 'Логотип у випадному меню (під стрілкою)', 'type' => 'image', 'help' => 'Основний логотип у шапці й підвалі — «Вигляд → Налаштувати → Ідентичність сайту».' ),
			),
		),
		'front'         => array(
			'title'  => 'Головна',
			'help'   => 'Слайди — розділ «Слайди головної», переваги — «Переваги», картки — «Діяльність», новини — «Новини».',
			'fields' => array(
				array( 'type' => 'heading', 'label' => 'Чому ми?' ),
				array( 'key' => 'title', 'in' => 'mits_why', 'label' => 'Заголовок', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'text', 'in' => 'mits_why', 'label' => 'Текст', 'type' => 'textarea', 'rows' => 5, 'tr' => true ),
				array( 'key' => 'stat1_num', 'in' => 'mits_why', 'label' => 'Цифра 1', 'type' => 'text' ),
				array( 'key' => 'stat1_label', 'in' => 'mits_why', 'label' => 'Підпис 1', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'stat2_num', 'in' => 'mits_why', 'label' => 'Цифра 2', 'type' => 'text' ),
				array( 'key' => 'stat2_label', 'in' => 'mits_why', 'label' => 'Підпис 2', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'stat3_num', 'in' => 'mits_why', 'label' => 'Цифра 3', 'type' => 'text' ),
				array( 'key' => 'stat3_label', 'in' => 'mits_why', 'label' => 'Підпис 3', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'stat4_num', 'in' => 'mits_why', 'label' => 'Цифра 4', 'type' => 'text' ),
				array( 'key' => 'stat4_label', 'in' => 'mits_why', 'label' => 'Підпис 4', 'type' => 'text', 'tr' => true ),
				array( 'type' => 'heading', 'label' => 'Переваги' ),
				array( 'key' => 'title', 'in' => 'mits_advantages', 'label' => 'Заголовок', 'type' => 'text', 'tr' => true ),
				array( 'type' => 'heading', 'label' => 'Блок «Якісне, фундаментальне та online IT-навчання»' ),
				array( 'key' => 'mits_front_edu_title', 'label' => 'Заголовок', 'type' => 'textarea', 'rows' => 2, 'tr' => true, 'help' => 'Перший рядок — помаранчевий, другий — блакитний.' ),
				array( 'key' => 'mits_front_edu_text', 'label' => 'Текст картки', 'type' => 'html', 'tr' => true ),
				array( 'type' => 'heading', 'label' => 'Діяльність' ),
				array( 'key' => 'title', 'in' => 'mits_activities', 'label' => 'Заголовок', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'cta_text', 'in' => 'mits_activities', 'label' => 'Текст кнопки під картками', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'cta_url', 'in' => 'mits_activities', 'label' => 'Посилання кнопки', 'type' => 'url', 'tr' => true ),
			),
		),
		'osvita'        => array(
			'title'  => 'Освіта',
			'help'   => 'Блоки сторінки «Освіта». Опис, слоган і слайд — у полях самої сторінки.',
			'fields' => array(
				array( 'key' => 'mits_osvita_tech_items', 'label' => 'Логотипи програм', 'type' => 'repeater', 'add' => '+ Логотип', 'fields' => array(
					array( 'key' => 'img', 'label' => 'Зображення', 'type' => 'image' ),
					array( 'key' => 'link', 'label' => 'Посилання (необов\'язково)', 'type' => 'url' ),
				) ),
				array( 'key' => 'mits_osvita_docs_title', 'label' => 'Заголовок документів', 'type' => 'text', 'tr' => true, 'help' => 'Порожнє — «Нормативні документи».' ),
				array( 'key' => 'mits_osvita_docs_desc', 'label' => 'Текст про документи', 'type' => 'html', 'tr' => true ),
				array( 'key' => 'mits_osvita_docs_items', 'label' => 'Список документів', 'type' => 'repeater', 'tr' => true, 'add' => '+ Документ', 'fields' => $name_url ),
				array( 'key' => 'mits_osvita_level_blocks', 'label' => 'Картки рівнів (Бакалавр, Магістратура, Аспірантура)', 'type' => 'repeater', 'tr' => true, 'add' => '+ Картка', 'fields' => array(
					array( 'key' => 'title', 'label' => 'Назва', 'type' => 'text' ),
					array( 'key' => 'color', 'label' => 'Колір назви', 'type' => 'color' ),
					array( 'key' => 'docs', 'label' => 'Документи', 'type' => 'repeater', 'add' => '+ Документ', 'fields' => $name_url ),
					array( 'key' => 'vote_url', 'label' => 'Посилання кнопки «Проголосувати»', 'type' => 'url' ),
					array( 'path' => array( 'schedule', 'name' ), 'key' => 'schedule_name', 'label' => 'Розклад занять: текст', 'type' => 'text' ),
					array( 'path' => array( 'schedule', 'url' ), 'key' => 'schedule_url', 'label' => 'Розклад занять: посилання', 'type' => 'url' ),
					array( 'path' => array( 'exams', 'name' ), 'key' => 'exams_name', 'label' => 'Розклад екзаменів: текст', 'type' => 'text' ),
					array( 'path' => array( 'exams', 'url' ), 'key' => 'exams_url', 'label' => 'Розклад екзаменів: посилання', 'type' => 'url' ),
				) ),
			),
		),
		'international' => array(
			'title'  => 'Міжнародність',
			'fields' => array(
				array( 'key' => 'mits_intl_hero_id', 'label' => 'Слайд угорі', 'type' => 'slide' ),
				array( 'key' => 'mits_intl_photo', 'label' => 'Фото', 'type' => 'image' ),
				array( 'key' => 'mits_intl_text', 'label' => 'Текст біля фото', 'type' => 'textarea', 'rows' => 4, 'tr' => true ),
				array( 'key' => 'mits_intl_links', 'label' => 'Посилання під текстом', 'type' => 'repeater', 'tr' => true, 'add' => '+ Посилання', 'fields' => $name_url, 'help' => 'Якщо назву не вказано, показується адреса.' ),
				array( 'key' => 'mits_intl_stats', 'label' => 'Цифри університету-партнера', 'type' => 'repeater', 'tr' => true, 'add' => '+ Цифра', 'fields' => array(
					array( 'key' => 'value', 'label' => 'Число', 'type' => 'text' ),
					array( 'key' => 'label', 'label' => 'Підпис', 'type' => 'text' ),
				), 'help' => 'По чотири в рядку.' ),
				array( 'key' => 'mits_intl_courses_photo', 'label' => 'Фото біля опису університету', 'type' => 'image' ),
				array( 'key' => 'mits_intl_courses_text', 'label' => 'Опис університету', 'type' => 'textarea', 'rows' => 6, 'tr' => true ),
				array( 'key' => 'mits_intl_courses_slogan', 'label' => 'Слоган', 'type' => 'textarea', 'rows' => 2, 'tr' => true ),
				array( 'key' => 'mits_intl_courses_title', 'label' => 'Заголовок курсів', 'type' => 'textarea', 'rows' => 2, 'tr' => true ),
				array( 'key' => 'mits_intl_courses', 'label' => 'Курси (акордеон)', 'type' => 'repeater', 'tr' => true, 'add' => '+ Курс', 'fields' => array(
					array( 'key' => 'name', 'label' => 'Назва', 'type' => 'text' ),
					array( 'key' => 'desc', 'label' => 'Опис', 'type' => 'textarea', 'rows' => 3 ),
				) ),
				array( 'key' => 'mits_intl_docs_title', 'label' => 'Заголовок документів', 'type' => 'text', 'tr' => true ),
				array( 'key' => 'mits_intl_docs', 'label' => 'Документи', 'type' => 'repeater', 'tr' => true, 'add' => '+ Документ', 'fields' => $name_url ),
			),
		),
		'nauka'         => array(
			'title'  => 'Наука',
			'help'   => 'Решта блоків «Науки» (IPST, Micro CAD, гуртки, стартапи, конкурси) редагуються на самій сторінці «Наука» (кожна мова — своя сторінка).',
			'fields' => array(
				array( 'key' => 'mits_nauka_hero_id', 'label' => 'Слайд угорі', 'type' => 'slide' ),
			),
		),
		'hero'          => array(
			'title'  => 'Слайдер',
			'fields' => array(
				array( 'key' => 'mits_hero_custom_css', 'label' => 'CSS класів градієнтів', 'type' => 'textarea', 'rows' => 16, 'help' => 'Клас указується в полі «Клас градієнта» слайда. Лише CSS, без &lt;style&gt;.' ),
			),
		),
	);
}

add_action( 'admin_menu', function () {
	add_menu_page( 'Блоки сайту', 'МІТС: блоки', mits_settings_capability(), 'mits-blocks', 'mits_render_settings_page', 'dashicons-layout', 4 );
} );

/**
 * Значення поля налаштувань для мови.
 *
 * @param array  $field Опис поля.
 * @param string $lang  uk|en.
 */
function mits_settings_value( $field, $lang ) {
	$suffix = ( 'uk' === $lang ) ? '' : '_' . $lang;
	if ( ! empty( $field['in'] ) ) {
		$arr = get_option( $field['in'], array() );
		$key = $field['key'] . $suffix;
		return is_array( $arr ) && isset( $arr[ $key ] ) ? $arr[ $key ] : '';
	}
	return get_option( $field['key'] . $suffix, '' );
}

/**
 * Сторінка налаштувань.
 */
function mits_render_settings_page() {
	if ( ! current_user_can( mits_settings_capability() ) ) {
		return;
	}
	$tabs = mits_settings_tabs();
	$tab  = isset( $_GET['tab'] ) && isset( $tabs[ sanitize_key( $_GET['tab'] ) ] ) ? sanitize_key( $_GET['tab'] ) : 'contacts'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	echo '<div class="wrap mits-settings"><h1>Блоки сайту</h1>';
	if ( isset( $_GET['saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
	}
	echo '<nav class="nav-tab-wrapper">';
	foreach ( $tabs as $slug => $def ) {
		printf( '<a href="%1$s" class="nav-tab%2$s">%3$s</a>', esc_url( add_query_arg( array( 'page' => 'mits-blocks', 'tab' => $slug ), admin_url( 'admin.php' ) ) ), $slug === $tab ? ' nav-tab-active' : '', esc_html( $def['title'] ) );
	}
	echo '</nav>';
	$def = $tabs[ $tab ];
	if ( ! empty( $def['help'] ) ) {
		echo '<p class="description mits-box-help">' . esc_html( $def['help'] ) . '</p>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="mits_save_blocks"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
	wp_nonce_field( 'mits_save_blocks_' . $tab );
	echo '<table class="form-table mits-fields" role="presentation"><tbody>';
	foreach ( $def['fields'] as $field ) {
		if ( 'heading' === $field['type'] ) {
			echo '<tr class="mits-fields__heading"><th colspan="2"><h2>' . esc_html( $field['label'] ) . '</h2></th></tr>';
			continue;
		}
		echo '<tr><th scope="row">' . esc_html( $field['label'] ) . '</th><td>';
		$langs = ! empty( $field['tr'] ) ? array_keys( mits_languages() ) : array( 'uk' );
		if ( count( $langs ) > 1 ) {
			echo '<div class="mits-langs">';
		}
		foreach ( $langs as $lang ) {
			if ( count( $langs ) > 1 ) {
				echo '<div class="mits-langs__col"><span class="mits-langs__label">' . ( 'uk' === $lang ? 'Українською' : 'English' ) . '</span>';
			}
			$name = 'mits_blocks[' . $lang . '][' . ( ! empty( $field['in'] ) ? $field['in'] . '][' : '' ) . $field['key'] . ']';
			mits_render_field( $field, $name, mits_settings_value( $field, $lang ) );
			if ( count( $langs ) > 1 ) {
				echo '</div>';
			}
		}
		if ( count( $langs ) > 1 ) {
			echo '</div>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	submit_button( 'Зберегти' );
	echo '</form></div>';
}

/**
 * Збереження вкладки налаштувань.
 */
add_action( 'admin_post_mits_save_blocks', function () {
	$tabs = mits_settings_tabs();
	$tab  = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : '';
	if ( ! isset( $tabs[ $tab ] ) || ! current_user_can( mits_settings_capability() ) ) {
		wp_die( 'Недостатньо прав.', 403 );
	}
	check_admin_referer( 'mits_save_blocks_' . $tab );
	$input = isset( $_POST['mits_blocks'] ) ? wp_unslash( $_POST['mits_blocks'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- очищується для кожного поля.

	foreach ( $tabs[ $tab ]['fields'] as $field ) {
		if ( 'heading' === $field['type'] ) {
			continue;
		}
		$langs = ! empty( $field['tr'] ) ? array_keys( mits_languages() ) : array( 'uk' );
		foreach ( $langs as $lang ) {
			$suffix = ( 'uk' === $lang ) ? '' : '_' . $lang;
			if ( ! empty( $field['in'] ) ) {
				$raw = isset( $input[ $lang ][ $field['in'] ][ $field['key'] ] ) ? $input[ $lang ][ $field['in'] ][ $field['key'] ] : '';
				$arr = get_option( $field['in'], array() );
				$arr = is_array( $arr ) ? $arr : array();
				$arr[ $field['key'] . $suffix ] = mits_sanitize_field( $field, $raw );
				update_option( $field['in'], $arr );
			} else {
				$raw   = isset( $input[ $lang ][ $field['key'] ] ) ? $input[ $lang ][ $field['key'] ] : '';
				$value = 'mits_hero_custom_css' === $field['key'] ? wp_strip_all_tags( (string) $raw ) : mits_sanitize_field( $field, $raw );
				update_option( $field['key'] . $suffix, $value );
			}
		}
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'mits-blocks', 'tab' => $tab, 'saved' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
} );
