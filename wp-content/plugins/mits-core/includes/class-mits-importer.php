<?php
/**
 * Імпорт вмісту старого сайту в чистий WordPress.
 *
 * Джерела:
 *  - content/content.json — записи, поля, налаштування mits_* старого сайту (українською;
 *    англійських записів там небагато);
 *  - reference/static-site/ — затверджена статична копія: файли завантажень, англійські
 *    тексти (en/) і блоки, яких немає в базі (сторінка «Наука», блок «Якісне… IT-навчання»).
 *
 * Ідентифікатори записів зберігаються такими, як у старій базі (якщо вільні), тож
 * посилання між записами (зображення, сторінки рівнів, слайди) лишаються дійсними.
 * Стара база цілком не переноситься: беруться лише потрібні типи записів і поля.
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-mits-static-source.php';

/**
 * Імпортер.
 */
class Mits_Importer {

	/** Типи записів, що переносяться зі старої бази. */
	const POST_TYPES = array( 'page', 'news', 'teacher', 'mits_hero_slide', 'mits_activity', 'mits_advantage', 'mits_edu_level', 'mits_faq', 'wpcf7_contact_form' );

	/** Мета-поля, що переносяться (решта — службові поля старої теми, не потрібні). */
	const META_KEYS = array(
		'_thumbnail_id', '_wp_page_template',
		'_teacher_position', '_teacher_grade', '_teacher_description', '_teacher_photo_id', '_teacher_subjects', '_teacher_publications',
		'_mits_slide_line1', '_mits_slide_line2', '_mits_slide_line1_size', '_mits_slide_line2_size', '_mits_slide_subtitle', '_mits_slide_social', '_mits_slide_gradient',
		'_act_icon_url', '_act_icon_bg', '_act_desc', '_act_btn', '_act_url',
		'_adv_details', '_faq_answer',
		'_edu_level_page_id', '_edu_level_url', '_edu_level_description', '_edu_level_subjects_title', '_edu_level_subjects', '_edu_level_subject_alt', '_edu_level_tips', '_edu_level_tips_title', '_edu_level_algorithm_title', '_edu_level_algorithm', '_edu_level_steps',
		'_kafedra_spec_1', '_kafedra_spec_2', '_kafedra_spec_3', '_kafedra_history_url', '_kafedra_history_text',
		'_vstup_video_url', '_vstup_sub_hero_id', '_osvita_hero_id', '_osvita_slogan', '_osvita_desc', '_osvita_sub_hero_id',
		'_form', '_mail', '_mail_2', '_messages', '_locale', '_additional_settings',
	);

	/** Мета-поля з ID інших записів (перераховуються, якщо ID змінився). */
	const ID_META = array( '_thumbnail_id', '_teacher_photo_id', '_edu_level_page_id', '_vstup_sub_hero_id', '_osvita_hero_id', '_osvita_sub_hero_id', '_kafedra_hero_id', '_vstup_hero_id' );

	/** Налаштування mits_*, що переносяться. */
	const OPTIONS = array(
		'mits_why', 'mits_contacts', 'mits_intl_text', 'mits_intl_links', 'mits_intl_photo', 'mits_intl_stats', 'mits_intl_courses', 'mits_intl_hero_id',
		'mits_nauka_hero_id', 'mits_hero_custom_css', 'mits_osvita_docs_desc', 'mits_intl_courses_text', 'mits_osvita_docs_items', 'mits_osvita_docs_title',
		'mits_osvita_tech_items', 'mits_intl_courses_photo', 'mits_intl_courses_title', 'mits_intl_courses_slogan', 'mits_osvita_level_blocks',
	);

	/** @var array Дані content.json. */
	private $data;

	/** @var Mits_Static_Source */
	private $static;

	/** @var array Параметри. */
	private $opts;

	/** @var array Старий ID => новий ID. */
	private $map = array();

	/** @var array ID => запис content.json. */
	private $posts = array();

	/** @var array ID => [ключ => значення]. */
	private $meta = array();

	/** @var array ID запису => мова (uk|en) за даними Polylang у content.json. */
	private $lang = array();

	/** @var array Групи перекладів зі старої бази: [[uk => id, en => id], …]. */
	private $groups = array();

	/** @var array Український ID => англійський ID (після імпорту). */
	private $en = array();

	/** @var callable Вивід повідомлень. */
	private $log;

	/**
	 * @param string   $json   Шлях до content.json.
	 * @param string   $static Шлях до reference/static-site.
	 * @param array    $opts   Параметри (recipient).
	 * @param callable $log    Функція виводу.
	 */
	public function __construct( $json, $static, $opts, $log ) {
		$raw = json_decode( (string) file_get_contents( $json ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! is_array( $raw ) || empty( $raw['posts'] ) ) {
			throw new RuntimeException( 'Не вдалося прочитати ' . $json );
		}
		$this->data   = $raw;
		$this->static = new Mits_Static_Source( $static );
		$this->opts   = $opts;
		$this->log    = $log;

		foreach ( $raw['posts'] as $p ) {
			$this->posts[ (int) $p['ID'] ] = $p;
		}
		foreach ( $raw['postmeta'] as $m ) {
			$this->meta[ (int) $m['post_id'] ][ $m['meta_key'] ] = $m['meta_value'];
		}
		$tt = array();
		foreach ( $raw['terms'] as $t ) {
			$tt[ (int) $t['term_taxonomy_id'] ] = $t;
		}
		foreach ( $raw['term_relationships'] as $r ) {
			$t = isset( $tt[ (int) $r['term_taxonomy_id'] ] ) ? $tt[ (int) $r['term_taxonomy_id'] ] : null;
			if ( $t && 'language' === $t['taxonomy'] ) {
				$this->lang[ (int) $r['object_id'] ] = $t['slug'];
			}
		}
		foreach ( $tt as $t ) {
			if ( 'post_translations' === $t['taxonomy'] ) {
				$g = maybe_unserialize( $t['description'] );
				if ( is_array( $g ) ) {
					$this->groups[] = array_map( 'intval', $g );
				}
			}
		}
	}

	/**
	 * Повідомлення в консоль.
	 *
	 * @param string $msg Текст.
	 */
	private function log( $msg ) {
		call_user_func( $this->log, $msg );
	}

	/**
	 * Повний імпорт.
	 */
	public function run() {
		if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_set_post_language' ) ) {
			throw new RuntimeException( 'Потрібен активний плагін Polylang.' );
		}
		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			throw new RuntimeException( 'Потрібен активний плагін Contact Form 7.' );
		}
		kses_remove_filters(); // Вміст довірений (власний сайт); фільтри kses ламають блоки й iframe.
		wp_defer_term_counting( true );

		$this->remove_sample_content();
		$this->setup_site();
		$this->setup_languages();
		$this->import_media();
		$this->import_posts();
		$this->import_static_extras_uk();
		$this->import_english();
		$this->import_menus();
		$this->import_options();
		$this->finish();

