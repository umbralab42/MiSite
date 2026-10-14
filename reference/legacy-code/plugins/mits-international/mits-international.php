<?php
/**
 * Plugin Name: MITS Міжнародна діяльність
 * Description: Налаштування сторінки «Міжнародна діяльність»: фото, текст, посилання, статистика.
 * Version:     1.0.0
 * Author:      MITS
 * Text Domain: mits-international
 *
 * @package MITS_International
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ═══════════════════════════════════════════════════════════════════
   1. ADMIN MENU
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', function (): void {

	// Батьківське меню
	add_menu_page(
		'MITS Міжнародна',
		'MITS Міжнародна',
		'edit_pages',
		'mits-international',
		'__return_null',
		'dashicons-networking',
		8
	);

	// Видалити авто-дублікат у підменю
	remove_submenu_page( 'mits-international', 'mits-international' );

	// Підсторінка «Налаштування»
	add_submenu_page(
		'mits-international',
		'Налаштування',
		'Налаштування',
		'edit_pages',
		'mits-international-settings',
		'mits_intl_settings_page_render'
	);

	// Підсторінка «Курси»
	add_submenu_page(
		'mits-international',
		'Курси',
		'Курси',
		'edit_pages',
		'mits-international-courses',
		'mits_intl_courses_page_render'
	);

	// Підсторінка «Документи»
	add_submenu_page(
		'mits-international',
		'Документи',
		'Документи',
		'edit_pages',
		'mits-international-docs',
		'mits_intl_docs_page_render'
	);

}, 999 );

// Підсвічування меню при редагуванні сторінки з шаблоном
add_filter( 'parent_file', function ( string $parent ): string {
	if ( ! is_admin() ) {
		return $parent;
	}
	$screen = get_current_screen();
	if ( ! $screen ) {
		return $parent;
	}
	if ( $screen->base === 'post' && $screen->post_type === 'page' ) {
		global $post;
		if ( $post && get_post_meta( $post->ID, '_wp_page_template', true ) === 'template-mits-international.php' ) {
			return 'mits-international';
		}
	}
	return $parent;
} );

/* ═══════════════════════════════════════════════════════════════════
   2. ENQUEUE MEDIA ON SETTINGS PAGE
═══════════════════════════════════════════════════════════════════ */

add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
	if ( strpos( $hook, 'mits-international-settings' ) !== false
		|| strpos( $hook, 'mits-international-courses' ) !== false ) {
		wp_enqueue_media();
	}
} );

/* ═══════════════════════════════════════════════════════════════════
   3. SETTINGS PAGE RENDER
═══════════════════════════════════════════════════════════════════ */

