<?php
/**
 * Метабокси: поля типів записів і поля сторінок за шаблоном.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Поля типів записів.
 */
function mits_post_type_fields() {
	return array(
		'news'            => array(
			'title'  => 'Показ новини',
			'fields' => array(
				array( 'key' => '_mits_hidden', 'label' => 'Списки новин', 'type' => 'checkbox', 'text' => 'Не показувати в списках новин (сторінка новини лишається доступною за адресою)' ),
			),
		),
		'teacher'         => array(
			'title'  => 'Дані викладача',
			'fields' => array(
				array( 'key' => '_teacher_position', 'label' => 'Посада', 'type' => 'text' ),
				array( 'key' => '_teacher_grade', 'label' => 'Науковий ступінь, звання', 'type' => 'text' ),
				array( 'key' => '_teacher_publications', 'label' => 'Посилання «Наукові праці»', 'type' => 'url', 'help' => 'Google Scholar, ORCID тощо. Порожнє — кнопка не показується.' ),
				array( 'key' => '_teacher_subjects', 'label' => 'Предмети', 'type' => 'list', 'storage' => 'array', 'add' => '+ Додати предмет' ),
				array( 'key' => '_teacher_description', 'label' => 'Про викладача', 'type' => 'textarea', 'rows' => 6, 'help' => 'Необов\'язково. Показується на сторінці викладача.' ),
				array( 'key' => '_teacher_photo_id', 'label' => 'Фото на сторінці викладача', 'type' => 'image_id', 'help' => 'Необов\'язково. Якщо не вибрано, на сторінці викладача фото немає (у списку викладачів завжди показується «Зображення» праворуч).' ),
			),
			'help'   => 'Фото для списку викладачів — «Зображення» праворуч. Порядок у списку — поле «Порядок» у блоці «Атрибути».',
		),
		'mits_hero_slide' => array(
			'title'  => 'Вміст слайда',
			'fields' => array(
				array( 'key' => '_mits_slide_line1', 'label' => 'Рядок 1', 'type' => 'text' ),
				array( 'key' => '_mits_slide_line1_size', 'label' => 'Розмір шрифту рядка 1, px', 'type' => 'number', 'help' => 'Порожнє — розмір зі стилів.' ),
				array( 'key' => '_mits_slide_line2', 'label' => 'Рядок 2', 'type' => 'text' ),
				array( 'key' => '_mits_slide_line2_size', 'label' => 'Розмір шрифту рядка 2, px', 'type' => 'number' ),
				array( 'key' => '_mits_slide_subtitle', 'label' => 'Підзаголовок', 'type' => 'text', 'help' => 'Порожнє — «Кафедра МУЛЬТИМЕДІЙНИХ ТА ІНТЕРНЕТ ТЕХНОЛОГІЙ І СИСТЕМ НТУ ХПІ» (або англійський відповідник).' ),
				array( 'key' => '_mits_slide_social', 'label' => 'Іконки соцмереж', 'type' => 'checkbox', 'text' => 'Показувати іконки Telegram, Instagram, YouTube, TikTok під заголовком' ),
				array( 'key' => '_mits_slide_gradient', 'label' => 'Клас градієнта', 'type' => 'text', 'help' => 'Колір тексту: gradient-orange-cyan, gradient-blue-purple, gradient-green-teal, gradient-pink-magenta, gradient-pink-magenta2. Нові класи — у «МІТС → Блоки → Слайдер».' ),
			),
			'help'   => 'Фото — «Зображення» праворуч. Порядок слайдів — поле «Порядок».',
		),
		'mits_activity'   => array(
			'title'  => 'Картка',
			'fields' => array(
				array( 'key' => '_act_desc', 'label' => 'Опис', 'type' => 'textarea_html' ),
				array( 'key' => '_act_btn', 'label' => 'Текст кнопки', 'type' => 'text', 'help' => 'Порожнє — «Детальніше».' ),
				array( 'key' => '_act_url', 'label' => 'Посилання кнопки', 'type' => 'url' ),
				array( 'key' => '_act_icon_url', 'label' => 'Іконка', 'type' => 'image' ),
				array( 'key' => '_act_icon_bg', 'label' => 'Колір фону іконки', 'type' => 'color' ),
			),
		),
		'mits_advantage'  => array(
			'title'  => 'Текст, що розгортається',
			'fields' => array(
				array( 'key' => '_adv_details', 'label' => 'Текст', 'type' => 'textarea_html', 'rows' => 8, 'help' => 'Кожен новий рядок показується з нового рядка.' ),
			),
		),
		'mits_faq'        => array(
			'title'  => 'Відповідь',
			'fields' => array(
				array( 'key' => '_faq_answer', 'label' => 'Відповідь', 'type' => 'textarea_html', 'rows' => 6 ),
			),
		),
		'mits_edu_level'  => array(
			'title'  => 'Освітній рівень',
			'fields' => array(
				array( 'key' => '_edu_level_page_id', 'label' => 'Сторінка рівня', 'type' => 'page', 'help' => 'Куди веде кнопка на сторінці «Вступ»; на цій сторінці виводяться поля нижче.' ),
				array( 'key' => '_edu_level_steps', 'label' => 'Кроки (стрілочки)', 'type' => 'list', 'add' => '+ Крок' ),
				array( 'key' => '_edu_level_description', 'label' => 'Короткий опис (під кроками)', 'type' => 'html' ),
				array( 'key' => '_edu_level_subjects_title', 'label' => 'Заголовок предметів', 'type' => 'text' ),
				array( 'key' => '_edu_level_subjects', 'label' => 'Предмети', 'type' => 'list', 'add' => '+ Предмет' ),
				array( 'key' => '_edu_level_subject_alt', 'label' => 'Примітка під предметами', 'type' => 'textarea' ),
				array( 'key' => '_edu_level_algorithm_title', 'label' => 'Заголовок алгоритму вступу', 'type' => 'text' ),
				array( 'key' => '_edu_level_algorithm', 'label' => 'Кроки алгоритму', 'type' => 'list', 'item' => 'textarea_html', 'add' => '+ Крок', 'help' => 'Дозволено &lt;strong&gt;, &lt;br&gt;, посилання.' ),
				array( 'key' => '_edu_level_tips_title', 'label' => 'Заголовок порад', 'type' => 'text' ),
				array( 'key' => '_edu_level_tips', 'label' => 'Поради', 'type' => 'list', 'add' => '+ Порада' ),
			),
		),
	);
}

