<?php
/**
 * Невеликий набір полів для метабоксів і сторінки налаштувань на рідному API WordPress.
 *
 * Опис поля: [
 *   'key'     => ключ мета-поля / опції,
 *   'label'   => підпис,
 *   'type'    => text|textarea|textarea_html|html|url|number|color|image|image_id|page|slide|list|repeater|checkbox,
 *               (textarea_html — текстове поле, у якому дозволено &lt;strong&gt;, &lt;br&gt;, посилання),
 *   'help'    => підказка (необов'язково),
 *   'fields'  => підполя для repeater,
 *   'storage' => 'json' (за замовчуванням для list/repeater) або 'array' (серіалізований масив PHP),
 * ]
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Виводить поле.
 *
 * @param array  $field Опис поля.
 * @param string $name  Атрибут name.
 * @param mixed  $value Поточне значення.
 */
function mits_render_field( $field, $name, $value ) {
	$type = isset( $field['type'] ) ? $field['type'] : 'text';
	$id   = 'mits-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $name ) );

	switch ( $type ) {
		case 'textarea':
		case 'textarea_html':
			printf( '<textarea class="large-text" rows="%3$d" id="%1$s" name="%2$s">%4$s</textarea>', esc_attr( $id ), esc_attr( $name ), isset( $field['rows'] ) ? (int) $field['rows'] : 4, esc_textarea( (string) $value ) );
			break;

		case 'html':
			wp_editor( (string) $value, str_replace( '-', '_', $id ), array(
				'textarea_name' => $name,
				'textarea_rows' => isset( $field['rows'] ) ? (int) $field['rows'] : 6,
				'media_buttons' => ! empty( $field['media'] ),
				'teeny'         => true,
				'quicktags'     => true,
			) );
			break;

		case 'url':
			printf( '<input type="text" class="large-text code" id="%1$s" name="%2$s" value="%3$s" placeholder="https://… або /шлях/">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			break;

		case 'number':
			printf( '<input type="number" class="small-text" id="%1$s" name="%2$s" value="%3$s" min="0">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			break;

		case 'color':
			printf( '<input type="text" class="mits-color" id="%1$s" name="%2$s" value="%3$s" placeholder="#000000" pattern="#[0-9a-fA-F]{3,6}">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			break;

		case 'checkbox':
			printf( '<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> %4$s</label>', esc_attr( $id ), esc_attr( $name ), checked( ! empty( $value ), true, false ), esc_html( isset( $field['text'] ) ? $field['text'] : '' ) );
			break;

		case 'image':
		case 'image_id':
			$is_id = 'image_id' === $type;
			$src   = $is_id ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : (string) $value;
			echo '<div class="mits-image-field" data-store="' . ( $is_id ? 'id' : 'url' ) . '">';
			printf( '<input type="%1$s" class="large-text code mits-image-value" id="%2$s" name="%3$s" value="%4$s">', $is_id ? 'hidden' : 'text', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			printf( '<img class="mits-image-preview" src="%1$s" alt=""%2$s>', esc_url( $src ? mits_url( $src ) : '' ), $src ? '' : ' hidden' );
			echo '<button type="button" class="button mits-image-pick">Вибрати зображення</button> ';
			echo '<button type="button" class="button-link mits-image-clear">Прибрати</button>';
			echo '</div>';
			break;

		case 'page':
			wp_dropdown_pages( array(
				'name'              => $name,
				'id'                => $id,
				'selected'          => (int) $value,
				'show_option_none'  => '— не вибрано —',
				'option_none_value' => '0',
				'lang'              => '',
			) );
			break;

		case 'slide':
			$slides = get_posts( array(
				'post_type'      => 'mits_hero_slide',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'lang'           => function_exists( 'pll_default_language' ) ? pll_default_language() : '',
			) );
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			printf( '<option value="0">%s</option>', esc_html( isset( $field['none'] ) ? $field['none'] : '— усі слайди —' ) );
			foreach ( $slides as $slide ) {
				$label = trim( get_post_meta( $slide->ID, '_mits_slide_line1', true ) . ' ' . get_post_meta( $slide->ID, '_mits_slide_line2', true ) );
				printf( '<option value="%1$d" %2$s>%3$s</option>', (int) $slide->ID, selected( (int) $value, $slide->ID, false ), esc_html( '#' . $slide->ID . ' ' . ( $label ? $label : $slide->post_title ) ) );
			}
			echo '</select>';
			echo '<p class="description">Для англійської версії автоматично береться переклад вибраного слайда.</p>';
			break;

		case 'list':
			$items = mits_field_list_value( $value );
			$sub   = array( array( 'key' => '', 'type' => isset( $field['item'] ) ? $field['item'] : 'text' ) );
			mits_render_repeater( $field, $name, array_map( function ( $v ) {
				return array( '' => $v );
			}, $items ), $sub, true );
			break;

		case 'repeater':
			mits_render_repeater( $field, $name, mits_field_list_value( $value ), $field['fields'], false );
			break;

		default:
			printf( '<input type="text" class="large-text" id="%1$s" name="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
	}

	if ( ! empty( $field['help'] ) ) {
		echo '<p class="description">' . wp_kses_post( $field['help'] ) . '</p>';
	}
}

/**
 * Значення поля-списку як масив.
 *
 * @param mixed $value JSON, серіалізований масив або масив.
 */
function mits_field_list_value( $value ) {
	if ( is_array( $value ) ) {
		return array_values( $value );
	}
	$maybe = maybe_unserialize( $value );
	if ( is_array( $maybe ) ) {
		return array_values( $maybe );
	}
	return array_values( mits_json_decode( $value ) );
}

/**
 * Повторюваний блок: рядки з підполями, кнопки «Додати», «Вгору», «Вниз», «Видалити».
 *
 * @param array  $field  Опис поля.
 * @param string $name   Атрибут name.
 * @param array  $rows   Рядки.
 * @param array  $fields Підполя.
 * @param bool   $simple Простий список (одне поле без підпису).
 * @param int    $depth  Рівень вкладеності (для власного заповнювача індексу).
 */
function mits_render_repeater( $field, $name, $rows, $fields, $simple, $depth = 0 ) {
	$placeholder = '__i' . $depth . '__';
	$row_html    = function ( $index, $row ) use ( $name, $fields, $simple, $depth ) {
		ob_start();
		echo '<div class="mits-repeater__row">';
		echo '<div class="mits-repeater__fields">';
		foreach ( $fields as $sub ) {
			$sub_name = $simple ? $name . '[' . $index . ']' : $name . '[' . $index . '][' . $sub['key'] . ']';
			$val      = isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : '';
			if ( isset( $sub['path'] ) ) {
				$val = isset( $row[ $sub['path'][0] ][ $sub['path'][1] ] ) ? $row[ $sub['path'][0] ][ $sub['path'][1] ] : '';
				$sub_name = $name . '[' . $index . '][' . $sub['path'][0] . '][' . $sub['path'][1] . ']';
			}
			echo '<div class="mits-repeater__field">';
			if ( ! $simple && ! empty( $sub['label'] ) ) {
				echo '<span class="mits-repeater__label">' . esc_html( $sub['label'] ) . '</span>';
			}
			if ( 'html' === ( isset( $sub['type'] ) ? $sub['type'] : '' ) ) {
				// У повторюваних рядках редактор TinyMCE не створюється динамічно — простий HTML.
				$sub['type'] = 'textarea';
				$sub['rows'] = isset( $sub['rows'] ) ? $sub['rows'] : 3;
			}
			if ( 'repeater' === ( isset( $sub['type'] ) ? $sub['type'] : '' ) ) {
				mits_render_repeater( $sub, $sub_name, is_array( $val ) ? array_values( $val ) : mits_field_list_value( $val ), $sub['fields'], false, $depth + 1 );
			} else {
				mits_render_field( array_diff_key( $sub, array( 'help' => 1 ) ), $sub_name, $val );
			}
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="mits-repeater__actions">';
		echo '<button type="button" class="button-link mits-repeater__up" aria-label="Вгору">↑</button>';
		echo '<button type="button" class="button-link mits-repeater__down" aria-label="Вниз">↓</button>';
		echo '<button type="button" class="button-link button-link-delete mits-repeater__remove" aria-label="Видалити">✕</button>';
		echo '</div>';
		echo '</div>';
		return ob_get_clean();
	};

	echo '<div class="mits-repeater' . ( $simple ? ' mits-repeater--simple' : '' ) . '" data-placeholder="' . esc_attr( $placeholder ) . '">';
	echo '<input type="hidden" name="' . esc_attr( $name ) . '[__present]" value="1">';
	echo '<div class="mits-repeater__rows">';
	foreach ( array_values( $rows ) as $i => $row ) {
		echo $row_html( $i, $row ); // phpcs:ignore WordPress.Security.EscapeOutput -- зібрано з екранованих частин.
	}
	echo '</div>';
	echo '<template class="mits-repeater__template">' . $row_html( $placeholder, array() ) . '</template>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<button type="button" class="button mits-repeater__add">' . esc_html( isset( $field['add'] ) ? $field['add'] : '+ Додати' ) . '</button>';
	echo '</div>';
}

/**
 * Очищення значення поля перед збереженням.
 *
 * @param array $field Опис поля.
 * @param mixed $raw   Значення з форми (вже без слешів).
 * @return mixed
 */
function mits_sanitize_field( $field, $raw ) {
	$type = isset( $field['type'] ) ? $field['type'] : 'text';
	switch ( $type ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $raw );
		case 'textarea_html':
			return trim( wp_kses( (string) $raw, mits_kses_inline() ) );
		case 'html':
			return wp_kses_post( (string) $raw );
		case 'url':
			$raw = trim( (string) $raw );
			if ( '' === $raw ) {
				return '';
			}
			if ( '#' === $raw[0] || ( '/' === $raw[0] && ( ! isset( $raw[1] ) || '/' !== $raw[1] ) ) ) {
				return sanitize_text_field( $raw );
			}
			return esc_url_raw( $raw );
		case 'image':
			$raw = trim( (string) $raw );
			return ( '' !== $raw && '/' === $raw[0] ) ? sanitize_text_field( $raw ) : esc_url_raw( $raw );
		case 'number':
			return '' === trim( (string) $raw ) ? '' : absint( $raw );
		case 'image_id':
		case 'page':
		case 'slide':
			return absint( $raw );
		case 'color':
			return preg_match( '/^#[0-9a-fA-F]{3,6}$/', trim( (string) $raw ) ) ? strtolower( trim( $raw ) ) : '';
		case 'checkbox':
			return empty( $raw ) ? '' : '1';
		case 'list':
			$item  = array( 'type' => isset( $field['item'] ) ? $field['item'] : 'text' );
			$items = array();
			foreach ( (array) $raw as $k => $v ) {
				if ( '__present' === $k || is_array( $v ) ) {
					continue;
				}
				$v = mits_sanitize_field( $item, $v );
				if ( '' !== trim( wp_strip_all_tags( (string) $v ) ) || '' !== trim( (string) $v ) ) {
					$items[] = $v;
				}
			}
			return mits_store_list( $field, $items );
		case 'repeater':
			$rows = array();
			foreach ( (array) $raw as $k => $row ) {
				if ( '__present' === $k || ! is_array( $row ) ) {
					continue;
				}
				$clean = array();
				$empty = true;
				foreach ( $field['fields'] as $sub ) {
					if ( isset( $sub['path'] ) ) {
						$v = isset( $row[ $sub['path'][0] ][ $sub['path'][1] ] ) ? $row[ $sub['path'][0] ][ $sub['path'][1] ] : '';
						$v = mits_sanitize_field( $sub, $v );
						$clean[ $sub['path'][0] ][ $sub['path'][1] ] = $v;
					} else {
						$v = isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : '';
						$v = 'repeater' === $sub['type'] ? mits_json_decode( mits_sanitize_field( $sub, $v ) ) : mits_sanitize_field( $sub, $v );
						$clean[ $sub['key'] ] = $v;
					}
					if ( ( is_string( $v ) && '' !== trim( $v ) ) || ( is_array( $v ) && $v ) || ( is_int( $v ) && $v ) ) {
						$empty = false;
					}
				}
				if ( ! $empty ) {
					$rows[] = $clean;
				}
			}
			return mits_store_list( $field, $rows );
		default:
			return sanitize_text_field( (string) $raw );
	}
}

/**
 * Формат збереження списку: JSON (як на старому сайті) або масив PHP.
 *
 * @param array $field Опис поля.
 * @param array $items Елементи.
 */
function mits_store_list( $field, $items ) {
	if ( isset( $field['storage'] ) && 'array' === $field['storage'] ) {
		return $items;
	}
	return $items ? wp_json_encode( $items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
}