function mits_intl_settings_page_render(): void {

	// ── Handle save ──────────────────────────────────────────────────
	if (
		isset( $_POST['mits_intl_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_intl_nonce'] ) ), 'mits_intl_save' )
		&& current_user_can( 'edit_pages' )
	) {
		// Hero slider
		update_option( 'mits_intl_hero_id', absint( $_POST['intl_hero_id'] ?? 0 ) );

		// Фото
		update_option( 'mits_intl_photo', esc_url_raw( wp_unslash( $_POST['intl_photo'] ?? '' ) ) );

		// Текст
		update_option( 'mits_intl_text', wp_kses_post( wp_unslash( $_POST['intl_text'] ?? '' ) ) );

		// Посилання
		$link_names = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['intl_link_name'] ?? [] ) ) );
		$link_urls  = array_map( 'esc_url_raw',         array_map( 'wp_unslash', (array) ( $_POST['intl_link_url']  ?? [] ) ) );
		$links      = [];
		foreach ( $link_names as $i => $name ) {
			$url = $link_urls[ $i ] ?? '';
			if ( trim( $name ) === '' && trim( $url ) === '' ) {
				continue;
			}
			$links[] = [ 'name' => $name, 'url' => $url ];
		}
		update_option( 'mits_intl_links', wp_json_encode( $links, JSON_UNESCAPED_UNICODE ) );

		// Статистика
		$stat_types   = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['intl_stat_type']  ?? [] ) ) );
		$stat_labels_ = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['intl_stat_label'] ?? [] ) ) );
		$stat_values  = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['intl_stat_value'] ?? [] ) ) );
		$stats        = [];
		foreach ( $stat_types as $i => $type ) {
			if ( $type === 'divider' ) {
				$stats[] = [ 'divider' => true ];
			} else {
				$lbl = $stat_labels_[ $i ] ?? '';
				$val = $stat_values[ $i ] ?? '';
				if ( $lbl === '' && $val === '' ) {
					continue;
				}
				$stats[] = [ 'label' => $lbl, 'value' => $val ];
			}
		}
		update_option( 'mits_intl_stats', wp_json_encode( $stats, JSON_UNESCAPED_UNICODE ) );

		echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
	}

	// ── Load current values ──────────────────────────────────────────
	$hero_id = (int) get_option( 'mits_intl_hero_id', 0 );
	$photo   = get_option( 'mits_intl_photo', '' );
	$text    = get_option( 'mits_intl_text',  '' );
	$links = json_decode( get_option( 'mits_intl_links' ) ?: '[]', true );
	$stats = json_decode( get_option( 'mits_intl_stats' ) ?: '[]', true );
	if ( ! is_array( $links ) ) $links = [];
	if ( ! is_array( $stats ) ) $stats = [];
	// Backward compat: old format was assoc array {years:val, ...}
	if ( ! empty( $stats ) && array_keys( $stats ) !== range( 0, count( $stats ) - 1 ) ) {
		$_old_labels = [ 'years' => 'Роки', 'students' => 'Студенти', 'professors' => 'Професори', 'faculties' => 'Факультети', 'programs' => 'Програми', 'masters' => 'Магістерські програми', 'doctors' => 'Докторські програми' ];
		$_converted  = [];
		foreach ( $stats as $_k => $_v ) {
			if ( $_v !== '' ) {
				$_converted[] = [ 'label' => $_old_labels[ $_k ] ?? $_k, 'value' => $_v ];
			}
		}
			$stats = $_converted;
	}
	?>
	<div class="wrap">
	<h1>Міжнародна діяльність — Налаштування</h1>
	<form method="post">
		<?php wp_nonce_field( 'mits_intl_save', 'mits_intl_nonce' ); ?>

		<!-- ─────────────────── HERO SLIDER ─────────────────── -->
		<h2 style="margin-top:24px">Hero Slider</h2>
		<table class="form-table" style="margin-top:0">
			<tr>
				<th style="width:180px"><label for="intl-hero-id">ID слайду</label></th>
				<td>
					<input type="number" id="intl-hero-id" name="intl_hero_id" min="0"
					       value="<?php echo esc_attr( $hero_id ); ?>" style="width:120px">
					<p class="description">ID запису типу «Hero Слайди». 0 = показати всі слайди.</p>
					<?php
					if ( $hero_id ) {
						$slide = get_post( $hero_id );
						if ( $slide && $slide->post_type === 'mits_hero_slide' ) {
							echo '<p class="description" style="color:#46b450">✓ ' . esc_html( $slide->post_title ) . '</p>';
						} elseif ( $slide ) {
							echo '<p class="description" style="color:#d63638">⚠ Запис із цим ID не є Hero Слайдом.</p>';
						} else {
							echo '<p class="description" style="color:#d63638">⚠ Запис не знайдено.</p>';
						}
					}
					?>
				</td>
			</tr>
		</table>

		<!-- ─────────────────── ФОТО ─────────────────── -->
		<h2 style="margin-top:24px">Фото</h2>
		<table class="form-table" style="margin-top:0">
			<tr>
				<th style="width:180px"><label for="intl-photo">URL фото</label></th>
				<td>
					<?php $thumb = $photo ? '<img src="' . esc_url( $photo ) . '" style="max-height:80px;display:block;margin-bottom:8px">' : ''; ?>
					<div id="intl-photo-preview" style="margin-bottom:8px"><?php echo $thumb; ?></div>
					<input type="hidden" id="intl-photo" name="intl_photo" value="<?php echo esc_attr( $photo ); ?>">
					<button type="button" class="button" id="intl-photo-choose">Вибрати фото</button>
					<button type="button" class="button" id="intl-photo-clear"<?php echo $photo ? '' : ' style="display:none"'; ?>>✕ Видалити</button>
					<p class="description">Виберіть зображення з медіатеки.</p>
				</td>
			</tr>
		</table>

		<!-- ─────────────────── ТЕКСТ ─────────────────── -->
		<h2 style="margin-top:32px">Текст</h2>
		<?php
		wp_editor( $text, 'intl_text', [
			'textarea_name' => 'intl_text',
			'media_buttons' => false,
			'teeny'         => false,
			'textarea_rows' => 10,
			'quicktags'     => true,
		] );
		?>

		<!-- ─────────────────── ПОСИЛАННЯ ─────────────────── -->
		<h2 style="margin-top:40px">Посилання</h2>
		<p class="description" style="margin-bottom:12px">Довільна кількість посилань (назва + URL).</p>
		<table class="wp-list-table widefat striped" id="intl-links-table">
			<thead><tr>
				<th>Назва</th>
				<th>URL</th>
				<th style="width:44px"></th>
			</tr></thead>
			<tbody id="intl-links-rows">
			<?php foreach ( $links as $link ) : ?>
			<tr class="intl-link-row">
				<td><input type="text"  name="intl_link_name[]" value="<?php echo esc_attr( $link['name'] ?? '' ); ?>" class="regular-text" placeholder="Назва"></td>
				<td><input type="url"   name="intl_link_url[]"  value="<?php echo esc_attr( $link['url']  ?? '' ); ?>" class="large-text"   placeholder="https://"></td>
				<td><button type="button" class="button intl-link-remove">✕</button></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="intl-link-add">+ Додати посилання</button></p>

		<!-- ─────────────────── СТАТИСТИКА ─────────────────── -->
		<h2 style="margin-top:40px">Статистика</h2>
		<p class="description" style="margin-bottom:12px">Довільна кількість показників (назва + значення). «Розділювач» додає горизонтальну лінію між рядами на сторінці.</p>
		<table class="wp-list-table widefat striped" id="intl-stats-table">
			<thead><tr>
				<th>Назва</th>
				<th style="width:180px">Значення</th>
				<th style="width:44px"></th>
			</tr></thead>
			<tbody id="intl-stats-rows">
			<?php foreach ( $stats as $item ) : ?>
			<?php if ( ! empty( $item['divider'] ) ) : ?>
			<tr class="intl-stat-divider-row">
				<td colspan="2" style="background:#1a1a1a;color:#aaa;font-style:italic;padding:8px 10px">──── Розділювач рядків ────
					<input type="hidden" name="intl_stat_type[]" value="divider">
					<input type="hidden" name="intl_stat_label[]" value="">
					<input type="hidden" name="intl_stat_value[]" value="">
				</td>
				<td><button type="button" class="button intl-stat-remove">✕</button></td>
			</tr>
			<?php else : ?>
			<tr class="intl-stat-row">
				<td>
					<input type="hidden" name="intl_stat_type[]" value="stat">
					<input type="text" name="intl_stat_label[]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>" class="regular-text" placeholder="напр. років історії">
				</td>
				<td><input type="text" name="intl_stat_value[]" value="<?php echo esc_attr( $item['value'] ?? '' ); ?>" class="regular-text" placeholder="напр. 800"></td>
				<td><button type="button" class="button intl-stat-remove">✕</button></td>
			</tr>
			<?php endif; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p>
			<button type="button" class="button" id="intl-stat-add">+ Додати показник</button>
			<button type="button" class="button" id="intl-stat-divider-add" style="margin-left:8px">— Додати розділювач</button>
		</p>

		<?php submit_button( 'Зберегти' ); ?>
	</form>
	</div>

	<script>
	(function () {
		/* ── Photo picker ── */
		var photoInput   = document.getElementById('intl-photo');
		var photoPreview = document.getElementById('intl-photo-preview');
		var photoClear   = document.getElementById('intl-photo-clear');

		document.getElementById('intl-photo-choose').addEventListener('click', function () {
			var frame = wp.media({
				title:   'Вибрати фото',
				button:  { text: 'Вибрати' },
				library: { type: 'image' },
				multiple: false,
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				photoInput.value        = att.url;
				photoPreview.innerHTML  = '<img src="' + att.url + '" style="max-height:80px;display:block;margin-bottom:8px">';
				photoClear.style.display = 'inline-block';
			});
			frame.open();
		});

		photoClear.addEventListener('click', function () {
			photoInput.value        = '';
			photoPreview.innerHTML  = '';
			this.style.display      = 'none';
		});

		/* ── Links repeater ── */
		function buildLinkRow() {
			return '<tr class="intl-link-row">'
				+ '<td><input type="text"  name="intl_link_name[]" value="" class="regular-text" placeholder="Назва"></td>'
				+ '<td><input type="url"   name="intl_link_url[]"  value="" class="large-text"   placeholder="https://"></td>'
				+ '<td><button type="button" class="button intl-link-remove">✕</button></td>'
				+ '</tr>';
		}

		document.getElementById('intl-link-add').addEventListener('click', function () {
			document.getElementById('intl-links-rows').insertAdjacentHTML('beforeend', buildLinkRow());
		});

		document.addEventListener('click', function (e) {
			if ( e.target.classList.contains('intl-link-remove')
				|| e.target.classList.contains('intl-stat-remove')
				|| e.target.classList.contains('intl-course-remove') ) {
				e.target.closest('tr').remove();
			}
		});

		/* ── Stats repeater ── */
		function buildStatRow() {
			return '<tr class="intl-stat-row">'
				+ '<td><input type="hidden" name="intl_stat_type[]" value="stat">'
				+ '<input type="text" name="intl_stat_label[]" value="" class="regular-text" placeholder="напр. років історії"></td>'
				+ '<td><input type="text" name="intl_stat_value[]" value="" class="regular-text" placeholder="напр. 800"></td>'
				+ '<td><button type="button" class="button intl-stat-remove">✕</button></td>'
				+ '</tr>';
		}

		function buildStatDividerRow() {
			return '<tr class="intl-stat-divider-row">'
				+ '<td colspan="2" style="background:#1a1a1a;color:#aaa;font-style:italic;padding:8px 10px">──── Розділювач рядків ────'
				+ '<input type="hidden" name="intl_stat_type[]" value="divider">'
				+ '<input type="hidden" name="intl_stat_label[]" value="">'
				+ '<input type="hidden" name="intl_stat_value[]" value=""></td>'
				+ '<td><button type="button" class="button intl-stat-remove">✕</button></td>'
				+ '</tr>';
		}

		document.getElementById('intl-stat-add').addEventListener('click', function () {
			document.getElementById('intl-stats-rows').insertAdjacentHTML('beforeend', buildStatRow());
		});

		document.getElementById('intl-stat-divider-add').addEventListener('click', function () {
			document.getElementById('intl-stats-rows').insertAdjacentHTML('beforeend', buildStatDividerRow());
		});

	}());
	</script>
	<?php
}