		wp_defer_term_counting( false );
		kses_init_filters();
	}

	/* =====================================================================
	 * Підготовка
	 * ===================================================================== */

	/**
	 * Видаляє демонстраційний вміст свіжого WordPress і типову форму CF7,
	 * щоб звільнити ID і не лишати зайвого.
	 */
	private function remove_sample_content() {
		$removed = 0;
		// Записи типу «post» сайт не використовує (новини — окремий тип), тож «Привіт, світ!» тощо зайві.
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'lang' => '' ) ) as $p ) {
			wp_delete_post( $p->ID, true );
			++$removed;
		}
		// Сторінки, створені під час встановлення WordPress (зразок і політика конфіденційності):
		// не імпортовані й не пізніші за реєстрацію першого користувача.
		$admins  = get_users( array( 'orderby' => 'registered', 'order' => 'ASC', 'number' => 1, 'fields' => array( 'user_registered' ) ) );
		$install = $admins ? strtotime( $admins[0]->user_registered . ' UTC' ) + 120 : 0;
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'lang' => '' ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_mits_import', true ) && $install && strtotime( get_gmt_from_date( $p->post_date ) . ' UTC' ) <= $install ) {
				wp_delete_post( $p->ID, true );
				++$removed;
			}
		}
		// Типова форма, яку створює Contact Form 7 під час активації.
		foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_mits_import', true ) && preg_match( '/^(Contact form 1|Контактна форма 1)$/u', $p->post_title ) ) {
				wp_delete_post( $p->ID, true );
				++$removed;
			}
		}
		$this->log( 'Видалено демонстраційний вміст WordPress: ' . $removed . ' записів.' );
	}

	/**
	 * Загальні налаштування сайту.
	 */
	private function setup_site() {
		$o = $this->data['options'];
		update_option( 'blogname', $o['blogname'] );
		update_option( 'blogdescription', $o['blogdescription'] );
		update_option( 'permalink_structure', '/%postname%/' );
		update_option( 'date_format', 'd.m.Y' );
		update_option( 'time_format', 'H:i' );
		update_option( 'timezone_string', 'Europe/Kyiv' );
		update_option( 'start_of_week', 1 );
		update_option( 'WPLANG', 'uk' );
		// Коментарі: лише в новинах, кожен коментар — після схвалення, без пінгбеків.
		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );
		update_option( 'default_pingback_flag', 0 );
		update_option( 'comment_moderation', 1 );
		update_option( 'comment_previously_approved', 0 );
		update_option( 'require_name_email', 1 );
		update_option( 'show_avatars', 0 );
		update_option( 'users_can_register', 0 );
		update_option( 'default_role', 'subscriber' );
		// Розміри зображень старого сайту (імена файлів у uploads їм відповідають).
		update_option( 'thumbnail_size_w', 300 );
		update_option( 'thumbnail_size_h', 300 );
		update_option( 'thumbnail_crop', 0 );
		update_option( 'medium_size_w', 500 );
		update_option( 'medium_size_h', 500 );
		update_option( 'large_size_w', 1200 );
		update_option( 'large_size_h', 1200 );
		update_option( 'uploads_use_yearmonth_folders', 1 );
		update_option( 'blog_public', 1 );
		$this->log( 'Налаштування сайту записано.' );
	}

	/**
	 * Мови Polylang: українська за замовчуванням без префікса, англійська з /en/.
	 */
	private function setup_languages() {
		$model = PLL()->model;
		$have  = $model->get_languages_list( array( 'fields' => 'slug' ) );
		$langs = array(
			array( 'name' => 'Українська', 'slug' => 'uk', 'locale' => 'uk', 'rtl' => false, 'term_group' => 0, 'flag' => 'ua' ),
			array( 'name' => 'English', 'slug' => 'en', 'locale' => 'en_US', 'rtl' => false, 'term_group' => 1, 'flag' => 'us' ),
		);
		foreach ( $langs as $l ) {
			if ( in_array( $l['slug'], $have, true ) ) {
				continue;
			}
			$res = isset( $model->languages ) && is_object( $model->languages ) && method_exists( $model->languages, 'add' ) ? $model->languages->add( $l ) : $model->add_language( $l );
			if ( is_wp_error( $res ) ) {
				throw new RuntimeException( 'Polylang: ' . $res->get_error_message() );
			}
		}
		$options = PLL()->options;
		$set     = array(
			'default_lang'  => 'uk',
			'hide_default'  => true,
			'force_lang'    => 1,
			'rewrite'       => true,
			'browser'       => false,
			'redirect_lang' => false,
			'media_support' => false,
			// Між перекладами синхронізуються лише структура й зображення, тексти — окремо.
			'sync'          => array( 'taxonomies', 'post_parent', '_wp_page_template', 'menu_order', '_thumbnail_id' ),
			'post_types'    => array_keys( mits_post_type_definitions() ),
		);
		foreach ( $set as $k => $v ) {
			if ( is_object( $options ) && method_exists( $options, 'set' ) ) {
				$options->set( $k, $v );
			} else {
				$options[ $k ] = $v;
			}
		}
		if ( is_object( $options ) && method_exists( $options, 'save' ) ) {
			$options->save();
		}
		if ( method_exists( $model, 'clean_languages_cache' ) ) {
			$model->clean_languages_cache();
		} elseif ( isset( $model->languages ) && method_exists( $model->languages, 'clean_cache' ) ) {
			$model->languages->clean_cache();
		}
		$this->log( 'Мови Polylang: uk (за замовчуванням, без префікса), en (/en/).' );
	}

	/* =====================================================================
	 * Медіафайли
	 * ===================================================================== */

	/**
	 * Копіює файли з reference/static-site/wp-content/uploads і створює записи вкладень.
	 */
	private function import_media() {
		$src_root = $this->static->upload_path( '' );
		$uploads  = wp_get_upload_dir();
		$dst_root = trailingslashit( $uploads['basedir'] );
		$copied   = 0;

		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $src_root, FilesystemIterator::SKIP_DOTS ) );
		$files = array();
		foreach ( $it as $file ) {
			$rel = ltrim( str_replace( $src_root, '', $file->getPathname() ), '/' );
			if ( preg_match( '/\.(php\d?|phtml|phar|html?|js|htaccess)$/i', $rel ) ) {
				continue; // У завантаженнях можуть бути лише медіафайли й документи.
			}
			$files[ $rel ] = true;
			$dst = $dst_root . $rel;
			wp_mkdir_p( dirname( $dst ) );
			if ( ! file_exists( $dst ) || filesize( $dst ) !== $file->getSize() ) {
				copy( $file->getPathname(), $dst );
				++$copied;
			}
		}
		$this->log( "Скопійовано файлів у uploads: $copied (усього у статичній копії: " . count( $files ) . ').' );

		$covered = array();
		$count   = 0;
		foreach ( $this->posts as $id => $p ) {
			if ( 'attachment' !== $p['post_type'] ) {
				continue;
			}
			$m    = isset( $this->meta[ $id ] ) ? $this->meta[ $id ] : array();
			$file = isset( $m['_wp_attached_file'] ) ? $m['_wp_attached_file'] : '';
			if ( '' === $file ) {
				continue;
			}
			$meta = isset( $m['_wp_attachment_metadata'] ) ? maybe_unserialize( $m['_wp_attachment_metadata'] ) : array();
			$meta = is_array( $meta ) ? $meta : array();
			$dir  = dirname( $file );
			// У метаданих лишаються тільки ті розміри, файли яких справді є.
			if ( ! empty( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size => $info ) {
					$rel = ( '.' === $dir ? '' : $dir . '/' ) . $info['file'];
					if ( isset( $files[ $rel ] ) ) {
						$covered[ $rel ] = true;
					} else {
						unset( $meta['sizes'][ $size ] );
					}
				}
			}
			$has_original = isset( $files[ $file ] );
			if ( ! $has_original && empty( $meta['sizes'] ) ) {
				continue; // Файлу немає ні в якому розмірі — вкладення не потрібне.
			}
			$covered[ $file ] = true;
			$new_id = $this->insert_post( $id, array(
				'post_type'      => 'attachment',
				'post_title'     => $p['post_title'],
				'post_name'      => $this->slug( $p['post_name'] ),
				'post_content'   => $this->relativize( $p['post_content'] ),
				'post_excerpt'   => $p['post_excerpt'],
				'post_status'    => 'inherit',
				'post_mime_type' => $p['post_mime_type'],
				'post_date'      => substr( $p['post_date'], 0, 19 ),
				'guid'           => $uploads['baseurl'] . '/' . $file,
			) );
			update_post_meta( $new_id, '_wp_attached_file', $file );
			unset( $meta['image_meta']['keywords'] );
			update_post_meta( $new_id, '_wp_attachment_metadata', $meta );
			if ( ! empty( $m['_wp_attachment_context'] ) ) {
				update_post_meta( $new_id, '_wp_attachment_context', $m['_wp_attachment_context'] );
			}
			++$count;
		}

		// Файли, яких немає серед вкладень старої бази (афіші «Науки» тощо), — у медіатеку.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		foreach ( array_keys( $files ) as $rel ) {
			if ( isset( $covered[ $rel ] ) || preg_match( '/-\d+x\d+\.\w+$/', $rel ) || preg_match( '/^cropped-|\/cropped-/', $rel ) ) {
				continue;
			}
			$type = wp_check_filetype( $rel );
			if ( ! $type['type'] ) {
				continue;
			}
			$new_id = wp_insert_attachment( array(
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $rel ) ),
				'post_mime_type' => $type['type'],
				'post_status'    => 'inherit',
				'guid'           => $uploads['baseurl'] . '/' . $rel,
			), $dst_root . $rel, 0, true );
			if ( is_wp_error( $new_id ) ) {
				continue;
			}
			update_post_meta( $new_id, '_mits_import', 'static' );
			if ( 0 === strpos( $type['type'], 'image/' ) ) {
				wp_update_attachment_metadata( $new_id, wp_generate_attachment_metadata( $new_id, $dst_root . $rel ) );
			}
			++$count;
		}
		$this->log( "Вкладень у медіатеці: $count." );
	}

	/* =====================================================================
	 * Записи зі старої бази
	 * ===================================================================== */

	/**
	 * Створює запис зі збереженням старого ID, якщо він вільний.
	 *
	 * @param int   $old_id Старий ID.
	 * @param array $args   Аргументи wp_insert_post.
	 * @return int Новий ID.
	 */
	private function insert_post( $old_id, $args ) {
		if ( $old_id && ! get_post( $old_id ) ) {
			$args['import_id'] = $old_id;
		}
		$args = wp_slash( $args );
		$id   = 'attachment' === $args['post_type'] ? wp_insert_attachment( $args, false, 0, true ) : wp_insert_post( $args, true );
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( 'Не вдалося створити запис ' . $old_id . ': ' . $id->get_error_message() );
		}
		if ( $old_id ) {
			$this->map[ $old_id ] = (int) $id;
		}
		update_post_meta( $id, '_mits_import', $old_id ? $old_id : 'static' );
		return (int) $id;
	}

	/**
	 * Новий ID для старого.
	 *
	 * @param int $old Старий ID.
	 */
	private function id( $old ) {
		$old = (int) $old;
		return isset( $this->map[ $old ] ) ? $this->map[ $old ] : 0;
	}

	/**
	 * Записи (сторінки, новини, викладачі, блоки, форми) зі старої бази.
	 */
	private function import_posts() {
		$order   = array( 'page', 'mits_hero_slide', 'teacher', 'news', 'mits_activity', 'mits_advantage', 'mits_edu_level', 'mits_faq', 'wpcf7_contact_form' );
		$count   = 0;
		$created = array();
		foreach ( $order as $type ) {
			$list = array_filter( $this->posts, function ( $p ) use ( $type ) {
				return $p['post_type'] === $type && 'publish' === $p['post_status'];
			} );
			// Батьківські сторінки — раніше за дочірні.
			uasort( $list, function ( $a, $b ) {
				return (int) ( $a['post_parent'] > 0 ) - (int) ( $b['post_parent'] > 0 );
			} );
			foreach ( $list as $id => $p ) {
				$new = $this->insert_post( $id, array(
					'post_type'      => $type,
					'post_title'     => $p['post_title'],
					'post_name'      => $this->slug( $p['post_name'] ),
					'post_content'   => $this->relativize( $p['post_content'] ),
					'post_excerpt'   => $p['post_excerpt'],
					'post_status'    => 'publish',
					'post_date'      => substr( $p['post_date'], 0, 19 ),
					'post_modified'  => substr( $p['post_modified'], 0, 19 ),
					'menu_order'     => (int) $p['menu_order'],
					'post_parent'    => $p['post_parent'] ? $this->id( $p['post_parent'] ) : 0,
					'comment_status' => 'news' === $type ? 'open' : 'closed',
					'ping_status'    => 'closed',
				) );
				if ( 'wpcf7_contact_form' !== $type ) {
					pll_set_post_language( $new, isset( $this->lang[ $id ] ) ? $this->lang[ $id ] : 'uk' );
				}
				$created[ $id ] = $new;
				++$count;
			}
		}
		// Поля — після створення всіх записів, щоб посилання на інші записи (слайди, сторінки) знайшлися.
		foreach ( $created as $id => $new ) {
			$this->import_meta( $id, $new );
		}
		// Старі сторінки «Бакалавр», «Спеціаліст», «Магістр» розділу «Вступ».
		foreach ( array( 304, 305, 306 ) as $old ) {
			if ( $this->id( $old ) ) {
				update_post_meta( $this->id( $old ), '_mits_level_page', 1 );
			}
		}
		// Тестова «Новина №1» на старому сайті не мала мови й не показувалась у списках.
		if ( $this->id( 162 ) ) {
			update_post_meta( $this->id( 162 ), '_mits_hidden', '1' );
		}
		// Шаблон «Голосування» для сторінки /вступ/golosuvannia/ (як у статичній копії).
		if ( $this->id( 338 ) ) {
			update_post_meta( $this->id( 338 ), '_wp_page_template', 'template-mits-golosuvannia.php' );
		}
		// Перша сторінка вступу: слайд «Вступ», «Кафедра»: слайд «Мультимедійні та інтернет…».
		if ( $this->id( 12 ) ) {
			update_post_meta( $this->id( 12 ), '_vstup_hero_id', $this->id( 193 ) );
		}
		if ( $this->id( 9 ) ) {
			update_post_meta( $this->id( 9 ), '_kafedra_hero_id', $this->id( 192 ) );
		}
		$this->log( "Записів зі старої бази: $count." );
	}

	/**
	 * Мета-поля запису.
	 *
	 * @param int $old Старий ID.
	 * @param int $new Новий ID.
	 */
	private function import_meta( $old, $new ) {
		$m = isset( $this->meta[ $old ] ) ? $this->meta[ $old ] : array();
		foreach ( $m as $key => $value ) {
			if ( ! in_array( $key, self::META_KEYS, true ) ) {
				continue;
			}
			$value = maybe_unserialize( $value );
			if ( is_string( $value ) && is_serialized( $value ) ) {
				$value = maybe_unserialize( $value ); // Подвійна серіалізація у формі CF7.
			}
			if ( in_array( $key, self::ID_META, true ) ) {
				$value = $this->id( $value );
				if ( ! $value ) {
					continue;
				}
			}
			if ( '_edu_level_url' === $key && preg_match( '/page_id=(\d+)/', (string) $value, $mm ) ) {
				$value = '/?page_id=' . $this->id( $mm[1] );
			}
			if ( '_mail' === $key || '_mail_2' === $key ) {
				$value = $this->cf7_mail( $value );
			}
			if ( '_wp_page_template' === $key && ( 'default' === $value || '' === $value ) ) {
				continue;
			}
			$value = is_string( $value ) ? $this->relativize( $value ) : $value;
			if ( '' === $value || array() === $value ) {
				continue;
			}
			update_post_meta( $new, $key, wp_slash( $value ) );
		}
	}

	/**
	 * Налаштування листа CF7: одержувач не зберігається в коді й у базі старого сайту —
	 * його задають в адмінці (або параметром --recipient під час імпорту).
	 *
	 * @param mixed $mail Налаштування листа.
	 */
	private function cf7_mail( $mail ) {
		if ( ! is_array( $mail ) ) {
			return $mail;
		}
		if ( ! empty( $mail['active'] ) || isset( $mail['recipient'] ) ) {
			$mail['recipient'] = ! empty( $this->opts['recipient'] ) ? $this->opts['recipient'] : '[_site_admin_email]';
			$host              = wp_parse_url( home_url(), PHP_URL_HOST );
			$mail['sender']    = '[_site_title] <wordpress@' . ( $host ? $host : 'localhost' ) . '>';
		}
		return $mail;
	}

	/* =====================================================================
	 * Українські блоки, яких немає в базі (беруться зі статичної копії)
	 * ===================================================================== */

	/**
	 * Блок «Якісне… IT-навчання», заголовки секцій головної, логотип меню, сторінка «Наука».
	 */
	private function import_static_extras_uk() {
		$home = $this->static->page( '/' );
		if ( $home ) {
			$this->front_blocks( $home, '' );
			$logo = $home->attr( '.mits-dropdown__logo img', 'src' );
			if ( $logo ) {
				update_option( 'mits_dropdown_logo', $this->relativize( $logo ) );
			}
		}
		$nauka = $this->static->page( '/наука/' );
		if ( $nauka && $this->id( 18 ) ) {
			$this->nauka_meta( $nauka, $this->id( 18 ) );
		}
		$this->log( 'Українські блоки зі статичної копії: головна, «Наука».' );
	}

	/**
	 * Блоки головної з налаштувань (заголовки, «Якісне… IT-навчання»).
	 *
	 * @param Mits_Static_Page $page   Сторінка.
	 * @param string           $suffix '' або '_en'.
	 */
	private function front_blocks( $page, $suffix ) {
		$lines = array();
		foreach ( $page->all( '.mits-education__title span' ) as $span ) {
			$lines[] = $page->text( $span );
		}
		update_option( 'mits_front_edu_title' . $suffix, implode( "\n", $lines ) );
		update_option( 'mits_front_edu_text' . $suffix, $this->clean_html( $page->inner_html( $page->one( '.mits-education__card' ) ) ) );

		$adv = get_option( 'mits_advantages', array() );
		$adv = is_array( $adv ) ? $adv : array();
		$adv[ 'title' . $suffix ] = $page->text( '.mits-advantages .mits-section__title' );
		update_option( 'mits_advantages', $adv );

		$act = get_option( 'mits_activities', array() );
		$act = is_array( $act ) ? $act : array();
		$act[ 'title' . $suffix ]    = $page->text( '.mits-activities .mits-section__title' );
		$act[ 'cta_text' . $suffix ] = $page->text( '.mits-activities__cta a' );
		$act[ 'cta_url' . $suffix ]  = $this->relativize( $page->attr( '.mits-activities__cta a', 'href' ) );
		update_option( 'mits_activities', $act );

		$why = get_option( 'mits_why', array() );
		$why = is_array( $why ) ? $why : array();
		if ( '' !== $suffix ) {
			$why[ 'title' . $suffix ] = $page->text( '.mits-why .mits-section__title' );
			$why[ 'text' . $suffix ]  = $page->text( '.mits-why__text' );
			foreach ( $page->all( '.mits-stat' ) as $i => $stat ) {
				$why[ 'stat' . ( $i + 1 ) . '_label' . $suffix ] = $page->text( '.mits-stat__label', $stat );
			}
			update_option( 'mits_why', $why );
		}
	}

	/**
	 * Поля сторінки «Наука» з розмітки статичної копії.
	 *
	 * @param Mits_Static_Page $p  Сторінка.
	 * @param int              $id ID сторінки WordPress.
	 */
	private function nauka_meta( $p, $id ) {
		$set = function ( $key, $value ) use ( $id ) {
			if ( is_array( $value ) ) {
				$value = $value ? wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
			}
			if ( '' === $value ) {
				delete_post_meta( $id, '_nauka_' . $key );
			} else {
				update_post_meta( $id, '_nauka_' . $key, wp_slash( $value ) );
			}
		};
		$card = function ( $section ) use ( $p ) {
			$card  = $p->one( '.mits-nauka__card', $section );
			$text  = '';
			$links = array();
			if ( $card ) {
				foreach ( $card->childNodes as $child ) {
					if ( ! ( $child instanceof DOMElement ) ) {
						continue;
					}
					$a = $p->one( 'a.mits-nauka__link', $child );
					if ( 'p' === $child->nodeName && $a && trim( $child->textContent ) === trim( $a->textContent ) ) {
						$links[] = array( 'text' => $p->text( $a ), 'url' => $this->link_value( $a->getAttribute( 'href' ) ) );
					} else {
						$text .= $p->dom->saveHTML( $child ) . "\n";
					}
				}
			}
			return array( trim( $text ), $links );
		};
		$accordion = function ( $acc ) use ( $p ) {
			$items = array();
			if ( $acc ) {
				foreach ( $p->all( '.mits-adv-item', $acc ) as $item ) {
					$body    = $p->one( '.mits-adv-item__body-inner', $item );
					$items[] = array(
						'title' => $p->text( '.mits-adv-item__header span', $item ),
						'text'  => trim( $p->inner_html( $body ) ),
					);
				}
			}
			return $items;
		};

		$s = $p->one( 'section#ipst' );
		if ( $s ) {
			list( $text, $links ) = $card( $s );
			$set( 'ipst_title', $p->text( '.mits-nauka__title', $s ) );
			$set( 'ipst_lead', $p->text( '.mits-nauka__lead', $s ) );
			$set( 'ipst_text', $text );
			$set( 'ipst_links', $links );
			$posters = array();
			foreach ( $p->all( '.mits-nauka__poster img', $s ) as $img ) {
				$posters[] = array( 'img' => $this->relativize( $img->getAttribute( 'src' ) ), 'alt' => $img->getAttribute( 'alt' ) );
			}
			$set( 'ipst_posters', $posters );
		}
		$s = $p->one( 'section#microcad' );
		if ( $s ) {
			list( $text, $links ) = $card( $s );
			$set( 'microcad_title', $p->text( '.mits-nauka__title', $s ) );
			$set( 'microcad_text', $text );
			$set( 'microcad_links', $links );
			$stats = array();
			foreach ( $p->all( '.mits-nauka__stat', $s ) as $st ) {
				$stats[] = array( 'value' => $p->text( '.mits-nauka__stat-value', $st ), 'label' => $p->text( '.mits-nauka__stat-label', $st ) );
			}
			$set( 'microcad_stats', $stats );
			$set( 'microcad_dirs_title', $p->text( '.mits-nauka__subtitle', $s ) );
			$set( 'microcad_dirs', $accordion( $p->one( '.mits-nauka__accordion', $s ) ) );
			$more = array();
			foreach ( $p->all( 'p.mits-nauka__more a', $s ) as $a ) {
				$more[] = array( 'text' => $p->text( $a ), 'url' => $this->link_value( $a->getAttribute( 'href' ) ) );
			}
			$set( 'microcad_more', $more );
		}
		$s = $p->one( 'section#hurtky' );
		if ( $s ) {
			list( $text, $links ) = $card( $s );
			$set( 'clubs_title', $p->text( '.mits-nauka__title', $s ) );
			$set( 'clubs_text', $text );
			$set( 'clubs_links', $links );
			$set( 'clubs', $accordion( $p->one( '.mits-nauka__accordion', $s ) ) );
		}
		$s = $p->one( 'section#startups' );
		if ( $s ) {
			list( $text ) = $card( $s );
			$set( 'startups_title', $p->text( '.mits-nauka__title', $s ) );
			$set( 'startups_text', $text );
			$items = array();
			foreach ( $p->all( '.mits-nauka__item', $s ) as $it ) {
				$a       = $p->one( '.mits-nauka__item-title a', $it );
				$items[] = array(
					'img'   => $this->relativize( $p->attr( '.mits-nauka__item-img img', 'src', $it ) ),
					'title' => $p->text( '.mits-nauka__item-title', $it ),
					'url'   => $a ? $this->link_value( $a->getAttribute( 'href' ) ) : '',
					'text'  => $p->text( '.mits-nauka__item-text', $it ),
				);
			}
			$set( 'startups', $items );
		}
		$s = $p->one( 'section#konkursy' );
		if ( $s ) {
			list( $text ) = $card( $s );
			$set( 'contests_title', $p->text( '.mits-nauka__title', $s ) );
			$set( 'contests_text', $text );
			$set( 'contests_years_title', $p->text( '.mits-nauka__subtitle', $s ) );
			$years = array();
			foreach ( $p->all( '.mits-nauka__tab', $s ) as $tab ) {
				$panel = $p->one( '#' . $tab->getAttribute( 'aria-controls' ), $s );
				$items = array();
				$more  = $panel ? $p->one( '.mits-nauka__more a', $panel ) : null;
				if ( $panel ) {
					foreach ( $p->all( '.mits-nauka__results li', $panel ) as $li ) {
						$items[] = array( 'text' => trim( $p->inner_html( $li ) ) );
					}
				}
				$years[] = array(
					'label'     => $p->text( $tab ),
					'items'     => $items,
					'empty'     => $panel ? $p->text( '.mits-nauka__empty', $panel ) : '',
					'more_text' => $more ? $p->text( $more ) : '',
					'more_url'  => $more ? $this->link_value( $more->getAttribute( 'href' ) ) : '',
				);
			}
			$set( 'contests_years', $years );
		}
	}

	/**
	 * Значення поля-посилання: якорі лишаються, адреси сайту стають відносними.
	 *
	 * @param string $href Атрибут href.
	 */
	private function link_value( $href ) {
		$href = $this->relativize( (string) $href );
		if ( '' !== $href && '/' === $href[0] ) {
			$href = $this->encode_path( rawurldecode( $href ) );
		}
		return $href;
	}

	/* =====================================================================
	 * Англійська версія (зі статичної копії en/)
	 * ===================================================================== */

	/**
	 * Створює англійські переклади сторінок, новин, викладачів і блоків.
	 */
	private function import_english() {
		$this->en = array();
		// Переклади, що вже є у старій базі (ID зберігаються).
		foreach ( $this->groups as $g ) {
			if ( ! empty( $g['uk'] ) && ! empty( $g['en'] ) && $this->id( $g['uk'] ) && $this->id( $g['en'] ) ) {
				$this->en[ (int) $g['uk'] ] = $this->id( $g['en'] );
			}
		}

		$en_pages = $this->english_static_pages();
		$this->english_pages( $en_pages );
		$this->english_news( $en_pages );
		$this->english_teachers( $en_pages );
		$this->english_blocks();

		// Зв'язки перекладів.
		foreach ( $this->en as $uk => $en ) {
			if ( $this->id( $uk ) && $en ) {
				pll_set_post_language( $en, 'en' );
				pll_save_post_translations( array( 'uk' => $this->id( $uk ), 'en' => $en ) );
			}
		}
		// Англійські записи старої бази без пари в статичній копії (дублікати) не потрібні.
		foreach ( $this->lang as $old => $lang ) {
			$new = $this->id( $old );
			if ( 'en' === $lang && $new && ! in_array( $new, $this->en, true ) && 'wpcf7_contact_form' !== get_post_type( $new ) ) {
				wp_delete_post( $new, true );
			}
		}
		$this->log( 'Англійських перекладів: ' . count( $this->en ) . '.' );
	}

	/**
	 * Англійські сторінки статичної копії: український ID (з класу body) => сторінка.
	 *
	 * @return array<int, Mits_Static_Page>
	 */
	private function english_static_pages() {
		$out  = array();
		$root = $this->static->root . '/en';
		$it   = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $it as $file ) {
			if ( 'index.html' !== $file->getFilename() ) {
				continue;
			}
			$path = '/en/' . trim( str_replace( $root, '', dirname( $file->getPathname() ) ), '/' ) . '/';
			$path = str_replace( '//', '/', $path );
			$page = $this->static->page( $path );
			$body = $page ? $page->one( 'body' ) : null;
			if ( ! $body || ! preg_match( '/\b(?:page-id|postid)-(\d+)\b/', $body->getAttribute( 'class' ), $m ) ) {
				continue;
			}
			$out[ (int) $m[1] ][] = $page;
		}
		return $out;
	}

	/**
	 * Заголовок сторінки з &lt;title&gt; («Назва – MITS»).
	 *
	 * @param Mits_Static_Page $p Сторінка.
	 */
	private function static_title( $p ) {
		$t = html_entity_decode( $p->text( 'title' ), ENT_QUOTES, 'UTF-8' );
		return trim( preg_replace( '/\s+–\s+[^–]+$/u', '', $t ) );
	}

	/**
	 * Створює або оновлює англійський переклад.
	 *
	 * @param int   $uk_old Український ID у старій базі.
	 * @param array $args   Поля запису.
	 * @return int ID англійського запису.
	 */
	private function upsert_en( $uk_old, $args ) {
		$uk_new = $this->id( $uk_old );
		$args   = wp_parse_args( $args, array(
			'post_type'      => get_post_type( $uk_new ),
			'post_status'    => 'publish',
			'menu_order'     => (int) get_post_field( 'menu_order', $uk_new ),
			'comment_status' => get_post_field( 'comment_status', $uk_new ),
			'ping_status'    => 'closed',
		) );
		if ( ! isset( $this->en[ $uk_old ] ) && ! empty( $args['post_name'] ) ) {
			// Англійський запис старої бази з тим самим слагом, але без зв'язку перекладу.
			$same = get_posts( array(
				'post_type'      => $args['post_type'],
				'name'           => $args['post_name'],
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'lang'           => '',
			) );
			if ( $same && in_array( (int) $same[0], $this->map, true ) && ! in_array( (int) $same[0], $this->en, true ) && 'uk' !== pll_get_post_language( $same[0] ) ) {
				$this->en[ $uk_old ] = (int) $same[0];
			}
		}
		if ( isset( $this->en[ $uk_old ] ) && get_post( $this->en[ $uk_old ] ) ) {
			$args['ID'] = $this->en[ $uk_old ];
			$id         = wp_update_post( wp_slash( $args ), true );
		} else {
			$id = wp_insert_post( wp_slash( $args ), true );
			if ( ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_mits_import', 'en:' . $uk_old );
			}
		}
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( 'Англійський переклад ' . $uk_old . ': ' . $id->get_error_message() );
		}
		pll_set_post_language( $id, 'en' );
		$this->en[ $uk_old ] = (int) $id;
		// Мовонезалежні поля — як в українського запису.
		foreach ( array_merge( mits_language_neutral_meta(), array( '_thumbnail_id', '_wp_page_template', '_mits_level_page', '_vstup_sub_hero_id', '_osvita_hero_id', '_osvita_sub_hero_id', '_kafedra_hero_id', '_vstup_hero_id' ) ) as $key ) {
			$v = get_post_meta( $uk_new, $key, true );
			if ( '' !== $v && false !== $v ) {
				update_post_meta( $id, $key, wp_slash( $v ) );
			}
		}
		return (int) $id;
	}

	/**
	 * Англійські сторінки.
	 *
	 * @param array $en_pages Сторінки статичної копії за українським ID.
	 */
	private function english_pages( $en_pages ) {
		// Батьківські раніше за дочірні.
		$pages = array_filter( array_keys( $en_pages ), function ( $id ) {
			return isset( $this->posts[ $id ] ) && 'page' === $this->posts[ $id ]['post_type'];
		} );
		usort( $pages, function ( $a, $b ) {
			return (int) ( $this->posts[ $a ]['post_parent'] > 0 ) - (int) ( $this->posts[ $b ]['post_parent'] > 0 );
		} );
		foreach ( $pages as $uk ) {
			$p      = $en_pages[ $uk ][0];
			$slug   = basename( rtrim( $p->path, '/' ) );
			$parent = (int) $this->posts[ $uk ]['post_parent'];
			$tpl    = get_page_template_slug( $this->id( $uk ) );
			$args   = array(
				'post_type'    => 'page',
				'post_title'   => 2 === $uk ? 'Home' : $this->static_title( $p ),
				'post_name'    => $slug,
				'post_parent'  => $parent && isset( $this->en[ $parent ] ) ? $this->en[ $parent ] : 0,
				'post_content' => $this->english_page_content( $uk, $p, $tpl ),
			);
			$id = $this->upsert_en( $uk, $args );

			if ( 'template-mits-kafedra.php' === $tpl ) {
				foreach ( $p->all( '.mits-kafedra-spec-tile p' ) as $i => $tile ) {
					update_post_meta( $id, '_kafedra_spec_' . ( $i + 1 ), $p->text( $tile ) );
				}
				$link = $p->text( '.mits-kafedra-about__link a' );
				if ( $link ) {
					update_post_meta( $id, '_kafedra_history_text', $link );
				}
			}
			if ( 'template-mits-osvita.php' === $tpl ) {
				update_post_meta( $id, '_osvita_desc', wp_slash( $this->clean_html( $p->inner_html( $p->one( '.mits-osvita-desc-card__body' ) ) ) ) );
				update_post_meta( $id, '_osvita_slogan', wp_slash( $p->text( '.mits-osvita-lead__slogan' ) ) );
			}
			if ( 'template-mits-nauka.php' === $tpl ) {
				$this->nauka_meta( $p, $id );
			}
		}
	}

	/**
	 * Вміст англійської сторінки (у блоках редактора).
	 *
	 * @param int              $uk  Український ID.
	 * @param Mits_Static_Page $p   Сторінка.
	 * @param string           $tpl Шаблон.
	 */
	private function english_page_content( $uk, $p, $tpl ) {
		if ( 24 === $uk ) {
			return $this->relativize( (string) get_post_field( 'post_content', $this->id( 24 ) ) ); // Контакти: ті самі шорткоди.
		}
		$selectors = array(
			'template-mits-kafedra.php'     => '.mits-kafedra-about__box',
			'template-mits-vstup.php'       => '.mits-vstup-levels__desc',
			'template-mits-vstup-sub.php'   => '.mits-vstup-sub-content__body',
			'template-mits-osvita-sub.php'  => '.mits-vstup-sub-content__body',
			'template-mits-golosuvannia.php' => '.mits-vote-page__msg',
		);
		$sel = isset( $selectors[ $tpl ] ) ? $selectors[ $tpl ] : '.the_content_wrapper';
		if ( in_array( $tpl, array( 'template-mits-osvita.php', 'template-mits-nauka.php', 'template-mits-international.php' ), true ) || 2 === $uk ) {
			return '';
		}
		$node = $p->one( $sel );
		if ( ! $node && 'template-mits-golosuvannia.php' === $tpl ) {
			$node = $p->one( '.mits-vote-page .mits-container' );
		}
		return $node ? $this->html_to_blocks( $p, $node ) : '';
	}

	/**
	 * Англійські новини.
	 *
	 * @param array $en_pages Сторінки статичної копії за українським ID.
	 */
	private function english_news( $en_pages ) {
		// Анонси англійських новин — такі, як у статичній копії (переклад українського анонса).
		$excerpts = array();
		foreach ( array( '/en/home-en/', '/en/admission/', '/en/research/', '/en/education/' ) as $path ) {
			$page = $this->static->page( $path );
			if ( ! $page ) {
				continue;
			}
			foreach ( $page->all( '.mits-news__item' ) as $item ) {
				$href = $page->attr( '.mits-news__title a', 'href', $item );
				$np   = $href ? $this->static->page( $href ) : null;
				$body = $np ? $np->one( 'body' ) : null;
				if ( $body && preg_match( '/\bpostid-(\d+)\b/', $body->getAttribute( 'class' ), $m ) ) {
					$excerpts[ (int) $m[1] ] = $page->text( '.mits-news__excerpt', $item );
				}
			}
		}
		foreach ( $en_pages as $uk => $list ) {
			if ( ! isset( $this->posts[ $uk ] ) || 'news' !== $this->posts[ $uk ]['post_type'] ) {
				continue;
			}
			$p    = $list[0];
			$body = $p->one( '.mits-news-single__body .the_content_wrapper' );
			$date = $p->attr( 'time.mits-news-single__date', 'datetime' );
			$en_id = $this->upsert_en( $uk, array(
				'post_type'    => 'news',
				'post_title'   => $p->text( 'h1.mits-news-single__title' ),
				'post_name'    => basename( rtrim( $p->path, '/' ) ),
				'post_content' => $body ? $this->html_to_blocks( $p, $body ) : '',
				'post_date'    => $date ? get_date_from_gmt( gmdate( 'Y-m-d H:i:s', strtotime( $date ) ) ) : get_post_field( 'post_date', $this->id( $uk ) ),
				'post_excerpt' => isset( $excerpts[ $uk ] ) ? $excerpts[ $uk ] : '',
			) );
			if ( get_post_meta( $this->id( $uk ), '_mits_hidden', true ) ) {
				update_post_meta( $en_id, '_mits_hidden', '1' );
			}
		}
	}

	/**
	 * Англійські сторінки викладачів (посилання «Наукові праці» — з архіву).
	 *
	 * @param array $en_pages Сторінки статичної копії за українським ID.
	 */
	private function english_teachers( $en_pages ) {
		foreach ( $en_pages as $uk => $list ) {
			if ( ! isset( $this->posts[ $uk ] ) || 'teacher' !== $this->posts[ $uk ]['post_type'] ) {
				continue;
			}
			$p        = $list[0];
			$subjects = array();
			foreach ( $p->all( '.mits-teacher-single__subjects li' ) as $li ) {
				$subjects[] = $p->text( $li );
			}
			$id = $this->upsert_en( $uk, array(
				'post_type'  => 'teacher',
				'post_title' => $p->text( 'h1.mits-teacher-single__name' ),
				'post_name'  => basename( rtrim( $p->path, '/' ) ),
			) );
			// Без обрізання пробілів по краях: у статичній копії вони є й впливають на перенесення рядків.
			foreach ( array( '_teacher_position' => '.mits-teacher-single__position', '_teacher_grade' => '.mits-teacher-single__grade' ) as $key => $sel ) {
				$node = $p->one( $sel );
				update_post_meta( $id, $key, wp_slash( $node ? str_replace( "\xc2\xa0", ' ', preg_replace( '/\s*\R\s*/u', ' ', $node->textContent ) ) : '' ) );
			}
			update_post_meta( $id, '_teacher_subjects', $subjects );
			$desc = $p->one( '.mits-teacher-single__description-text' );
			if ( $desc ) {
				update_post_meta( $id, '_teacher_description', wp_slash( trim( wp_strip_all_tags( $p->inner_html( $desc ) ) ) ) );
			}
		}
	}

	/**
	 * Англійські слайди, картки «Діяльність», переваги, FAQ, освітні рівні.
	 */
	private function english_blocks() {
		$home  = $this->static->page( '/en/home-en/' );
		$vstup = $this->static->page( '/en/admission/' );
		if ( ! $home || ! $vstup ) {
			return;
		}

		// Слайди: у тому ж порядку, що й українські на головній.
		$uk_slides = $this->uk_ids( 'mits_hero_slide' );
		foreach ( $home->all( '.mits-hero__slide' ) as $i => $slide ) {
			if ( ! isset( $uk_slides[ $i ] ) ) {
				break;
			}
			$lines = $home->all( '.mits-hero__line', $slide );
			$img   = $home->one( '.mits-hero__image img', $slide );
			$id    = $this->upsert_en( $uk_slides[ $i ], array(
				'post_type'  => 'mits_hero_slide',
				'post_title' => $img ? html_entity_decode( $img->getAttribute( 'alt' ), ENT_QUOTES, 'UTF-8' ) : $home->text( $lines[0] ),
			) );
			foreach ( array( 1, 2 ) as $n ) {
				$line = isset( $lines[ $n - 1 ] ) ? $lines[ $n - 1 ] : null;
				update_post_meta( $id, '_mits_slide_line' . $n, wp_slash( $line ? $home->text( $line ) : '' ) );
				$size = $line && preg_match( '/font-size:\s*(\d+)px/', $line->getAttribute( 'style' ), $m ) ? (int) $m[1] : 0;
				if ( $size ) {
					update_post_meta( $id, '_mits_slide_line' . $n . '_size', $size );
				} else {
					delete_post_meta( $id, '_mits_slide_line' . $n . '_size' );
				}
			}
			$class = preg_replace( '/\b(mits-hero__slide|is-active)\b/', '', $slide->getAttribute( 'class' ) );
			update_post_meta( $id, '_mits_slide_gradient', trim( $class ) );
			if ( $home->one( '.mits-hero__social', $slide ) ) {
				update_post_meta( $id, '_mits_slide_social', '[mits_social_telegram][mits_social_instagram][mits_social_youtube][mits_social_tiktok]' );
			} else {
				delete_post_meta( $id, '_mits_slide_social' );
			}
			$sub = $home->text( '.mits-hero__subtitle', $slide );
			if ( $sub && 'Department of MULTIMEDIA AND INTERNET TECHNOLOGIES AND SYSTEMS, NTU KhPI' !== $sub ) {
				update_post_meta( $id, '_mits_slide_subtitle', wp_slash( $sub ) );
			} else {
				delete_post_meta( $id, '_mits_slide_subtitle' );
			}
		}

		// Картки «Діяльність».
		$uk_act = $this->uk_ids( 'mits_activity' );
		foreach ( $home->all( '.mits-activities .mits-card' ) as $i => $card ) {
			if ( ! isset( $uk_act[ $i ] ) ) {
				break;
			}
			$id = $this->upsert_en( $uk_act[ $i ], array(
				'post_type'  => 'mits_activity',
				'post_title' => $home->text( 'h3', $card ),
			) );
			update_post_meta( $id, '_act_desc', wp_slash( $home->text( 'p', $card ) ) );
			update_post_meta( $id, '_act_btn', wp_slash( $home->text( 'a.mits-btn', $card ) ) );
			update_post_meta( $id, '_act_url', wp_slash( $this->link_value( $home->attr( 'a.mits-btn', 'href', $card ) ) ) );
		}

		// Переваги.
		$uk_adv = $this->uk_ids( 'mits_advantage' );
		foreach ( $home->all( '.mits-advantages .mits-adv-item' ) as $i => $item ) {
			if ( ! isset( $uk_adv[ $i ] ) ) {
				break;
			}
			$id = $this->upsert_en( $uk_adv[ $i ], array(
				'post_type'  => 'mits_advantage',
				'post_title' => $home->text( '.mits-adv-item__header span', $item ),
			) );
			update_post_meta( $id, '_adv_details', wp_slash( $this->accordion_text( $home, $item ) ) );
		}

		// FAQ.
		$uk_faq = $this->uk_ids( 'mits_faq' );
		foreach ( $vstup->all( '.mits-vstup-faq .mits-adv-item' ) as $i => $item ) {
			if ( ! isset( $uk_faq[ $i ] ) ) {
				break;
			}
			$id = $this->upsert_en( $uk_faq[ $i ], array(
				'post_type'  => 'mits_faq',
				'post_title' => $vstup->text( '.mits-adv-item__header span', $item ),
			) );
			update_post_meta( $id, '_faq_answer', wp_slash( $this->accordion_text( $vstup, $item ) ) );
		}

		// Освітні рівні: кнопка на «Admission» + дані зі сторінки рівня.
		$uk_lvl = $this->uk_ids( 'mits_edu_level' );
		foreach ( $vstup->all( '.mits-vstup-levels__buttons a' ) as $i => $a ) {
			if ( ! isset( $uk_lvl[ $i ] ) ) {
				break;
			}
			$uk_page = (int) get_post_meta( $this->id( $uk_lvl[ $i ] ), '_edu_level_page_id', true );
			$uk_old  = array_search( $uk_page, $this->map, true );
			$en_page = false !== $uk_old && isset( $this->en[ $uk_old ] ) ? $this->en[ $uk_old ] : 0;
			$id      = $this->upsert_en( $uk_lvl[ $i ], array(
				'post_type'  => 'mits_edu_level',
				'post_title' => $vstup->text( $a ),
			) );
			if ( $en_page ) {
				update_post_meta( $id, '_edu_level_page_id', $en_page );
			}
			$lp = $this->static->page( $a->getAttribute( 'href' ) );
			if ( ! $lp ) {
				continue;
			}
			$list = function ( $sel ) use ( $lp ) {
				$out = array();
				foreach ( $lp->all( $sel ) as $n ) {
					$out[] = $lp->text( $n );
				}
				return $out ? wp_json_encode( $out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
			};
			$algo = array();
			foreach ( $lp->all( '.mits-algo__item-body' ) as $n ) {
				$algo[] = $this->clean_html( $lp->inner_html( $n ) );
			}
			$fields = array(
				'_edu_level_steps'           => $list( '.mits-steps__item' ),
				'_edu_level_description'     => $this->clean_html( $lp->inner_html( $lp->one( '.mits-vstup-sub-desc .mits-container' ) ) ),
				'_edu_level_subjects_title'  => $lp->text( '.mits-subjects__label' ),
				'_edu_level_subjects'        => $list( '.mits-subjects__item' ),
				'_edu_level_subject_alt'     => $lp->text( '.mits-subjects__note' ),
				'_edu_level_algorithm_title' => $lp->text( '.mits-algo__title' ),
				'_edu_level_algorithm'       => $algo ? wp_json_encode( $algo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '',
				'_edu_level_tips_title'      => $lp->text( '.mits-tips__title' ),
				'_edu_level_tips'            => $list( '.mits-tips__item span' ),
			);
			foreach ( $fields as $k => $v ) {
				if ( '' === $v ) {
					delete_post_meta( $id, $k );
				} else {
					update_post_meta( $id, $k, wp_slash( $v ) );
				}
			}
		}
	}

	/**
	 * Українські записи типу в порядку виводу на сайті.
	 *
	 * @param string $type Тип запису.
	 * @return int[] Старі ID.
	 */
	private function uk_ids( $type ) {
		$ids = get_posts( array(
			'post_type'      => $type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'fields'         => 'ids',
			'lang'           => 'uk',
		) );
		$flip = array_flip( $this->map );
		return array_values( array_filter( array_map( function ( $id ) use ( $flip ) {
			return isset( $flip[ $id ] ) ? (int) $flip[ $id ] : 0;
		}, $ids ) ) );
	}

	/**
	 * Текст пункту акордеона (переноси рядків зберігаються).
	 *
	 * @param Mits_Static_Page $p    Сторінка.
	 * @param DOMNode          $item Пункт.
	 */
	private function accordion_text( $p, $item ) {
		$body = $p->one( '.mits-adv-item__body-inner', $item );
		return $body ? trim( $p->inner_html( $body ) ) : '';
	}

	/* =====================================================================
	 * Меню
	 * ===================================================================== */

	/**
	 * Меню української версії зі старої бази та англійське — його дзеркало.
	 */
	private function import_menus() {
		$items = array_filter( $this->posts, function ( $p ) {
			return 'nav_menu_item' === $p['post_type'];
		} );
		$uk_items = array();
		foreach ( $items as $id => $p ) {
			if ( 'main_menu_mits_ua' === $this->menu_of( $id ) ) {
				$uk_items[ $id ] = $p;
			}
		}
		uasort( $uk_items, function ( $a, $b ) {
			return (int) $a['menu_order'] - (int) $b['menu_order'];
		} );

		$menus = array();
		foreach ( array( 'uk' => 'main_menu_mits_ua', 'en' => 'main_menu_mits_en' ) as $lang => $name ) {
			$existing = wp_get_nav_menu_object( $name );
			if ( $existing ) {
				wp_delete_nav_menu( $existing->term_id );
			}
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				throw new RuntimeException( 'Меню: ' . $menu_id->get_error_message() );
			}
			$menus[ $lang ] = $menu_id;
			$created        = array();
			foreach ( $uk_items as $old => $p ) {
				$m       = $this->meta[ $old ];
				$parent  = (int) $m['_menu_item_menu_item_parent'];
				$classes = maybe_unserialize( $m['_menu_item_classes'] );
				$args    = array(
					'menu-item-title'     => 'en' === $lang && 'post_type_archive' === $m['_menu_item_type'] ? 'Teachers' : $p['post_title'],
					'menu-item-type'      => $m['_menu_item_type'],
					'menu-item-object'    => $m['_menu_item_object'],
					'menu-item-parent-id' => $parent && isset( $created[ $parent ] ) ? $created[ $parent ] : 0,
					'menu-item-position'  => (int) $p['menu_order'],
					'menu-item-classes'   => is_array( $classes ) ? implode( ' ', array_filter( $classes ) ) : '',
					'menu-item-status'    => 'publish',
				);
				if ( 'post_type' === $m['_menu_item_type'] ) {
					$obj = (int) $m['_menu_item_object_id'];
					$args['menu-item-object-id'] = 'en' === $lang ? ( isset( $this->en[ $obj ] ) ? $this->en[ $obj ] : 0 ) : $this->id( $obj );
					if ( ! $args['menu-item-object-id'] ) {
						continue;
					}
				}
				$created[ $old ] = wp_update_nav_menu_item( $menu_id, 0, $args );
			}
		}

		// Розташування меню для кожної мови.
		$locations                 = get_theme_mod( 'nav_menu_locations', array() );
		$locations['mits-primary'] = $menus['uk'];
		set_theme_mod( 'nav_menu_locations', $locations );
		$nav = PLL()->options->get( 'nav_menus' );
		$nav = is_array( $nav ) ? $nav : array();
		$nav[ get_stylesheet() ]['mits-primary'] = $menus;
		PLL()->options->set( 'nav_menus', $nav );
		PLL()->options->save();
		$this->log( 'Меню: українське й англійське, розташування «Головна навігація».' );
	}

	/**
	 * Назва меню, до якого належить пункт у старій базі.
	 *
	 * @param int $item_id ID пункту.
	 */
	private function menu_of( $item_id ) {
		$tt = array();
		foreach ( $this->data['terms'] as $t ) {
			if ( 'nav_menu' === $t['taxonomy'] ) {
				$tt[ (int) $t['term_taxonomy_id'] ] = $t['slug'];
			}
		}
		foreach ( $this->data['term_relationships'] as $r ) {
			if ( (int) $r['object_id'] === (int) $item_id && isset( $tt[ (int) $r['term_taxonomy_id'] ] ) ) {
				return $tt[ (int) $r['term_taxonomy_id'] ];
			}
		}
		return '';
	}

	/* =====================================================================
	 * Налаштування mits_*
	 * ===================================================================== */

	/**
	 * Налаштування блоків (українські — зі старої бази, англійські — зі статичної копії).
	 */
	private function import_options() {
		$o = $this->data['options'];
		foreach ( self::OPTIONS as $name ) {
			if ( ! isset( $o[ $name ] ) ) {
				continue;
			}
			$value = maybe_unserialize( $o[ $name ] );
			if ( in_array( $name, array( 'mits_intl_hero_id', 'mits_nauka_hero_id' ), true ) ) {
				$value = $this->id( $value );
			}
			$value = $this->relativize_deep( $value );
			if ( is_string( $value ) && '' !== $value && '[' === $value[0] ) {
				$value = $this->normalize_json( $value );
			}
			update_option( $name, $value );
		}
		// Сторінка «Голосування» в картках рівнів веде одразу на /вступ/голосування/ (без переадресації).
		$blocks = mits_json_decode( get_option( 'mits_osvita_level_blocks' ) );
		foreach ( $blocks as &$b ) {
			if ( isset( $b['vote_url'] ) && false !== strpos( rawurldecode( $b['vote_url'] ), 'голосування' ) && $this->id( 349 ) ) {
				$b['vote_url'] = wp_make_link_relative( get_permalink( $this->id( 349 ) ) );
			}
		}
		unset( $b );
		update_option( 'mits_osvita_level_blocks', wp_json_encode( $blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );

		// Англійські варіанти.
		$home = $this->static->page( '/en/home-en/' );
		if ( $home ) {
			$this->front_blocks( $home, '_en' );
		}
		$contacts    = get_option( 'mits_contacts', array() );
		$en_contacts = $this->static->page( '/en/contacts/' );
		if ( is_array( $contacts ) && $en_contacts ) {
			// Адреса англійською — як у статичній копії (перший текстовий рядок блоку контактів).
			foreach ( $en_contacts->all( '.mits-contacts__item' ) as $item ) {
				foreach ( $item->childNodes as $child ) {
					if ( $child instanceof DOMElement && 'span' === $child->nodeName && false === strpos( $child->getAttribute( 'class' ), 'icon' ) && ! $en_contacts->one( 'a', $child ) ) {
						$contacts['mits_address_en'] = $en_contacts->text( $child );
						break 2;
					}
				}
			}
		}
		if ( is_array( $contacts ) ) {
			foreach ( $contacts as $k => $v ) {
				if ( '_en' === substr( $k, -3 ) && '' === $v ) {
					unset( $contacts[ $k ] ); // Порожні англійські дублікати телефонів не потрібні.
				}
			}
			update_option( 'mits_contacts', $contacts );
		}

		$edu = $this->static->page( '/en/education/' );
		if ( $edu ) {
			update_option( 'mits_osvita_docs_title_en', $edu->text( '.mits-osvita-docs__title' ) );
			update_option( 'mits_osvita_docs_desc_en', $this->clean_html( $edu->inner_html( $edu->one( '.mits-osvita-docs__card' ) ) ) );
			$docs = array();
			foreach ( $edu->all( '.mits-osvita-docs__item' ) as $li ) {
				$docs[] = array( 'url' => $this->relativize( $edu->attr( 'a', 'href', $li ) ), 'name' => $edu->text( $li ) );
			}
			update_option( 'mits_osvita_docs_items_en', wp_json_encode( $docs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			$blocks = array();
			foreach ( $edu->all( '.mits-level-card' ) as $card ) {
				$title = $edu->one( '.mits-level-card__title', $card );
				$ldocs = array();
				foreach ( $edu->all( '.mits-level-card__docs li', $card ) as $li ) {
					$ldocs[] = array( 'name' => $edu->text( $li ), 'url' => $this->relativize( $edu->attr( 'a', 'href', $li ) ) );
				}
				$color    = preg_match( '/color:\s*(#[0-9a-f]{3,6})/i', $title ? $title->getAttribute( 'style' ) : '', $m ) ? strtolower( $m[1] ) : '';
				$vote     = $this->link_value( $edu->attr( '.mits-level-card__vote-btn', 'href', $card ) );
				$blocks[] = array(
					'title'    => $edu->text( $title ),
					'color'    => $color,
					'vote_url' => $vote,
					'schedule' => array( 'name' => '', 'url' => '' ),
					'exams'    => array( 'name' => '', 'url' => '' ),
					'docs'     => $ldocs,
				);
			}
			update_option( 'mits_osvita_level_blocks_en', wp_json_encode( $blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}

		$intl = $this->static->page( '/en/international/' );
		if ( $intl ) {
			$text = $intl->one( '.mits-intl-about__text' );
			$own  = '';
			if ( $text ) {
				foreach ( $text->childNodes as $child ) {
					if ( $child instanceof DOMElement && 'ul' === $child->nodeName ) {
						continue;
					}
					$own .= $intl->dom->saveHTML( $child );
				}
			}
			update_option( 'mits_intl_text_en', trim( $own ) );
			$links = array();
			foreach ( $intl->all( '.mits-intl-about__links a' ) as $a ) {
				$name    = $intl->text( $a );
				$links[] = array( 'name' => $name === $a->getAttribute( 'href' ) ? '' : $name, 'url' => $a->getAttribute( 'href' ) );
			}
			update_option( 'mits_intl_links_en', wp_json_encode( $links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			$stats = array();
			foreach ( $intl->all( '.mits-intl-stat' ) as $st ) {
				$stats[] = array( 'label' => $intl->text( '.mits-intl-stat__label', $st ), 'value' => $intl->text( '.mits-intl-stat__value', $st ) );
			}
			update_option( 'mits_intl_stats_en', wp_json_encode( $stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			update_option( 'mits_intl_courses_text_en', trim( $intl->inner_html( $intl->one( '.mits-intl-courses__intro-text' ) ) ) );
			update_option( 'mits_intl_courses_slogan_en', $intl->text( '.mits-intl-courses__slogan' ) );
			update_option( 'mits_intl_courses_title_en', $intl->text( '.mits-intl-courses .mits-section__title' ) );
			$courses = array();
			foreach ( $intl->all( '#mits-intl-courses-acc .mits-adv-item' ) as $item ) {
				$courses[] = array( 'name' => $intl->text( '.mits-adv-item__header span', $item ), 'desc' => $this->accordion_text( $intl, $item ) );
			}
			update_option( 'mits_intl_courses_en', wp_json_encode( $courses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}

		// Форми CF7 за мовою. Збереження через CF7 перебудовує копію налаштувань у post_content
		// (там лишалися дані старого сайту, зокрема адреса відправника).
		foreach ( array( 124 => 'uk', 159 => 'en' ) as $old => $lang ) {
			if ( $this->id( $old ) ) {
				update_option( 'mits_cf7_form_' . $lang, $this->id( $old ) );
				$this->cf7_texts_from_static( $this->id( $old ), 'en' === $lang ? '/en/contacts/' : '/контакти/' );
				$form = function_exists( 'wpcf7_contact_form' ) ? wpcf7_contact_form( $this->id( $old ) ) : null;
				if ( $form ) {
					$form->save();
				}
			}
		}
		update_option( 'mits_contacts_page_id', $this->id( 24 ) );
		$this->log( 'Налаштування блоків mits_* (uk + en).' );
	}

	/**
	 * Підписи полів і кнопки форми — як у статичній копії.
	 *
	 * @param int    $form_id ID форми CF7.
	 * @param string $path    Сторінка статичної копії з формою.
	 */
	private function cf7_texts_from_static( $form_id, $path ) {
		$page = $this->static->page( $path );
		$tpl  = (string) get_post_meta( $form_id, '_form', true );
		if ( ! $page || '' === $tpl ) {
			return;
		}
		foreach ( $page->all( 'form.wpcf7-form input' ) as $el ) {
			$name = $el->getAttribute( 'name' );
			$ph   = $el->getAttribute( 'placeholder' );
			if ( $name && '' !== $ph ) {
				$tpl = preg_replace( '/(\[[a-z*]+\s+' . preg_quote( $name, '/' ) . '\b[^\]]*placeholder\s+")[^"]*(")/u', '${1}' . str_replace( '$', '\$', $ph ) . '${2}', $tpl );
			}
		}
		foreach ( $page->all( 'form.wpcf7-form textarea' ) as $el ) {
			$name = $el->getAttribute( 'name' );
			$ph   = $el->getAttribute( 'placeholder' );
			if ( $name && '' !== $ph ) {
				$tpl = preg_replace( '/(\[textarea\*?\s+' . preg_quote( $name, '/' ) . '\b[^\]]*placeholder\s+")[^"]*(")/u', '${1}' . str_replace( '$', '\$', $ph ) . '${2}', $tpl );
			}
		}
		$submit = $page->attr( 'form.wpcf7-form input.wpcf7-submit', 'value' );
		if ( '' !== $submit ) {
			$tpl = preg_replace( '/\[submit\s+"[^"]*"\]/u', '[submit "' . str_replace( '$', '\$', $submit ) . '"]', $tpl );
		}
		update_post_meta( $form_id, '_form', wp_slash( $tpl ) );
	}

	/**
	 * Нормалізує JSON (екранування як у WordPress, без \/ і \uXXXX).
	 *
	 * @param string $json JSON.
	 */
	private function normalize_json( $json ) {
		$v = json_decode( $json, true );
		return is_array( $v ) ? wp_json_encode( $v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : $json;
	}

	/* =====================================================================
	 * Завершення
	 * ===================================================================== */

	/**
	 * Головна сторінка, логотип, рядки Polylang, правила адрес.
	 */
	private function finish() {
		$o = $this->data['options'];
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $this->id( $o['page_on_front'] ) );
		update_option( 'page_for_posts', 0 );
		if ( $this->id( $o['site_icon'] ) ) {
			update_option( 'site_icon', $this->id( $o['site_icon'] ) );
		}
		$mods = maybe_unserialize( $o['theme_mods_betheme'] );
		if ( is_array( $mods ) && ! empty( $mods['custom_logo'] ) && $this->id( $mods['custom_logo'] ) ) {
			set_theme_mod( 'custom_logo', $this->id( $mods['custom_logo'] ) );
		}

		// Назва сайту англійською (Polylang → Переклади рядків).
		if ( class_exists( 'PLL_MO' ) ) {
			$en = PLL()->model->get_language( 'en' );
			if ( $en ) {
				$mo = new PLL_MO();
				$mo->import_from_db( $en );
				$mo->add_entry( $mo->make_entry( get_option( 'blogname' ), 'MITS' ) );
				$mo->add_entry( $mo->make_entry( get_option( 'blogdescription' ), 'MITS' ) );
				$mo->export_to_db( $en );
			}
		}

		mits_protect_uploads_dir();
		update_option( 'mits_import_done', gmdate( 'c' ) );
		flush_rewrite_rules( false );
		$this->log( 'Готово: головна сторінка, логотип, назва англійською, правила адрес.' );
	}

	/* =====================================================================
	 * Допоміжне
	 * ===================================================================== */

	/**
	 * Слаг для WordPress: кирилиця в %-кодуванні (у старій базі частина слагів
	 * була записана кирилицею без кодування — такі сторінки віддавали 404).
	 *
	 * @param string $slug Слаг зі старої бази.
	 */
	private function slug( $slug ) {
		$decoded = rawurldecode( (string) $slug );
		return sanitize_title( $decoded );
	}

	/**
	 * %-кодування кожного сегмента шляху (як WordPress кодує слаги).
	 *
	 * @param string $path Шлях.
	 */
	private function encode_path( $path ) {
		$parts = explode( '/', $path );
		return implode( '/', array_map( function ( $s ) {
			return '' === $s ? '' : strtolower( rawurlencode( $s ) );
		}, $parts ) );
	}

	/**
	 * Адреси старого сайту в тексті — відносні.
	 *
	 * @param string $text Текст.
	 */
	private function relativize( $text ) {
		if ( ! is_string( $text ) || '' === $text ) {
			return $text;
		}
		// JSON (списки в полях і налаштуваннях): адреси там екрановані «https:\/\/…».
		$trim = ltrim( $text );
		if ( '' !== $trim && ( '[' === $trim[0] || '{' === $trim[0] ) ) {
			$decoded = json_decode( $text, true );
			if ( is_array( $decoded ) ) {
				return wp_json_encode( $this->relativize_deep( $decoded ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			}
		}
		return preg_replace( '#https?://(?:dev\.)?mits-khpi\.kh\.ua(?=/|"|\'|\s|$)|https?://localhost(?::\d+)?(?=/)#i', '', $text );
	}

	/**
	 * Те саме для масивів.
	 *
	 * @param mixed $value Значення.
	 */
	private function relativize_deep( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( $this, 'relativize_deep' ), $value );
		}
		return is_string( $value ) ? $this->relativize( $value ) : $value;
	}

	/**
	 * HTML фрагмента без відступів-пробілів між тегами.
	 *
	 * @param string $html HTML.
	 */
	private function clean_html( $html ) {
		$html = $this->relativize( trim( (string) $html ) );
		return preg_replace( "/>\s*\n\s*</", ">\n<", $html );
	}

	/**
	 * Перетворює відрендерений HTML (зі статичної копії) на розмітку блоків редактора.
	 *
	 * @param Mits_Static_Page $p    Сторінка.
	 * @param DOMNode          $root Контейнер.
	 */
	private function html_to_blocks( $p, $root ) {
		$out = array();
		foreach ( $root->childNodes as $node ) {
			if ( $node instanceof DOMText ) {
				$t = trim( $node->textContent );
				if ( '' !== $t ) {
					$out[] = "<!-- wp:paragraph -->\n<p>" . esc_html( $t ) . "</p>\n<!-- /wp:paragraph -->";
				}
				continue;
			}
			if ( ! ( $node instanceof DOMElement ) ) {
				continue;
			}
			$out[] = $this->element_to_block( $p, $node );
		}
		return $this->relativize( implode( "\n\n", array_filter( $out ) ) );
	}

	/**
	 * Один елемент → блок.
	 *
	 * @param Mits_Static_Page $p    Сторінка.
	 * @param DOMElement       $node Елемент.
	 */
	private function element_to_block( $p, $node ) {
		$tag = strtolower( $node->nodeName );
		$this->strip_render_attrs( $node );
		$html = $p->dom->saveHTML( $node );
		switch ( $tag ) {
			case 'p':
				$html = preg_replace( '/^<p class="wp-block-paragraph">/', '<p>', $html );
				$html = preg_replace( '/^<p class="wp-block-paragraph ([^"]*)">/', '<p class="$1">', $html );
				return "<!-- wp:paragraph -->\n" . $html . "\n<!-- /wp:paragraph -->";
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
				$level = (int) substr( $tag, 1 );
				return '<!-- wp:heading' . ( 2 === $level ? '' : ' {"level":' . $level . '}' ) . " -->\n" . $html . "\n<!-- /wp:heading -->";
			case 'ul':
			case 'ol':
				$items = array();
				foreach ( $node->childNodes as $li ) {
					if ( $li instanceof DOMElement && 'li' === $li->nodeName ) {
						$items[] = "<!-- wp:list-item -->\n" . $p->dom->saveHTML( $li ) . "\n<!-- /wp:list-item -->";
					}
				}
				$open = 'ol' === $tag ? '<ol class="wp-block-list">' : '<ul class="wp-block-list">';
				return '<!-- wp:list' . ( 'ol' === $tag ? ' {"ordered":true}' : '' ) . " -->\n" . $open . implode( '', $items ) . '</' . $tag . ">\n<!-- /wp:list -->";
			case 'figure':
				$class = ' ' . $node->getAttribute( 'class' ) . ' ';
				if ( false !== strpos( $class, ' wp-block-gallery ' ) ) {
					$inner = array();
					foreach ( $node->childNodes as $fig ) {
						if ( $fig instanceof DOMElement && 'figure' === $fig->nodeName ) {
							$inner[] = $this->element_to_block( $p, $fig );
						}
					}
					return "<!-- wp:gallery {\"linkTo\":\"none\"} -->\n<figure class=\"wp-block-gallery has-nested-images columns-default is-cropped\">" . implode( "\n\n", $inner ) . "</figure>\n<!-- /wp:gallery -->";
				}
				if ( false !== strpos( $class, ' wp-block-image ' ) ) {
					$img   = $p->one( 'img', $node );
					$id    = $img && preg_match( '/wp-image-(\d+)/', $img->getAttribute( 'class' ), $m ) ? (int) $m[1] : 0;
					$size  = preg_match( '/size-([\w-]+)/', $class, $m ) ? $m[1] : 'large';
					$attrs = array_filter( array( 'id' => $id ? $this->id( $id ) : null, 'sizeSlug' => $size, 'linkDestination' => 'none' ) );
					if ( $id && $this->id( $id ) && $img ) {
						$img->setAttribute( 'class', 'wp-image-' . $this->id( $id ) );
					}
					$html = $p->dom->saveHTML( $node );
					return '<!-- wp:image ' . wp_json_encode( $attrs ) . " -->\n" . $html . "\n<!-- /wp:image -->";
				}
				return "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";
			default:
				return "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";
		}
	}

	/**
	 * Прибирає атрибути, які WordPress додає під час виводу (srcset, sizes, loading…).
	 *
	 * @param DOMElement $node Елемент.
	 */
	private function strip_render_attrs( $node ) {
		foreach ( array( $node ) as $n ) {
			$imgs = $n->getElementsByTagName( 'img' );
			foreach ( iterator_to_array( $imgs ) as $img ) {
				foreach ( array( 'srcset', 'sizes', 'loading', 'decoding', 'fetchpriority', 'width', 'height', 'data-id' ) as $a ) {
					$img->removeAttribute( $a );
				}
				$img->setAttribute( 'src', $this->relativize( $img->getAttribute( 'src' ) ) );
			}
		}
	}
}
