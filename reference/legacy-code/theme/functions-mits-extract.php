<?php
// Extract: MITS-specific parts of the old betheme/functions.php (the rest is the commercial theme and is not included).

// --- functions.php lines 158-158
require_once(get_theme_file_path('/includes/mits-i18n.php'));

// --- functions.php lines 265-271
// Register MITS nav menu locations
add_action( 'init', function () {
    register_nav_menus( array(
        'mits-primary' => __( 'MITS — Головна навігація', 'betheme' ),
        'mits-footer'  => __( 'MITS — Підвал навігація',  'betheme' ),
    ) );
} );

// --- functions.php lines 274-288
add_action( 'after_setup_theme', function () {
    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 80,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    // Dark editor styles — makes TinyMCE & Gutenberg preview match the front end
    add_theme_support( 'editor-styles' );
    add_editor_style( 'css/mits-components.css' );
    add_editor_style( 'css/mits-front.css' );
    add_editor_style( 'css/mits-pages.css' );
    add_editor_style( 'css/mits-teachers.css' );
    add_editor_style( 'css/mits-editor.css' );
}, 11 );

// --- functions.php lines 291-309
add_action( 'enqueue_block_editor_assets', function () {
    wp_enqueue_style(
        'mits-editor-fonts',
        'https://fonts.googleapis.com/css2?family=Geist:wght@400;600;700;800&family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap',
        [],
        null
    );

    // Hero-slider styles (plugin CSS not covered by add_editor_style)
    $hero_css = WP_PLUGIN_DIR . '/mits-hero-slider/css/mits-hero.css';
    if ( file_exists( $hero_css ) ) {
        wp_enqueue_style(
            'mits-hero-editor',
            plugins_url( 'mits-hero-slider/css/mits-hero.css' ),
            [],
            '1.0'
        );
    }
} );

// --- functions.php lines 319-366
function mits_lang_switcher_url( string $target_lang ): string {
	$home_url    = trailingslashit( home_url( '/' ) );
	$target_home = function_exists( 'pll_home_url' )
		? trailingslashit( pll_home_url( $target_lang ) )
		: ( $target_lang === 'uk' ? $home_url : trailingslashit( home_url( '/en/' ) ) );

	// 1. For singular posts/pages: use Polylang's per-post translation API.
	//    pll_the_languages() is intentionally NOT used here — it reads from internal state
	//    that can be wrong for CPT archives, returning stale or unrelated URLs.
	if ( is_singular() && function_exists( 'pll_get_post' ) ) {
		$translated_id = pll_get_post( get_the_ID(), $target_lang );
		if ( $translated_id ) {
			return esc_url( get_permalink( $translated_id ) );
		}
		// Post exists but no translation — fall back to language home.
		return esc_url( $target_home );
	}

	// 2. Build candidate URL by detecting and swapping the language prefix in REQUEST_URI.
	//    We detect from the URL directly (not pll_current_language()) so CPT archives work
	//    correctly even when Polylang returns the default language for unregistered post types.
	$default_lang = function_exists( 'pll_default_language' ) ? pll_default_language() : 'uk';
	$known_slugs  = function_exists( 'pll_languages_list' )
		? pll_languages_list( [ 'fields' => 'slug' ] )
		: [ $default_lang, $target_lang ];

	$home_path = rtrim( parse_url( $home_url, PHP_URL_PATH ) ?? '/', '/' );
	$req_path  = strtok( $_SERVER['REQUEST_URI'] ?? '/', '?' ); // path only, no query string

	// Strip home path prefix to get the relative path.
	$rel = ltrim( substr( $req_path, strlen( $home_path ) ), '/' );

	// Strip any known language slug prefix found in the URL (e.g. "en/").
	foreach ( $known_slugs as $_slug ) {
		if ( preg_match( '/^' . preg_quote( $_slug, '/' ) . '\//', $rel ) ) {
			$rel = substr( $rel, strlen( $_slug ) + 1 );
			break;
		}
	}

	// Default language has no URL prefix; other languages get one.
	$candidate = ( $target_lang === $default_lang )
		? $home_url . $rel
		: $home_url . $target_lang . '/' . $rel;

	// 3. Validate the candidate; fall back to language home if it doesn't exist.
	return esc_url( mits_candidate_url_exists( $candidate ) ? $candidate : $target_home );
}