/* ═══════════════════════════════════════════════════════════════════
   4. COURSES PAGE RENDER
═══════════════════════════════════════════════════════════════════ */

function mits_intl_courses_page_render(): void {

	// ── Handle save ──────────────────────────────────────────────────
	if (
		isset( $_POST['mits_intl_courses_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_intl_courses_nonce'] ) ), 'mits_intl_courses_save' )
		&& current_user_can( 'edit_pages' )
	) {
		update_option( 'mits_intl_courses_photo',  esc_url_raw( wp_unslash( $_POST['intl_courses_photo']  ?? '' ) ) );
		update_option( 'mits_intl_courses_text',   wp_kses_post( wp_unslash( $_POST['intl_courses_text']   ?? '' ) ) );
		update_option( 'mits_intl_courses_slogan', sanitize_text_field( wp_unslash( $_POST['intl_courses_slogan'] ?? '' ) ) );
		update_option( 'mits_intl_courses_title',  sanitize_text_field( wp_unslash( $_POST['intl_courses_title']  ?? '' ) ) );
		$course_names = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['intl_course_name'] ?? [] ) ) );
		$course_descs = array_map( 'wp_kses_post',         array_map( 'wp_unslash', (array) ( $_POST['intl_course_desc'] ?? [] ) ) );
		$courses      = [];
		foreach ( $course_names as $i => $name ) {
			$desc = $course_descs[ $i ] ?? '';
			if ( trim( $name ) === '' && trim( $desc ) === '' ) {
				continue;
			}
			$courses[] = [ 'name' => $name, 'desc' => $desc ];
		}
		update_option( 'mits_intl_courses', wp_json_encode( $courses, JSON_UNESCAPED_UNICODE ) );

		echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
	}

	// ── Load current values ──────────────────────────────────────────
	$courses_photo  = get_option( 'mits_intl_courses_photo',  '' );
	$courses_text   = get_option( 'mits_intl_courses_text',   '' );
	$courses_slogan = get_option( 'mits_intl_courses_slogan', '' );
	$courses_title  = get_option( 'mits_intl_courses_title',  '' );
	$courses        = json_decode( get_option( 'mits_intl_courses' ) ?: '[]', true );
	if ( ! is_array( $courses ) ) $courses = [];

	?>
	<div class="wrap">
	<h1>Міжнародна діяльність — Курси</h1>
	<form method="post">
		<?php wp_nonce_field( 'mits_intl_courses_save', 'mits_intl_courses_nonce' ); ?>

		<!-- ─────────────────── ФОТО + ТЕКСТ ─────────────────── -->
		<h2 style="margin-top:24px">Фото та текст (вгорі секції)</h2>
		<table class="form-table" style="margin-top:0">
			<tr>
				<th style="width:180px"><label>Фото</label></th>
				<td>
					<?php $thumb = $courses_photo ? '<img src="' . esc_url( $courses_photo ) . '" style="max-height:80px;display:block;margin-bottom:8px">' : ''; ?>
					<div id="intl-courses-photo-preview" style="margin-bottom:8px"><?php echo $thumb; ?></div>
					<input type="hidden" id="intl-courses-photo" name="intl_courses_photo" value="<?php echo esc_attr( $courses_photo ); ?>">
					<button type="button" class="button" id="intl-courses-photo-choose">Вибрати фото</button>
					<button type="button" class="button" id="intl-courses-photo-clear"<?php echo $courses_photo ? '' : ' style="display:none"'; ?>>✕ Видалити</button>
				</td>
			</tr>
		</table>
		<p style="margin-bottom:6px"><strong>Текст</strong></p>
		<?php
		wp_editor( $courses_text, 'intl_courses_text', [
			'textarea_name' => 'intl_courses_text',
			'media_buttons' => false,
			'teeny'         => false,
			'textarea_rows' => 8,
			'quicktags'     => true,
		] );
		?>

		<!-- ─────────────────── СЛОГАН / ЗАГОЛОВОК ─────────────────── -->
		<h2 style="margin-top:40px">Слоган та заголовок</h2>
		<table class="form-table" style="margin-top:0">
			<tr>
				<th style="width:180px"><label for="intl-courses-slogan">Слоган</label></th>
				<td><input type="text" id="intl-courses-slogan" name="intl_courses_slogan" value="<?php echo esc_attr( $courses_slogan ); ?>" class="large-text" placeholder="Короткий слоган над заголовком"></td>
			</tr>
			<tr>
				<th><label for="intl-courses-title">Заголовок</label></th>
				<td><input type="text" id="intl-courses-title" name="intl_courses_title" value="<?php echo esc_attr( $courses_title ); ?>" class="large-text" placeholder="Назва розділу"></td>
			</tr>
		</table>

		<h2 style="margin-top:32px">Перелік курсів</h2>
		<p class="description" style="margin-bottom:12px">Назва + опис (опис необов'язковий).</p>
		<table class="wp-list-table widefat striped" id="intl-courses-table">
			<thead><tr>
				<th>Назва та опис курсу</th>
				<th style="width:44px"></th>
			</tr></thead>
			<tbody id="intl-courses-rows">
			<?php foreach ( $courses as $course ) : ?>
			<tr class="intl-course-row">
				<td>
					<input type="text" name="intl_course_name[]" value="<?php echo esc_attr( $course['name'] ?? '' ); ?>" class="large-text" placeholder="Назва курсу">
					<textarea name="intl_course_desc[]" rows="2" class="large-text" style="margin-top:6px;resize:vertical" placeholder="Опис (необов'язково)"><?php echo esc_textarea( $course['desc'] ?? '' ); ?></textarea>
				</td>
				<td style="vertical-align:top;padding-top:12px"><button type="button" class="button intl-course-remove">✕</button></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="intl-course-add">+ Додати курс</button></p>

		<?php submit_button( 'Зберегти' ); ?>
	</form>
	</div>

	<script>
	(function () {
		document.addEventListener('click', function (e) {
			if ( e.target.classList.contains('intl-course-remove') ) {
				e.target.closest('tr').remove();
			}
		});

		function buildCourseRow() {
			return '<tr class="intl-course-row">'
				+ '<td><input type="text" name="intl_course_name[]" value="" class="large-text" placeholder="Назва курсу">'
				+ '<textarea name="intl_course_desc[]" rows="2" class="large-text" style="margin-top:6px;resize:vertical" placeholder="Опис (необов\'язково)"></textarea></td>'
				+ '<td style="vertical-align:top;padding-top:12px"><button type="button" class="button intl-course-remove">✕</button></td>'
				+ '</tr>';
		}

		document.getElementById('intl-course-add').addEventListener('click', function () {
			document.getElementById('intl-courses-rows').insertAdjacentHTML('beforeend', buildCourseRow());
		});

		/* ── Courses photo picker ── */
		var cPhotoInput   = document.getElementById('intl-courses-photo');
		var cPhotoPreview = document.getElementById('intl-courses-photo-preview');
		var cPhotoClear   = document.getElementById('intl-courses-photo-clear');

		if ( cPhotoInput ) {
			document.getElementById('intl-courses-photo-choose').addEventListener('click', function () {
				var frame = wp.media({
					title:   'Вибрати фото',
					button:  { text: 'Вибрати' },
					library: { type: 'image' },
					multiple: false,
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					cPhotoInput.value       = att.url;
					cPhotoPreview.innerHTML = '<img src="' + att.url + '" style="max-height:80px;display:block;margin-bottom:8px">';
					cPhotoClear.style.display = 'inline-block';
				});
				frame.open();
			});
			cPhotoClear.addEventListener('click', function () {
				cPhotoInput.value       = '';
				cPhotoPreview.innerHTML = '';
				this.style.display      = 'none';
			});
		}
	}());
	</script>
	<?php
}

