<?php
/**
 * Template Name: МІТС — Міжнародність
 *
 * Сторінка «Міжнародна діяльність»: слайд, партнер, цифри, курси, документи.
 * Дані — «МІТС: блоки → Міжнародність».
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mits_photo   = (string) mits_get_option( 'mits_intl_photo', '', 'uk' );
$mits_text    = (string) mits_get_option( 'mits_intl_text', '' );
$mits_links   = mits_get_json_option( 'mits_intl_links' );
$mits_stats   = mits_get_json_option( 'mits_intl_stats' );
$mits_c_photo = (string) mits_get_option( 'mits_intl_courses_photo', '', 'uk' );
$mits_c_text  = (string) mits_get_option( 'mits_intl_courses_text', '' );
$mits_slogan  = (string) mits_get_option( 'mits_intl_courses_slogan', '' );
$mits_c_title = (string) mits_get_option( 'mits_intl_courses_title', '' );
$mits_courses = mits_get_json_option( 'mits_intl_courses' );
$mits_docs_t  = (string) mits_get_option( 'mits_intl_docs_title', '' );
$mits_docs    = array_filter( mits_get_json_option( 'mits_intl_docs' ), function ( $d ) {
	return ! empty( $d['name'] ) || ! empty( $d['url'] );
} );
$mits_stats   = array_values( array_filter( $mits_stats, function ( $s ) {
	return '' !== trim( isset( $s['value'] ) ? $s['value'] : '' ) || '' !== trim( isset( $s['label'] ) ? $s['label'] : '' );
} ) );

mits_content_open();
?>
  <!-- ===================== HERO SLIDER ===================== -->
<?php mits_hero( (int) get_option( 'mits_intl_hero_id', 0 ) ); ?>
  <!-- ===================== ПРО ВІДДІЛ: фото + текст ===================== -->
<?php if ( $mits_photo || $mits_text ) : ?>
  <section class="mits-section mits-intl-about">
    <div class="mits-container">
      <div class="mits-intl-about__inner<?php echo ( $mits_photo && $mits_text ) ? ' mits-intl-about__inner--split' : ''; ?>">
	<?php if ( $mits_photo ) : ?>
        <div class="mits-intl-about__photo">
          <img src="<?php echo esc_url( mits_url( $mits_photo ) ); ?>" alt="" class="mits-intl-about__img">
        </div>
	<?php endif; ?>
        <div class="mits-intl-about__text">
          <?php echo wp_kses( $mits_text, mits_kses_inline() ); ?>
	<?php if ( $mits_links ) : ?>
          <ul class="mits-intl-about__links">
		<?php
		foreach ( $mits_links as $mits_link ) :
			$mits_lname = isset( $mits_link['name'] ) ? trim( $mits_link['name'] ) : '';
			$mits_lurl  = isset( $mits_link['url'] ) ? mits_url( $mits_link['url'] ) : '';
			if ( '' === $mits_lname && '' === $mits_lurl ) {
				continue;
			}
			?>
            <li>
			<?php if ( $mits_lurl ) : ?>
              <a href="<?php echo esc_url( $mits_lurl ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html( '' !== $mits_lname ? $mits_lname : $mits_lurl ); ?>
              </a>
			<?php else : ?>
              <span><?php echo esc_html( $mits_lname ); ?></span>
			<?php endif; ?>
            </li>
		<?php endforeach; ?>
          </ul>
	<?php endif; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== СТАТИСТИКА ===================== -->
<?php if ( $mits_stats ) : ?>
  <section class="mits-section mits-intl-stats">
    <div class="mits-container">
	<?php foreach ( array_chunk( $mits_stats, 4 ) as $mits_row ) : ?>
      <div class="mits-intl-stats__row">
		<?php foreach ( $mits_row as $mits_stat ) : ?>
          <div class="mits-intl-stat">
            <span class="mits-intl-stat__value"><?php echo esc_html( isset( $mits_stat['value'] ) ? $mits_stat['value'] : '' ); ?></span>
            <span class="mits-intl-stat__label"><?php echo esc_html( isset( $mits_stat['label'] ) ? $mits_stat['label'] : '' ); ?></span>
          </div>
		<?php endforeach; ?>
      </div>
	<?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== КУРСИ ===================== -->
<?php if ( $mits_courses || $mits_slogan || $mits_c_title || $mits_c_photo || $mits_c_text ) : ?>
  <section class="mits-section mits-intl-courses">
    <div class="mits-container">
	<?php if ( $mits_c_photo || $mits_c_text ) : ?>
      <div class="mits-intl-courses__intro">
		<?php if ( $mits_c_photo ) : ?>
        <div class="mits-intl-courses__intro-photo">
          <img src="<?php echo esc_url( mits_url( $mits_c_photo ) ); ?>" alt="" loading="lazy">
        </div>
		<?php endif; ?>
		<?php if ( $mits_c_text ) : ?>
        <div class="mits-intl-courses__intro-text">
          <?php echo wp_kses( $mits_c_text, mits_kses_inline() ); ?>
        </div>
		<?php endif; ?>
      </div>
	<?php endif; ?>
	<?php if ( $mits_slogan ) : ?>
      <p class="mits-intl-courses__slogan"><?php echo esc_html( $mits_slogan ); ?></p>
	<?php endif; ?>
	<?php if ( $mits_c_title ) : ?>
      <h2 class="mits-section__title"><?php echo esc_html( $mits_c_title ); ?></h2>
	<?php endif; ?>
	<?php if ( $mits_courses ) : ?>
      <div class="mits-advantages__accordion" id="mits-intl-courses-acc">
		<?php
		foreach ( $mits_courses as $mits_course ) {
			$mits_name = isset( $mits_course['name'] ) ? trim( $mits_course['name'] ) : '';
			$mits_desc = isset( $mits_course['desc'] ) ? trim( $mits_course['desc'] ) : '';
			if ( '' !== $mits_name || '' !== $mits_desc ) {
				mits_accordion_item( $mits_name, $mits_desc );
			}
		}
		?>
      </div>
	<?php endif; ?>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== ДОКУМЕНТИ ===================== -->
<?php if ( $mits_docs ) : ?>
  <section class="mits-section mits-intl-docs">
    <div class="mits-container">
	<?php if ( $mits_docs_t ) : ?>
      <h2 class="mits-section__title"><?php echo esc_html( $mits_docs_t ); ?></h2>
	<?php endif; ?>
      <ul class="mits-intl-docs__list">
	<?php foreach ( $mits_docs as $mits_doc ) : ?>
        <li class="mits-intl-docs__item">
          <a class="mits-intl-docs__a" href="<?php echo esc_url( mits_url( isset( $mits_doc['url'] ) ? $mits_doc['url'] : '' ) ); ?>" target="_blank" rel="noopener noreferrer">
            <span class="mits-intl-docs__icon">&#128196;</span>
            <span class="mits-intl-docs__name"><?php echo esc_html( isset( $mits_doc['name'] ) ? $mits_doc['name'] : '' ); ?></span>
            <span class="mits-intl-docs__dl">&#8595;</span>
          </a>
        </li>
	<?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endif; ?>
<?php
mits_content_close( 'div', true );
mits_contacts_section();
get_footer();