/**
 * Поля сторінок за шаблоном.
 */
function mits_template_fields() {
	return array(
		'template-mits-kafedra.php'       => array(
			'title'  => 'Сторінка «Кафедра»',
			'fields' => array(
				array( 'key' => '_kafedra_hero_id', 'label' => 'Слайд угорі', 'type' => 'slide' ),
				array( 'key' => '_kafedra_history_text', 'label' => 'Текст посилання під описом', 'type' => 'text', 'help' => 'Порожнє — «Історія кафедри».' ),
				array( 'key' => '_kafedra_history_url', 'label' => 'Посилання', 'type' => 'url', 'help' => 'Порожнє — сторінка «Про кафедру».' ),
				array( 'key' => '_kafedra_spec_1', 'label' => 'Траєкторія 1', 'type' => 'text' ),
				array( 'key' => '_kafedra_spec_2', 'label' => 'Траєкторія 2', 'type' => 'text' ),
				array( 'key' => '_kafedra_spec_3', 'label' => 'Траєкторія 3', 'type' => 'text' ),
			),
			'help'   => 'Опис кафедри — основний текст сторінки. Картки «Навчання» — розділ «Діяльність» (перші дві), викладачі — розділ «Викладачі» (перші два за порядком).',
		),
		'template-mits-vstup.php'         => array(
			'title'  => 'Сторінка «Вступ»',
			'fields' => array(
				array( 'key' => '_vstup_hero_id', 'label' => 'Слайд угорі', 'type' => 'slide' ),
				array( 'key' => '_vstup_video_url', 'label' => 'Відео (адреса вбудовування YouTube)', 'type' => 'url', 'help' => 'Наприклад https://www.youtube.com/embed/XXXX. Порожнє — блок не показується.' ),
			),
			'help'   => 'Текст під «Освітні рівні» — основний текст сторінки. Кнопки рівнів — «Освітні рівні (Вступ)», питання — «FAQ (Вступ)».',
		),
		'template-mits-vstup-sub.php'     => array(
			'title'  => 'Підсторінка «Вступ»',
			'fields' => array(
				array( 'key' => '_vstup_sub_hero_id', 'label' => 'Слайд угорі', 'type' => 'slide' ),
			),
			'help'   => 'Основний текст сторінки — картка під слайдом. Кроки, предмети, алгоритм і поради — у записі «Освітні рівні (Вступ)», що посилається на цю сторінку.',
		),
		'template-mits-osvita.php'        => array(
			'title'  => 'Сторінка «Освіта»',
			'fields' => array(
				array( 'key' => '_osvita_hero_id', 'label' => 'Слайд угорі', 'type' => 'slide' ),
				array( 'key' => '_osvita_desc', 'label' => 'Короткий опис (картка з кнопкою «Детальніше»)', 'type' => 'html' ),
				array( 'key' => '_osvita_more_url', 'label' => 'Посилання «Детальніше»', 'type' => 'url', 'help' => 'Порожнє — підсторінка «Загальні положення».' ),
				array( 'key' => '_osvita_slogan', 'label' => 'Слоган', 'type' => 'textarea', 'rows' => 2 ),
			),
			'help'   => 'Логотипи програм, нормативні документи й картки рівнів — «МІТС → Блоки → Освіта».',
		),
		'template-mits-osvita-sub.php'    => array(
			'title'  => 'Підсторінка «Освіта»',
			'fields' => array(
				array( 'key' => '_osvita_sub_hero_id', 'label' => 'Слайд угорі', 'type' => 'slide' ),
			),
			'help'   => 'Основний текст сторінки показується в картці під слайдом (якщо не порожній).',
		),
		'template-mits-nauka.php'         => array(
			'title'  => 'Сторінка «Наука»',
			'fields' => mits_nauka_fields(),
			'help'   => 'Слайд угорі — «МІТС → Блоки → Наука». Посилання можна вказати як повну адресу, як /шлях/ на сайті або як якір #ipst.',
		),
	);
}