/* ═══════════════════════════════════════════════════════════════════
   5. DOCUMENTS PAGE RENDER
═══════════════════════════════════════════════════════════════════ */

function mits_intl_docs_page_render(): void {

	// ── Handle save ──────────────────────────────────────────────────
	if (
		isset( $_POST['mits_intl_docs_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mits_intl_docs_nonce'] ) ), 'mits_intl_docs_save' )
		&& current_user_can( 'edit_pages' )
	) {
		update_option( 'mits_intl_docs_title', sanitize_text_field( wp_unslash( $_POST['intl_docs_title'] ?? '' ) ) );

		$doc_names = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['intl_doc_name'] ?? [] ) ) );
		$doc_urls  = array_map( 'esc_url_raw',          array_map( 'wp_unslash', (array) ( $_POST['intl_doc_url']  ?? [] ) ) );
		$docs      = [];
		foreach ( $doc_names as $i => $name ) {
			$url = $doc_urls[ $i ] ?? '';
			if ( trim( $name ) === '' && trim( $url ) === '' ) {
				continue;
			}
			$docs[] = [ 'name' => $name, 'url' => $url ];
		}
		update_option( 'mits_intl_docs', wp_json_encode( $docs, JSON_UNESCAPED_UNICODE ) );

		echo '<div class="notice notice-success is-dismissible"><p>Збережено.</p></div>';
	}

	// ── Load current values ──────────────────────────────────────────
	$docs_title = get_option( 'mits_intl_docs_title', '' );
	$docs       = json_decode( get_option( 'mits_intl_docs' ) ?: '[]', true );
	if ( ! is_array( $docs ) ) $docs = [];

	?>
	<div class="wrap">
	<h1>Міжнародна діяльність — Документи</h1>
	<form method="post">
		<?php wp_nonce_field( 'mits_intl_docs_save', 'mits_intl_docs_nonce' ); ?>

		<table class="form-table" style="margin-top:0">
			<tr>
				<th style="width:180px"><label for="intl-docs-title">Заголовок секції</label></th>
				<td><input type="text" id="intl-docs-title" name="intl_docs_title"
				           value="<?php echo esc_attr( $docs_title ); ?>"
				           class="large-text" placeholder="напр. Документи"></td>
			</tr>
		</table>

		<h2 style="margin-top:32px">Перелік документів</h2>
		<p class="description" style="margin-bottom:12px">Назва документа + посилання на файл (PDF або інший).</p>
		<table class="wp-list-table widefat striped" id="intl-docs-table">
			<thead><tr>
				<th>Назва</th>
				<th>URL файлу</th>
				<th style="width:44px"></th>
			</tr></thead>
			<tbody id="intl-docs-rows">
			<?php foreach ( $docs as $doc ) : ?>
			<tr class="intl-doc-row">
				<td><input type="text" name="intl_doc_name[]"
				           value="<?php echo esc_attr( $doc['name'] ?? '' ); ?>"
				           class="regular-text" placeholder="Назва документа"></td>
				<td><input type="url" name="intl_doc_url[]"
				           value="<?php echo esc_attr( $doc['url'] ?? '' ); ?>"
				           class="large-text" placeholder="https://"></td>
				<td><button type="button" class="button intl-doc-remove">✕</button></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="intl-doc-add">+ Додати документ</button></p>

		<?php submit_button( 'Зберегти' ); ?>
	</form>
	</div>

	<script>
	(function () {
		document.addEventListener('click', function (e) {
			if ( e.target.classList.contains('intl-doc-remove') ) {
				e.target.closest('tr').remove();
			}
		});

		function buildDocRow() {
			return '<tr class="intl-doc-row">'
				+ '<td><input type="text" name="intl_doc_name[]" value="" class="regular-text" placeholder="Назва документа"></td>'
				+ '<td><input type="url"  name="intl_doc_url[]"  value="" class="large-text"   placeholder="https://"></td>'
				+ '<td><button type="button" class="button intl-doc-remove">✕</button></td>'
				+ '</tr>';
		}

		document.getElementById('intl-doc-add').addEventListener('click', function () {
			document.getElementById('intl-docs-rows').insertAdjacentHTML('beforeend', buildDocRow());
		});
	}());
	</script>
	<?php
}
