<?php
/**
 * Template Name: МІТС — Наука
 *
 * Сторінка «Наука» (за макетом Figma): IPST, Micro CAD, наукові гуртки, стартапи, конкурси.
 * Усі тексти, цифри, картки й вкладки редагуються в полях цієї сторінки.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mits_id = get_queried_object_id();

/**
 * Значення поля сторінки «Наука».
 *
 * @param string $key Ключ без префікса _nauka_.
 */
$mits_f = function ( $key ) use ( $mits_id ) {
	return (string) get_post_meta( $mits_id, '_nauka_' . $key, true );
};
$mits_list = function ( $key ) use ( $mits_id ) {
	return mits_get_meta_list( $mits_id, '_nauka_' . $key );
};

/**
 * Абзаци-посилання в картці.
 *
 * @param array  $links  [['text','url'],…].
 * @param string $anchor Якір секції для порожніх посилань.
 * @param string $class  Клас абзацу.
 */
$mits_links = function ( $links, $anchor, $class = '' ) {
	foreach ( $links as $link ) {
		if ( empty( $link['text'] ) ) {
			continue;
		}
		$url = ! empty( $link['url'] ) ? mits_url( $link['url'] ) : '#' . $anchor;
		printf( '        <p%1$s><a class="mits-nauka__link" href="%2$s">%3$s</a></p>' . "\n", $class ? ' class="' . esc_attr( $class ) . '"' : '', esc_url( $url ), esc_html( $link['text'] ) );
	}
};

/**
 * Акордеон.
 *
 * @param array $items [['title','text'],…].
 * @param int   $n     Номер акордеона на сторінці (для id).
 * @param string $extra Додатковий клас.
 */
$mits_accordion = function ( $items, $n, $extra = '' ) {
	if ( ! $items ) {
		return;
	}
	echo '      <div class="mits-advantages__accordion mits-nauka__accordion' . ( $extra ? ' ' . esc_attr( $extra ) : '' ) . '" data-nauka-accordion>' . "\n";
	foreach ( array_values( $items ) as $i => $item ) {
		$id   = 'nauka-acc-' . $n . '-' . ( $i + 1 );
		$text = isset( $item['text'] ) ? trim( $item['text'] ) : '';
		echo '        <div class="mits-adv-item">' . "\n";
		printf( '          <button type="button" class="mits-adv-item__header" aria-expanded="false" aria-controls="%1$s"><span>%2$s</span><span class="mits-adv-item__chevron" aria-hidden="true">›</span></button>' . "\n", esc_attr( $id ), esc_html( isset( $item['title'] ) ? $item['title'] : '' ) );
		printf( '          <div class="mits-adv-item__body" id="%1$s"><div class="mits-adv-item__body-inner">%2$s</div></div>' . "\n", esc_attr( $id ), wp_kses_post( wpautop( $text ) ) );
		echo "        </div>\n";
	}
	echo "      </div>\n";
};

/**
 * Зображення з розмірами з медіатеки (якщо вдається визначити).
 *
 * @param string $url URL зображення.
 */
$mits_img_attrs = function ( $url ) {
	$id = attachment_url_to_postid( mits_url( $url ) );
	if ( $id ) {
		$meta = wp_get_attachment_metadata( $id );
		if ( ! empty( $meta['width'] ) ) {
			return sprintf( ' width="%1$d" height="%2$d"', (int) $meta['width'], (int) $meta['height'] );
		}
	}
	return '';
};

$mits_card = function ( $html ) {
	if ( '' !== trim( wp_strip_all_tags( $html ) ) ) {
		echo wp_kses_post( wpautop( $html ) );
	}
};

mits_content_open();
?>
  <!-- ===================== HERO SLIDER ===================== -->
<?php
mits_hero(
	(int) get_option( 'mits_nauka_hero_id', 0 ),
	array(
		'gradient' => 'gradient-nauka',
		'icon'     => (string) file_get_contents( get_template_directory() . '/images/nauka-icon.svg' ), // phpcs:ignore WordPress.WP.AlternativeFunctions
		'social'   => true,
	)
);
?>
<?php if ( $mits_f( 'ipst_title' ) ) : ?>
  <!-- ===================== НАУКА: IPST ===================== -->
  <section class="mits-section mits-nauka mits-nauka--glow-teal" id="ipst">
    <div class="mits-container">
      <h2 class="mits-nauka__title"><?php echo esc_html( $mits_f( 'ipst_title' ) ); ?></h2>
	<?php if ( $mits_f( 'ipst_lead' ) ) : ?>
      <p class="mits-nauka__lead"><?php echo esc_html( $mits_f( 'ipst_lead' ) ); ?></p>
	<?php endif; ?>
      <div class="mits-nauka__card">
        <?php $mits_card( $mits_f( 'ipst_text' ) ); ?>