/**
 * Поля сторінки «Наука».
 */
function mits_nauka_fields() {
	$link = array(
		array( 'key' => 'text', 'label' => 'Текст', 'type' => 'text' ),
		array( 'key' => 'url', 'label' => 'Посилання', 'type' => 'url' ),
	);
	$acc  = array(
		array( 'key' => 'title', 'label' => 'Заголовок', 'type' => 'text' ),
		array( 'key' => 'text', 'label' => 'Текст', 'type' => 'html' ),
	);
	return array(
		array( 'type' => 'heading', 'label' => 'IPST' ),
		array( 'key' => '_nauka_ipst_title', 'label' => 'Заголовок', 'type' => 'text' ),
		array( 'key' => '_nauka_ipst_lead', 'label' => 'Підзаголовок', 'type' => 'textarea', 'rows' => 2 ),
		array( 'key' => '_nauka_ipst_text', 'label' => 'Текст картки', 'type' => 'html' ),
		array( 'key' => '_nauka_ipst_links', 'label' => 'Посилання в картці', 'type' => 'repeater', 'fields' => $link, 'add' => '+ Посилання' ),
		array( 'key' => '_nauka_ipst_posters', 'label' => 'Афіші', 'type' => 'repeater', 'add' => '+ Афіша', 'fields' => array(
			array( 'key' => 'img', 'label' => 'Зображення', 'type' => 'image' ),
			array( 'key' => 'alt', 'label' => 'Опис зображення (alt)', 'type' => 'text' ),
		) ),

		array( 'type' => 'heading', 'label' => 'Micro CAD' ),
		array( 'key' => '_nauka_microcad_title', 'label' => 'Заголовок', 'type' => 'text' ),
		array( 'key' => '_nauka_microcad_text', 'label' => 'Текст картки', 'type' => 'html' ),
		array( 'key' => '_nauka_microcad_links', 'label' => 'Посилання в картці', 'type' => 'repeater', 'fields' => $link, 'add' => '+ Посилання' ),
		array( 'key' => '_nauka_microcad_stats', 'label' => 'Цифри', 'type' => 'repeater', 'add' => '+ Цифра', 'fields' => array(
			array( 'key' => 'value', 'label' => 'Число', 'type' => 'text' ),
			array( 'key' => 'label', 'label' => 'Підпис', 'type' => 'text' ),
		) ),
		array( 'key' => '_nauka_microcad_dirs_title', 'label' => 'Заголовок напрямів', 'type' => 'text' ),
		array( 'key' => '_nauka_microcad_dirs', 'label' => 'Напрями досліджень (акордеон)', 'type' => 'repeater', 'fields' => $acc, 'add' => '+ Напрям' ),
		array( 'key' => '_nauka_microcad_more', 'label' => 'Посилання під напрямами', 'type' => 'repeater', 'fields' => $link, 'add' => '+ Посилання' ),

		array( 'type' => 'heading', 'label' => 'Наукові гуртки' ),
		array( 'key' => '_nauka_clubs_title', 'label' => 'Заголовок', 'type' => 'text' ),
		array( 'key' => '_nauka_clubs_text', 'label' => 'Текст картки', 'type' => 'html' ),
		array( 'key' => '_nauka_clubs_links', 'label' => 'Посилання в картці', 'type' => 'repeater', 'fields' => $link, 'add' => '+ Посилання' ),
		array( 'key' => '_nauka_clubs', 'label' => 'Гуртки (акордеон)', 'type' => 'repeater', 'fields' => $acc, 'add' => '+ Гурток' ),

		array( 'type' => 'heading', 'label' => 'Стартапи' ),
		array( 'key' => '_nauka_startups_title', 'label' => 'Заголовок', 'type' => 'text' ),
		array( 'key' => '_nauka_startups_text', 'label' => 'Текст картки', 'type' => 'html' ),
		array( 'key' => '_nauka_startups', 'label' => 'Картки стартапів', 'type' => 'repeater', 'add' => '+ Стартап', 'fields' => array(
			array( 'key' => 'img', 'label' => 'Зображення', 'type' => 'image' ),
			array( 'key' => 'title', 'label' => 'Назва', 'type' => 'text' ),
			array( 'key' => 'url', 'label' => 'Посилання', 'type' => 'url' ),
			array( 'key' => 'text', 'label' => 'Опис', 'type' => 'textarea', 'rows' => 2 ),
		) ),

		array( 'type' => 'heading', 'label' => 'Конкурси' ),
		array( 'key' => '_nauka_contests_title', 'label' => 'Заголовок', 'type' => 'text' ),
		array( 'key' => '_nauka_contests_text', 'label' => 'Текст картки', 'type' => 'html' ),
		array( 'key' => '_nauka_contests_years_title', 'label' => 'Заголовок вкладок', 'type' => 'text' ),
		array( 'key' => '_nauka_contests_years', 'label' => 'Навчальні роки (вкладки)', 'type' => 'repeater', 'add' => '+ Рік', 'fields' => array(
			array( 'key' => 'label', 'label' => 'Рік (наприклад 2025/2026)', 'type' => 'text' ),
			array( 'key' => 'items', 'label' => 'Результати (кожен — окремий пункт)', 'type' => 'repeater', 'add' => '+ Пункт', 'fields' => array(
				array( 'key' => 'text', 'label' => '', 'type' => 'textarea_html', 'rows' => 2 ),
			) ),
			array( 'key' => 'empty', 'label' => 'Текст, якщо результатів немає', 'type' => 'text' ),
			array( 'key' => 'more_text', 'label' => 'Текст посилання під списком', 'type' => 'text' ),
			array( 'key' => 'more_url', 'label' => 'Посилання', 'type' => 'url' ),
		) ),
	);
}

