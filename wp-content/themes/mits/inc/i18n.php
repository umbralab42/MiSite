<?php
/**
 * Рядки інтерфейсу теми українською та англійською.
 *
 * Тексти сторінок, новин і блоків редагуються в адмінці; тут лише підписи елементів
 * (кнопки, заголовки секцій). Англійські варіанти можна змінити без коду в
 * «Мови → Переклади рядків» (група «МІТС»).
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ключ => [uk, en].
 */
function mits_ui_strings() {
	static $strings = null;
	if ( null !== $strings ) {
		return $strings;
	}
	$pairs = array(
		// Шапка й підвал.
		'nav.home_aria'        => array( 'на головну', 'Go to homepage' ),
		'nav.hamburger_aria'   => array( 'Відкрити навігацію', 'Open navigation' ),
		'nav.arrow_aria'       => array( 'Відкрити меню', 'Open menu' ),
		'nav.label'            => array( 'Main navigation', 'Main navigation' ),
		'nav.footer_label'     => array( 'Footer navigation', 'Footer navigation' ),
		'nav.lang_switcher'    => array( 'Перемикач мови', 'Language switcher' ),
		'nav.cta'              => array( 'Консультація', 'Consultation' ),
		'nav.top_news'         => array( 'Топ новин', 'Top News' ),
		'footer.form_title'    => array( 'Контактна форма', 'Contact form' ),
		'footer.copyright'     => array( 'All rights reserved and designed', 'All rights reserved and designed' ),
		'footer.designed_by'   => array( 'Designed by katrine.gnz', 'Designed by katrine.gnz' ),
		// Слайдер.
		'hero.subtitle'        => array( 'Кафедра МУЛЬТИМЕДІЙНИХ ТА ІНТЕРНЕТ ТЕХНОЛОГІЙ І СИСТЕМ НТУ ХПІ', 'Department of MULTIMEDIA AND INTERNET TECHNOLOGIES AND SYSTEMS, NTU KhPI' ),
		'hero.slides'          => array( 'Слайди', 'Slides' ),
		'hero.slide'           => array( 'Слайд %d', 'Slide %d' ),
		// Головна.
		'front.why'            => array( 'Чому ми?', 'Why choose us?' ),
		'front.advantages'     => array( 'Переваги', 'Advantages' ),
		'front.activities'     => array( 'Діяльність', 'Activities' ),
		'front.activities_cta' => array( 'Навчальна програма', 'Curriculum' ),
		'front.more'           => array( 'Детальніше', 'Learn more' ),
		'news.title'           => array( 'Новини', 'News' ),
		'news.empty'           => array( 'Новин ще не додано.', 'No news yet.' ),
		'news.back'            => array( '← Всі новини', '← All News' ),
		// Кафедра й викладачі.
		'kafedra.tracks'       => array( 'Траєкторії', 'Tracks' ),
		'kafedra.education'    => array( 'Навчання', 'Education' ),
		'kafedra.teachers'     => array( 'ВИКЛАДАЧІ', 'TEACHERS' ),
		'kafedra.all_teachers' => array( 'Всі викладачі', 'All teachers' ),
		'kafedra.history'      => array( 'Історія кафедри', 'Department history' ),
		'teachers.title'       => array( 'Викладачі', 'Teachers' ),
		'teachers.empty'       => array( 'Викладачів ще не додано.', 'No teachers yet.' ),
		'teachers.publications' => array( 'НАУКОВІ ПРАЦІ', 'PUBLICATIONS' ),
		'teachers.subjects'    => array( 'Предмети', 'Courses' ),
		'teachers.about'       => array( 'Про викладача', 'About the teacher' ),
		'teachers.back'        => array( '← Всі викладачі', '← All teachers' ),
		// Вступ.
		'vstup.levels'         => array( 'ОСВІТНІ РІВНІ', 'EDUCATIONAL LEVELS' ),
		'vstup.faq'            => array( 'FAQ', 'FAQ' ),
		'vstup.algorithm'      => array( 'Алгоритм вступу', 'Admission steps' ),
		'vstup.tips'           => array( 'Важливо', 'Important' ),
		'vote.title'           => array( 'Голосування', 'Voting' ),
		'vote.unavailable'     => array( 'Голосування тимчасово недоступне.', 'Voting is temporarily unavailable.' ),
		// Освіта.
		'osvita.docs'          => array( 'Нормативні документи', 'Regulatory documents' ),
		'osvita.vote'          => array( 'Проголосувати', 'Vote' ),
		'osvita.schedule'      => array( 'Розклад занять', 'Class schedule' ),
		'osvita.exams'         => array( 'Розклад екзаменів', 'Exam schedule' ),
		// Наука.
		'nauka.more'           => array( 'Детальніше', 'Learn more' ),
		// 404.
		'404.title'            => array( 'Ooops... Error 404', 'Ooops... Error 404' ),
		'404.subtitle'         => array( 'We are sorry, but the page you are looking for does not exist.', 'We are sorry, but the page you are looking for does not exist.' ),
		'404.text'             => array( 'Please check entered address and try again or ', 'Please check entered address and try again or ' ),
		'404.button'           => array( 'go to homepage', 'go to homepage' ),
		'404.doc_title'        => array( 'Сторінку не знайдено', 'Page not found' ),
		// Форма коментарів у новинах.
		'comments.reply'       => array( 'Залишити відповідь', 'Leave a Reply' ),
		'comments.reply_to'    => array( 'Залишити відповідь для %s', 'Leave a Reply to %s' ),
		'comments.cancel'      => array( 'Скасувати коментар', 'Cancel reply' ),
		'comments.notes'       => array( 'Ваша e-mail адреса не оприлюднюватиметься.', 'Your email address will not be published.' ),
		'comments.required'    => array( 'Обов’язкові поля позначені %s', 'Required fields are marked %s' ),
		'comments.comment'     => array( 'Коментар', 'Comment' ),
		'comments.name'        => array( 'Ім\'я', 'Name' ),
		'comments.email'       => array( 'Email', 'Email' ),
		'comments.url'         => array( 'Сайт', 'Website' ),
		'comments.cookies'     => array( 'Зберегти моє ім\'я, e-mail, та адресу сайту в цьому браузері для моїх подальших коментарів.', 'Save my name, email, and website in this browser for the next time I comment.' ),
		'comments.submit'      => array( 'Опублікувати коментар', 'Post Comment' ),
	);
	$strings = array();
	foreach ( $pairs as $key => $pair ) {
		$strings[ $key ] = array( 'uk' => $pair[0], 'en' => $pair[1] );
	}
	return $strings;
}

/**
 * Рядок інтерфейсу поточною мовою.
 *
 * @param string $key Ключ рядка.
 */
function mits_t( $key ) {
	$strings = mits_ui_strings();
	if ( ! isset( $strings[ $key ] ) ) {
		return $key;
	}
	$lang = function_exists( 'mits_lang' ) ? mits_lang() : 'uk';
	if ( 'uk' === $lang ) {
		return $strings[ $key ]['uk'];
	}
	// Переклад, заданий редактором у Polylang, має перевагу над вбудованим.
	if ( function_exists( 'pll_translate_string' ) ) {
		$custom = pll_translate_string( $strings[ $key ]['uk'], $lang );
		if ( $custom !== $strings[ $key ]['uk'] ) {
			return $custom;
		}
	}
	return isset( $strings[ $key ][ $lang ] ) ? $strings[ $key ][ $lang ] : $strings[ $key ]['uk'];
}

/**
 * Виводить рядок інтерфейсу, екранований для HTML.
 *
 * @param string $key Ключ рядка.
 */
function mits_te( $key ) {
	echo esc_html( mits_t( $key ) );
}