<?php $mits_links( $mits_list( 'ipst_links' ), 'ipst' ); ?>
      </div>
	<?php if ( $mits_list( 'ipst_posters' ) ) : ?>
      <div class="mits-nauka__posters">
		<?php
		foreach ( $mits_list( 'ipst_posters' ) as $mits_poster ) :
			if ( empty( $mits_poster['img'] ) ) {
				continue;
			}
			$mits_src = mits_url( $mits_poster['img'] );
			?>
        <a class="mits-nauka__poster" href="<?php echo esc_url( $mits_src ); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url( $mits_src ); ?>"<?php echo $mits_img_attrs( $mits_poster['img'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?> loading="lazy" alt="<?php echo esc_attr( isset( $mits_poster['alt'] ) ? $mits_poster['alt'] : '' ); ?>"></a>
		<?php endforeach; ?>
      </div>
	<?php endif; ?>
    </div>
  </section>
<?php endif; ?>
<?php if ( $mits_f( 'microcad_title' ) ) : ?>
  <!-- ===================== НАУКА: MICRO CAD ===================== -->
  <section class="mits-section mits-nauka" id="microcad">
    <div class="mits-container">
      <h2 class="mits-nauka__title mits-nauka__title--plain"><?php echo esc_html( $mits_f( 'microcad_title' ) ); ?></h2>
      <div class="mits-nauka__card">
        <?php $mits_card( $mits_f( 'microcad_text' ) ); ?>
<?php $mits_links( $mits_list( 'microcad_links' ), 'microcad' ); ?>
      </div>
	<?php if ( $mits_list( 'microcad_stats' ) ) : ?>
      <div class="mits-nauka__stats">
		<?php foreach ( $mits_list( 'microcad_stats' ) as $mits_stat ) : ?>
        <div class="mits-nauka__stat"><span class="mits-nauka__stat-value"><?php echo esc_html( isset( $mits_stat['value'] ) ? $mits_stat['value'] : '' ); ?></span><span class="mits-nauka__stat-label"><?php echo esc_html( isset( $mits_stat['label'] ) ? $mits_stat['label'] : '' ); ?></span></div>
		<?php endforeach; ?>
      </div>
	<?php endif; ?>
	<?php if ( $mits_f( 'microcad_dirs_title' ) ) : ?>
      <h3 class="mits-nauka__subtitle"><?php echo esc_html( $mits_f( 'microcad_dirs_title' ) ); ?></h3>
	<?php endif; ?>
<?php $mits_accordion( $mits_list( 'microcad_dirs' ), 1 ); ?>
<?php $mits_links( $mits_list( 'microcad_more' ), 'microcad', 'mits-nauka__more' ); ?>
    </div>
  </section>
<?php endif; ?>
<?php if ( $mits_f( 'clubs_title' ) ) : ?>
  <!-- ===================== НАУКА: ГУРТКИ ===================== -->
  <section class="mits-section mits-nauka mits-nauka--glow-pink" id="hurtky">
    <div class="mits-container">
      <h2 class="mits-nauka__title"><?php echo esc_html( $mits_f( 'clubs_title' ) ); ?></h2>
      <div class="mits-nauka__card">
        <?php $mits_card( $mits_f( 'clubs_text' ) ); ?>
<?php $mits_links( $mits_list( 'clubs_links' ), 'hurtky' ); ?>
      </div>
<?php $mits_accordion( $mits_list( 'clubs' ), 2, 'mits-nauka__accordion--after-card' ); ?>
    </div>
  </section>
<?php endif; ?>
<?php if ( $mits_f( 'startups_title' ) ) : ?>
  <!-- ===================== НАУКА: СТАРТАПИ ===================== -->
  <section class="mits-section mits-nauka mits-nauka--glow-green" id="startups">
    <div class="mits-container">
      <h2 class="mits-nauka__title"><?php echo esc_html( $mits_f( 'startups_title' ) ); ?></h2>
      <div class="mits-nauka__card">
        <?php $mits_card( $mits_f( 'startups_text' ) ); ?>
      </div>
	<?php if ( $mits_list( 'startups' ) ) : ?>
      <div class="mits-nauka__cards">
		<?php foreach ( $mits_list( 'startups' ) as $mits_s ) : ?>
        <div class="mits-nauka__item">
          <div class="mits-nauka__item-img"><?php if ( ! empty( $mits_s['img'] ) ) : ?><img src="<?php echo esc_url( mits_url( $mits_s['img'] ) ); ?>"<?php echo $mits_img_attrs( $mits_s['img'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?> loading="lazy" alt="<?php echo esc_attr( isset( $mits_s['title'] ) ? $mits_s['title'] : '' ); ?>"><?php endif; ?></div>
          <p class="mits-nauka__item-title"><a class="mits-nauka__link" href="<?php echo esc_url( ! empty( $mits_s['url'] ) ? mits_url( $mits_s['url'] ) : '#startups' ); ?>"><?php echo esc_html( isset( $mits_s['title'] ) ? $mits_s['title'] : '' ); ?></a></p>
          <p class="mits-nauka__item-text"><?php echo esc_html( isset( $mits_s['text'] ) ? $mits_s['text'] : '' ); ?></p>
        </div>
		<?php endforeach; ?>
      </div>
	<?php endif; ?>
    </div>
  </section>
<?php endif; ?>
<?php if ( $mits_f( 'contests_title' ) ) : ?>
  <!-- ===================== НАУКА: КОНКУРСИ ===================== -->
  <section class="mits-section mits-nauka mits-nauka--glow-orange" id="konkursy">
    <div class="mits-container">
      <h2 class="mits-nauka__title"><?php echo esc_html( $mits_f( 'contests_title' ) ); ?></h2>
      <div class="mits-nauka__card">
        <?php $mits_card( $mits_f( 'contests_text' ) ); ?>
      </div>
	<?php
	$mits_years = array_values( $mits_list( 'contests_years' ) );
	if ( $mits_years ) :
		$mits_ids = array_map( function ( $y ) {
			return 'nauka-year-' . ( preg_match( '/\d{4}/', isset( $y['label'] ) ? $y['label'] : '', $m ) ? $m[0] : sanitize_title( $y['label'] ) );
		}, $mits_years );
		?>
		<?php if ( $mits_f( 'contests_years_title' ) ) : ?>
      <h3 class="mits-nauka__subtitle"><?php echo esc_html( $mits_f( 'contests_years_title' ) ); ?></h3>
		<?php endif; ?>
      <div class="mits-nauka__tabs" role="tablist" data-nauka-tabs>
		<?php foreach ( $mits_years as $mits_i => $mits_year ) : ?>
        <button type="button" class="mits-nauka__tab<?php echo 0 === $mits_i ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === $mits_i ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $mits_ids[ $mits_i ] ); ?>"<?php echo 0 === $mits_i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $mits_year['label'] ); ?></button>
		<?php endforeach; ?>
      </div>
		<?php
		foreach ( $mits_years as $mits_i => $mits_year ) :
			$mits_items = array_values( array_filter( array_map( function ( $it ) {
				return is_array( $it ) ? ( isset( $it['text'] ) ? trim( $it['text'] ) : '' ) : trim( (string) $it );
			}, isset( $mits_year['items'] ) ? (array) $mits_year['items'] : array() ) ) );
			?>
      <div class="mits-nauka__panel" role="tabpanel" id="<?php echo esc_attr( $mits_ids[ $mits_i ] ); ?>"<?php echo 0 === $mits_i ? '' : ' hidden'; ?>>
			<?php if ( $mits_items ) : ?>
        <ul class="mits-nauka__results">
				<?php foreach ( $mits_items as $mits_item ) : ?>
          <li><?php echo wp_kses( $mits_item, mits_kses_inline() ); ?></li>
				<?php endforeach; ?>
        </ul>
			<?php elseif ( ! empty( $mits_year['empty'] ) ) : ?>
        <p class="mits-nauka__empty"><?php echo esc_html( $mits_year['empty'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $mits_year['more_text'] ) ) : ?>
        <p class="mits-nauka__more"><a class="mits-nauka__link" href="<?php echo esc_url( ! empty( $mits_year['more_url'] ) ? mits_url( $mits_year['more_url'] ) : '#konkursy' ); ?>"><?php echo esc_html( $mits_year['more_text'] ); ?></a></p>
			<?php endif; ?>
      </div>
		<?php endforeach; ?>
	<?php endif; ?>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== НОВИНИ ===================== -->
<?php mits_news_section(); ?>
<?php
mits_content_close( 'div', true );
mits_contacts_section();
get_footer();
