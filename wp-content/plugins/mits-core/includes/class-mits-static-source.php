<?php
/**
 * Читання статичної копії сайту (reference/static-site/): тексти, яких немає в базі
 * (англійська версія, сторінка «Наука», блок «Якісне… IT-навчання» тощо).
 *
 * @package MITS_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Сторінка статичної копії як DOM + XPath з короткими помічниками.
 */
class Mits_Static_Page {

	/** @var DOMDocument */
	public $dom;

	/** @var DOMXPath */
	public $xpath;

	/** @var string Шлях URL сторінки (наприклад /en/admission/). */
	public $path;

	/**
	 * @param string $html HTML сторінки.
	 * @param string $path Шлях URL.
	 */
	public function __construct( $html, $path ) {
		$this->path = $path;
		$this->dom  = new DOMDocument();
		libxml_use_internal_errors( true );
		$this->dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT );
		libxml_clear_errors();
		$this->xpath = new DOMXPath( $this->dom );
	}

	/**
	 * XPath-вираз для класу.
	 *
	 * @param string $class Клас.
	 */
	public static function cls( $class ) {
		return "contains(concat(' ', normalize-space(@class), ' '), ' " . $class . " ')";
	}

	/**
	 * Вузли за CSS-подібним селектором (лише «.клас», «тег.клас», «#id» і нащадки через пробіл).
	 *
	 * @param string       $selector Селектор.
	 * @param DOMNode|null $context  Контекст.
	 * @return DOMNode[]
	 */
	public function all( $selector, $context = null ) {
		$parts = preg_split( '/\s+/', trim( $selector ) );
		$xp    = $context ? '.' : '';
		foreach ( $parts as $part ) {
			if ( '>' === $part ) {
				$xp .= '/';
				continue;
			}
			$tag  = '*';
			$cond = array();
			if ( preg_match( '/^([a-z0-9]+)?((?:[.#][\w-]+)*)$/i', $part, $m ) ) {
				$tag = '' !== $m[1] ? $m[1] : '*';
				preg_match_all( '/([.#])([\w-]+)/', $m[2], $mm, PREG_SET_ORDER );
				foreach ( $mm as $x ) {
					$cond[] = '.' === $x[1] ? self::cls( $x[2] ) : "@id='" . $x[2] . "'";
				}
			}
			$xp .= ( '/' === substr( $xp, -1 ) ? '' : '//' ) . $tag . ( $cond ? '[' . implode( ' and ', $cond ) . ']' : '' );
		}
		$list = $this->xpath->query( $xp, $context );
		return $list ? iterator_to_array( $list ) : array();
	}

	/**
	 * Перший вузол за селектором.
	 *
	 * @param string       $selector Селектор.
	 * @param DOMNode|null $context  Контекст.
	 * @return DOMNode|null
	 */
	public function one( $selector, $context = null ) {
		$all = $this->all( $selector, $context );
		return $all ? $all[0] : null;
	}

	/**
	 * Текст вузла (пробіли нормалізовано).
	 *
	 * @param string|DOMNode|null $selector Селектор або вузол.
	 * @param DOMNode|null        $context  Контекст.
	 */
	public function text( $selector, $context = null ) {
		$node = $selector instanceof DOMNode ? $selector : ( $selector ? $this->one( $selector, $context ) : null );
		return $node ? self::clean_text( $node->textContent ) : '';
	}

	/**
	 * Текст без зайвих пробілів по краях і з одинарними пробілами всередині рядків.
	 *
	 * @param string $text Текст.
	 */
	public static function clean_text( $text ) {
		$text = str_replace( "\xc2\xa0", ' ', $text );
		$lines = array_map( function ( $l ) {
			return trim( preg_replace( '/[ \t]+/u', ' ', $l ) );
		}, preg_split( '/\R/u', $text ) );
		return trim( implode( "\n", array_filter( $lines, 'strlen' ) ) );
	}

	/**
	 * Внутрішній HTML вузла.
	 *
	 * @param DOMNode|null $node Вузол.
	 */
	public function inner_html( $node ) {
		if ( ! $node ) {
			return '';
		}
		$html = '';
		foreach ( $node->childNodes as $child ) {
			$html .= $this->dom->saveHTML( $child );
		}
		return trim( $html );
	}

	/**
	 * Атрибут вузла за селектором.
	 *
	 * @param string       $selector Селектор.
	 * @param string       $attr     Атрибут.
	 * @param DOMNode|null $context  Контекст.
	 */
	public function attr( $selector, $attr, $context = null ) {
		$node = $this->one( $selector, $context );
		return ( $node instanceof DOMElement ) ? $node->getAttribute( $attr ) : '';
	}
}

/**
 * Доступ до сторінок статичної копії.
 */
class Mits_Static_Source {

	/** @var string Корінь статичної копії. */
	public $root;

	/** @var array Кеш сторінок. */
	private $cache = array();

	/**
	 * @param string $root Каталог reference/static-site.
	 */
	public function __construct( $root ) {
		$this->root = rtrim( $root, '/' );
	}

	/**
	 * Сторінка за шляхом URL (кирилиця допускається як є або в %-кодуванні).
	 *
	 * @param string $path Шлях, наприклад «/en/admission/».
	 * @return Mits_Static_Page|null
	 */
	public function page( $path ) {
		$path = '/' . trim( rawurldecode( $path ), '/' );
		if ( isset( $this->cache[ $path ] ) ) {
			return $this->cache[ $path ];
		}
		$file = $this->root . ( '/' === $path ? '' : $path ) . '/index.html';
		if ( ! is_readable( $file ) ) {
			return $this->cache[ $path ] = null;
		}
		return $this->cache[ $path ] = new Mits_Static_Page( (string) file_get_contents( $file ), $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	/**
	 * Шлях до файлу завантажень статичної копії.
	 *
	 * @param string $relative Відносний шлях у uploads.
	 */
	public function upload_path( $relative ) {
		return $this->root . '/wp-content/uploads/' . ltrim( $relative, '/' );
	}
}