/**
 * Реєстрація метабоксів.
 */
add_action( 'add_meta_boxes', function ( $post_type, $post ) {
	$defs = mits_post_type_fields();
	if ( isset( $defs[ $post_type ] ) ) {
		add_meta_box( 'mits_fields', $defs[ $post_type ]['title'], 'mits_render_meta_box', $post_type, 'normal', 'high', $defs[ $post_type ] );
	}
	if ( 'page' === $post_type && $post instanceof WP_Post ) {
		$template = get_page_template_slug( $post );
		$tdefs    = mits_template_fields();
		if ( isset( $tdefs[ $template ] ) ) {
			add_meta_box( 'mits_fields', $tdefs[ $template ]['title'], 'mits_render_meta_box', 'page', 'normal', 'high', $tdefs[ $template ] );
		}
	}
}, 10, 2 );

/**
 * Вивід метабокса.
 *
 * @param WP_Post $post Запис.
 * @param array   $box  Дані метабокса.
 */
function mits_render_meta_box( $post, $box ) {
	$def = $box['args'];
	wp_nonce_field( 'mits_save_fields', 'mits_fields_nonce' );
	if ( ! empty( $def['help'] ) ) {
		echo '<p class="description mits-box-help">' . esc_html( $def['help'] ) . '</p>';
	}
	echo '<table class="form-table mits-fields" role="presentation"><tbody>';
	foreach ( $def['fields'] as $field ) {
		if ( 'heading' === $field['type'] ) {
			echo '<tr class="mits-fields__heading"><th colspan="2"><h3>' . esc_html( $field['label'] ) . '</h3></th></tr>';
			continue;
		}
		$value = get_post_meta( $post->ID, $field['key'], true );
		if ( 'checkbox' === $field['type'] && '_mits_slide_social' === $field['key'] ) {
			$value = '' !== trim( (string) $value );
		}
		echo '<tr><th scope="row"><label>' . esc_html( $field['label'] ) . '</label></th><td>';
		mits_render_field( $field, 'mits_meta[' . $field['key'] . ']', $value );
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Збереження полів.
 */
add_action( 'save_post', function ( $post_id, $post ) {
	if ( ! isset( $_POST['mits_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mits_fields_nonce'] ), 'mits_save_fields' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) || empty( $_POST['mits_meta'] ) || ! is_array( $_POST['mits_meta'] ) ) {
		return;
	}

	$defs = mits_post_type_fields();
	if ( isset( $defs[ $post->post_type ] ) ) {
		$fields = $defs[ $post->post_type ]['fields'];
	} else {
		$tdefs  = mits_template_fields();
		$tpl    = get_page_template_slug( $post );
		$fields = isset( $tdefs[ $tpl ] ) ? $tdefs[ $tpl ]['fields'] : array();
	}

	$input = wp_unslash( $_POST['mits_meta'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- очищується нижче для кожного поля.
	foreach ( $fields as $field ) {
		if ( 'heading' === $field['type'] ) {
			continue;
		}
		$key = $field['key'];
		if ( 'checkbox' === $field['type'] ) {
			$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';
		} elseif ( ! array_key_exists( $key, $input ) ) {
			continue;
		} else {
			$raw = $input[ $key ];
		}
		$value = mits_sanitize_field( $field, $raw );
		if ( '_mits_slide_social' === $key ) {
			$value = $value ? '[mits_social_telegram][mits_social_instagram][mits_social_youtube][mits_social_tiktok]' : '';
		}
		if ( '' === $value || array() === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, wp_slash( $value ) );
		}
	}
}, 10, 2 );
