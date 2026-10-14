<?php
/**
 * Template Name: МІТС — Освіта
 *
 * Сторінка «Освіта»: слайд, логотипи програм, опис, слоган, документи, картки рівнів, новини.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mits_id     = get_queried_object_id();
$mits_desc   = (string) get_post_meta( $mits_id, '_osvita_desc', true );
$mits_slogan = (string) get_post_meta( $mits_id, '_osvita_slogan', true );
$mits_more   = get_post_meta( $mits_id, '_osvita_more_url', true );
$mits_more   = $mits_more ? mits_url( $mits_more ) : mits_page_link_by_path( 'освіта/загальні-положення' );
$mits_tech   = mits_get_json_option( 'mits_osvita_tech_items', 'uk' );
$mits_docs_t = (string) mits_get_option( 'mits_osvita_docs_title', '' );
$mits_docs_t = '' !== $mits_docs_t ? $mits_docs_t : mits_t( 'osvita.docs' );
$mits_docs_d = (string) mits_get_option( 'mits_osvita_docs_desc', '' );
$mits_docs   = mits_get_json_option( 'mits_osvita_docs_items' );
$mits_blocks = mits_get_json_option( 'mits_osvita_level_blocks' );

mits_content_open();
?>
  <!-- ===================== HERO SLIDER ===================== -->
<?php mits_hero( (int) get_post_meta( $mits_id, '_osvita_hero_id', true ) ); ?>
  <!-- ===================== ТЕХНОЛОГІЇ ===================== -->
<?php if ( $mits_tech ) : ?>
  <div class="mits-tech">
    <div class="mits-container">
      <div class="mits-tech__grid">
	<?php
	foreach ( $mits_tech as $mits_item ) :
		$mits_img  = isset( $mits_item['img'] ) ? mits_url( $mits_item['img'] ) : '';
		$mits_link = isset( $mits_item['link'] ) ? mits_url( $mits_item['link'] ) : '';
		if ( ! $mits_img ) {
			continue;
		}
		if ( $mits_link ) :
			?>
        <a href="<?php echo esc_url( $mits_link ); ?>" class="mits-tech__item" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url( $mits_img ); ?>" alt="" class="mits-tech__img"></a>
		<?php else : ?>
        <span class="mits-tech__item"><img src="<?php echo esc_url( $mits_img ); ?>" alt="" class="mits-tech__img"></span>
			<?php
		endif;
	endforeach;
	?>
      </div>
    </div>
  </div>
<?php endif; ?>
  <!-- ===================== КОРОТКИЙ ОПИС ===================== -->
<?php if ( $mits_desc ) : ?>
  <section class="mits-section mits-osvita-lead">
    <div class="mits-container">
      <div class="mits-osvita-desc-card">
        <div class="mits-osvita-desc-card__body"><?php echo wp_kses_post( wpautop( $mits_desc ) ); ?></div>
        <a href="<?php echo esc_url( $mits_more ? $mits_more : '#' ); ?>" class="mits-osvita-desc-card__link"><?php mits_te( 'front.more' ); ?></a>
      </div>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== СЛОГАН ===================== -->
<?php if ( $mits_slogan ) : ?>
  <section class="mits-section mits-osvita-slogan">
    <div class="mits-container">
      <p class="mits-osvita-lead__slogan"><?php echo esc_html( $mits_slogan ); ?></p>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== ДОКУМЕНТИ ===================== -->
<?php if ( $mits_docs_d || $mits_docs ) : ?>
  <section class="mits-section mits-osvita-docs">
    <div class="mits-container">
      <h2 class="mits-osvita-docs__title"><?php echo esc_html( $mits_docs_t ); ?></h2>
	<?php if ( $mits_docs_d ) : ?>
      <div class="mits-osvita-docs__card">
		<?php echo wp_kses_post( wpautop( $mits_docs_d ) ); ?>
      </div>
	<?php endif; ?>
	<?php if ( $mits_docs ) : ?>
      <div class="mits-osvita-docs__list-wrap">
      <ul class="mits-osvita-docs__list">
		<?php
		foreach ( $mits_docs as $mits_doc ) :
			$mits_name = isset( $mits_doc['name'] ) ? trim( $mits_doc['name'] ) : '';
			$mits_url  = isset( $mits_doc['url'] ) ? mits_url( $mits_doc['url'] ) : '';
			if ( '' === $mits_name ) {
				continue;
			}
			?>
        <li class="mits-osvita-docs__item"><?php echo $mits_url ? '<a href="' . esc_url( $mits_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $mits_name ) . '</a>' : esc_html( $mits_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
		<?php endforeach; ?>
      </ul>
      </div>
	<?php endif; ?>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== РІВНІ НАВЧАННЯ ===================== -->
<?php if ( $mits_blocks ) : ?>
  <section class="mits-section mits-level-blocks">
    <div class="mits-container">
      <div class="mits-level-blocks__row">
	<?php
	foreach ( $mits_blocks as $mits_lv ) :
		$mits_vote   = ! empty( $mits_lv['vote_url'] ) ? mits_url( $mits_lv['vote_url'] ) : '';
		$mits_s_url  = ! empty( $mits_lv['schedule']['url'] ) ? mits_url( $mits_lv['schedule']['url'] ) : '';
		$mits_s_name = ! empty( $mits_lv['schedule']['name'] ) ? $mits_lv['schedule']['name'] : mits_t( 'osvita.schedule' );
		$mits_e_url  = ! empty( $mits_lv['exams']['url'] ) ? mits_url( $mits_lv['exams']['url'] ) : '';
		$mits_e_name = ! empty( $mits_lv['exams']['name'] ) ? $mits_lv['exams']['name'] : mits_t( 'osvita.exams' );
		$mits_color  = ! empty( $mits_lv['color'] ) ? $mits_lv['color'] : '#ffffff';
		?>
        <div class="mits-level-card">
          <h3 class="mits-level-card__title" style="color:<?php echo esc_attr( $mits_color ); ?>"><?php echo esc_html( isset( $mits_lv['title'] ) ? $mits_lv['title'] : '' ); ?></h3>
		<?php if ( ! empty( $mits_lv['docs'] ) ) : ?>
          <ul class="mits-level-card__docs">
			<?php
			foreach ( (array) $mits_lv['docs'] as $mits_d ) :
				if ( empty( $mits_d['name'] ) ) {
					continue;
				}
				$mits_d_url = ! empty( $mits_d['url'] ) ? mits_url( $mits_d['url'] ) : '';
				?>
            <li><?php echo $mits_d_url ? '<a href="' . esc_url( $mits_d_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $mits_d['name'] ) . '</a>' : esc_html( $mits_d['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
			<?php endforeach; ?>
          </ul>
		<?php endif; ?>
		<?php if ( $mits_vote ) : ?>
          <a href="<?php echo esc_url( $mits_vote ); ?>" class="mits-level-card__vote-btn"><?php mits_te( 'osvita.vote' ); ?></a>
		<?php endif; ?>
		<?php if ( $mits_s_url || $mits_e_url ) : ?>
          <div class="mits-level-card__schedule">
			<?php if ( $mits_s_url ) : ?>
            <a href="<?php echo esc_url( $mits_s_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $mits_s_name ); ?></a>
			<?php endif; ?>
			<?php if ( $mits_e_url ) : ?>
            <a href="<?php echo esc_url( $mits_e_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $mits_e_name ); ?></a>
			<?php endif; ?>
          </div>
		<?php endif; ?>
        </div>
	<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>
  <!-- ===================== НОВИНИ ===================== -->
<?php mits_news_section(); ?>
<?php
mits_content_close( 'div', true );
mits_contacts_section();
get_footer();