// --- functions.php lines 372-407
function mits_candidate_url_exists( string $url ): bool {
	$url = trailingslashit( $url );

	// Home is always valid.
	if ( $url === trailingslashit( home_url( '/' ) ) ) {
		return true;
	}

	// Language home URLs are always valid.
	if ( function_exists( 'pll_languages_list' ) ) {
		foreach ( pll_languages_list( [ 'fields' => 'slug' ] ) as $slug ) {
			$lang_home = function_exists( 'pll_home_url' )
				? trailingslashit( pll_home_url( $slug ) )
				: trailingslashit( home_url( $slug === 'uk' ? '/' : '/' . $slug . '/' ) );
			if ( $url === $lang_home ) {
				return true;
			}
		}
	}

	// Singular posts / pages (Polylang hooks into url_to_postid to handle language-prefixed URLs).
	if ( url_to_postid( $url ) > 0 ) {
		return true;
	}

	// Post-type archives: compare path slugs after stripping home path and language prefix.
	$url_slug = mits_url_to_slug( $url );
	foreach ( get_post_types( [ 'public' => true, 'has_archive' => true ] ) as $pt ) {
		$archive = get_post_type_archive_link( $pt );
		if ( $archive && mits_url_to_slug( $archive ) === $url_slug ) {
			return true;
		}
	}

	return false;
}

// --- functions.php lines 413-420
function mits_url_to_slug( string $url ): string {
	$home_path = rtrim( parse_url( home_url( '/' ), PHP_URL_PATH ) ?? '/', '/' );
	$path      = parse_url( $url, PHP_URL_PATH ) ?? '';
	$rel       = ltrim( substr( $path, strlen( $home_path ) ), '/' );
	// Strip a leading 2-letter language segment (e.g. "en/", "uk/").
	$rel = preg_replace( '/^[a-z]{2}\//', '', $rel );
	return rtrim( $rel, '/' );
}

// --- functions.php lines 422-456
// Enqueue MITS stylesheets on all front-end pages
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'mits-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'mits-components',
        get_template_directory_uri() . '/css/mits-components.css',
        array(),
        '1.0'
    );
    wp_enqueue_style(
        'mits-front',
        get_template_directory_uri() . '/css/mits-front.css',
        array( 'mits-components' ),
        '4.7.7'
    );
    wp_enqueue_style(
        'mits-pages',
        get_template_directory_uri() . '/css/mits-pages.css',
        array( 'mits-front' ),
        '1.0'
    );
    if ( is_post_type_archive( 'teacher' ) || is_singular( 'teacher' ) ) {
        wp_enqueue_style(
            'mits-teachers',
            get_template_directory_uri() . '/css/mits-teachers.css',
            array( 'mits-front' ),
            '1.0'
        );
    }
} );

// --- functions.php lines 457-460
// Hero slider assets + gradient CSS are enqueued by the mits-hero-slider plugin.

// Suppress Betheme header and footer globally on all pages
add_filter( 'mfn_header_render', '__return_false' );

// --- functions.php lines 464-473
add_action( 'mfn_before_content', function () {
    if ( is_front_page() || is_home() ) {
        return;
    }
    $title = get_the_title();
    if ( ! $title ) {
        return;
    }
    echo '<h1 class="mits-page-title">' . wp_kses( $title, array() ) . '</h1>';
} );

// --- functions.php lines 475-513
// ── MITS menus — register parent items early (before plugins add submenus)
add_action( 'admin_menu', function () {
    add_menu_page(
        'MITS Контент',
        'MITS Контент',
        'edit_posts',
        'mits-content',
        '__return_null',
        'dashicons-admin-multisite',
        4
    );
    add_menu_page(
        'MITS Сторінки',
        'MITS Сторінки',
        'edit_posts',
        'mits-pages',
        '__return_null',
        'dashicons-welcome-widgets-menus',
        5
    );
    add_menu_page(
        'MITS Налаштування',
        'MITS Налаштування',
        'manage_options',
        'mits-settings',
        '__return_null',
        'dashicons-admin-settings',
        6
    );
    add_menu_page(
        'MITS Освіта',
        'MITS Освіта',
        'edit_posts',
        'mits-osvita',
        '__return_null',
        'dashicons-welcome-learn-more',
        7
    );
}, 1 );

// --- functions.php lines 515-549
// ── MITS menus — move CPTs + hide unused items ──────────────────
add_action( 'admin_menu', function () {

    // Remove the auto-duplicated parent items from their own submenus
    remove_submenu_page( 'mits-content', 'mits-content' );
    remove_submenu_page( 'mits-pages', 'mits-pages' );
    remove_submenu_page( 'mits-settings', 'mits-settings' );
    remove_submenu_page( 'mits-osvita', 'mits-osvita' );
    remove_submenu_page( 'mits-international', 'mits-international' );

    // MITS Контент: Новини, Документи, FAQ, Викладачі
    $content_cpts = array(
        'news'          => 'Новини',
        'mits_document' => 'Документи',
        'mits_faq'      => 'FAQ',
        'teacher'       => 'Викладачі',
    );
    foreach ( $content_cpts as $slug => $label ) {
        add_submenu_page( 'mits-content', $label, $label, 'edit_posts', 'edit.php?post_type=' . $slug );
        remove_menu_page( 'edit.php?post_type=' . $slug );
    }

    // MITS Сторінки: Сторінки, Hero Слайди
    add_submenu_page( 'mits-pages', 'Сторінки', 'Сторінки', 'edit_pages', 'edit.php?post_type=page' );
    remove_menu_page( 'edit.php?post_type=page' );

    add_submenu_page( 'mits-pages', 'Hero Слайди', 'Hero Слайди', 'edit_posts', 'edit.php?post_type=mits_hero_slide' );
    remove_menu_page( 'edit.php?post_type=mits_hero_slide' );

    // Hide unused The7 post types
    foreach ( array( 'offer', 'portfolio', 'slide', 'testimonial', 'client', 'layout' ) as $slug ) {
        remove_menu_page( 'edit.php?post_type=' . $slug );
    }

}, 999 );

// --- functions.php lines 554-566
add_action( 'add_meta_boxes', function () {
    // Show only on pages that use the kafedra template
    $screen = get_current_screen();
    add_meta_box(
        'mits_kafedra_fields',
        'Кафедра — спеціалізації та посилання',
        'mits_kafedra_meta_box_render',
        'page',
        'normal',
        'default',
        null
    );
} );

// --- functions.php lines 568-594
function mits_kafedra_meta_box_render( $post ) {
    // Identify kafedra pages by slug
    $kafedra_slugs = array( '%d0%ba%d0%b0%d1%84%d0%b5%d0%b4%d1%80%d0%b0', 'department' );
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $is_kafedra = in_array( $post->post_name, $kafedra_slugs, true )
               || in_array( $post->ID, array( 9, 136 ), true )
               || $tpl === 'template-mits-kafedra.php';
    if ( ! $is_kafedra ) {
        echo '<p style="color:#888">Ця панель активна тільки для сторінки <em>Кафедра</em>.</p>';
        return;
    }
    wp_nonce_field( 'mits_kafedra_save', 'mits_kafedra_nonce' );
    $fields = array(
        '_kafedra_spec_1'      => 'Спеціалізація 1',
        '_kafedra_spec_2'      => 'Спеціалізація 2',
        '_kafedra_spec_3'      => 'Спеціалізація 3',
        '_kafedra_history_url' => 'Посилання «Історія кафедри» (URL)',
        '_kafedra_history_text'=> 'Текст посилання «Історія кафедри»',
    );
    echo '<table class="form-table" style="width:100%">';
    foreach ( $fields as $key => $label ) {
        $val = esc_attr( get_post_meta( $post->ID, $key, true ) );
        echo '<tr><th style="width:200px"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . $val . '" style="width:100%" /></td></tr>';
    }
    echo '</table>';
}

// --- functions.php lines 596-608
add_action( 'save_post_page', function ( $post_id ) {
    if ( ! isset( $_POST['mits_kafedra_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['mits_kafedra_nonce'], 'mits_kafedra_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_page', $post_id ) ) return;

    $fields = array( '_kafedra_spec_1', '_kafedra_spec_2', '_kafedra_spec_3', '_kafedra_history_url', '_kafedra_history_text' );
    foreach ( $fields as $key ) {
        if ( array_key_exists( $key, $_POST ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
} );
