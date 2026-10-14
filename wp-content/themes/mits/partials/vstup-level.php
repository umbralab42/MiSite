<?php
/**
 * Вміст підсторінки вступу (бакалаврат, магістратура, аспірантура):
 * картка з текстом сторінки, кроки, опис, предмети, алгоритм, поради, новини, контакти.
 *
 * Дані — із запису «Освітні рівні (Вступ)», що посилається на цю сторінку.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

$mits_id    = get_queried_object_id();
$mits_level = mits_level_for_page( $mits_id );
$mits_l     = array(
	'steps'          => array(),
	'description'    => '',
	'subjects_title' => '',
	'subjects'       => array(),
	'subject_alt'    => '',
	'algo_title'     => '',
	'algorithm'      => array(),
	'tips_title'     => '',
	'tips'           => array(),
);
if ( $mits_level ) {
	$mits_l = array(
		'steps'          => mits_get_meta_list( $mits_level, '_edu_level_steps' ),
		'description'    => (string) get_post_meta( $mits_level, '_edu_level_description', true ),
		'subjects_title' => (string) get_post_meta( $mits_level, '_edu_level_subjects_title', true ),
		'subjects'       => mits_get_meta_list( $mits_level, '_edu_level_subjects' ),
		'subject_alt'    => (string) get_post_meta( $mits_level, '_edu_level_subject_alt', true ),
		'algo_title'     => (string) get_post_meta( $mits_level, '_edu_level_algorithm_title', true ),
		'algorithm'      => mits_get_meta_list( $mits_level, '_edu_level_algorithm' ),
		'tips_title'     => (string) get_post_meta( $mits_level, '_edu_level_tips_title', true ),
		'tips'           => mits_get_meta_list( $mits_level, '_edu_level_tips' ),
	);
}

mits_content_open();
?>
  <!-- ===================== HERO SLIDER ===================== -->
<?php mits_hero( (int) get_post_meta( $mits_id, '_vstup_sub_hero_id', true ) ); ?>
  <!-- ===================== ЗМІСТ СТОРІНКИ ===================== -->
<?php
$mits_content = '';
if ( have_posts() ) {
	the_post();
	$mits_content = get_the_content();
}
if ( trim( wp_strip_all_tags( $mits_content ) ) ) :
	?>
  <section class="mits-section mits-vstup-sub-content">
    <div class="mits-container">
      <div class="mits-vstup-sub-content__card">
        <div class="mits-vstup-sub-content__body">
	<?php echo apply_filters( 'the_content', $mits_content ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
      </div>
    </div><!-- /.mits-container -->
  </section>
<?php endif; ?>
  <!-- ===================== КРОКИ ===================== -->
<?php if ( $mits_l['steps'] ) : ?>
  <div class="mits-steps">
    <div class="mits-container">
      <div class="mits-steps__flow">
	<?php foreach ( array_values( $mits_l['steps'] ) as $mits_i => $mits_step ) : ?>
		<?php if ( $mits_i > 0 ) : ?><span class="mits-steps__arrow" aria-hidden="true">&rarr;</span><?php endif; ?>
        <span class="mits-steps__item"><?php echo esc_html( $mits_step ); ?></span>
	<?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
  <!-- ===================== КОРОТКИЙ ОПИС ===================== -->
<?php if ( trim( wp_strip_all_tags( $mits_l['description'] ) ) ) : ?>
  <section class="mits-section mits-vstup-sub-desc">
    <div class="mits-container">
      <?php echo wp_kses_post( $mits_l['description'] ); ?>
    </div>
  </section>
<?php endif; ?>
<?php mits_content_close( 'div', true ); ?>
<!-- ===================== ПРЕДМЕТИ ===================== -->
<?php if ( $mits_l['subjects'] ) : ?>
<section class="mits-section mits-subjects">
  <div class="mits-container">
	<?php if ( $mits_l['subjects_title'] ) : ?>
    <p class="mits-subjects__label"><?php echo esc_html( $mits_l['subjects_title'] ); ?></p>
	<?php endif; ?>
    <div class="mits-subjects__grid">
	<?php foreach ( $mits_l['subjects'] as $mits_subject ) : ?>
      <span class="mits-subjects__item"><?php echo esc_html( $mits_subject ); ?></span>
	<?php endforeach; ?>
    </div>
	<?php if ( $mits_l['subject_alt'] ) : ?>
    <p class="mits-subjects__note"><?php echo esc_html( $mits_l['subject_alt'] ); ?></p>
	<?php endif; ?>
  </div>
</section>
<?php endif; ?>
<!-- ===================== АЛГОРИТМ ВСТУПУ ===================== -->
<?php if ( $mits_l['algorithm'] ) : ?>
<section class="mits-section mits-algo">
  <div class="mits-container">
    <div class="mits-algo__cta">
      <a href="#contact-form" class="mits-btn mits-btn--primary"><?php mits_te( 'nav.cta' ); ?></a>
    </div>
    <h2 class="mits-algo__title"><?php echo esc_html( $mits_l['algo_title'] ? $mits_l['algo_title'] : mits_t( 'vstup.algorithm' ) ); ?></h2>
    <div class="mits-algo__card">
      <ol class="mits-algo__list">
	<?php foreach ( $mits_l['algorithm'] as $mits_step ) : ?>
        <li class="mits-algo__item"><div class="mits-algo__item-body"><?php echo wp_kses( $mits_step, mits_kses_inline() ); ?></div></li>
	<?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- ===================== ВАЖЛИВІ ПОРАДИ ===================== -->
<?php if ( $mits_l['tips'] ) : ?>
<section class="mits-section mits-tips">
  <div class="mits-container">
    <h2 class="mits-tips__title"><?php echo esc_html( $mits_l['tips_title'] ? $mits_l['tips_title'] : mits_t( 'vstup.tips' ) ); ?></h2>
    <ul class="mits-tips__list">
	<?php foreach ( $mits_l['tips'] as $mits_tip ) : ?>
      <li class="mits-tips__item">
        <img class="mits-tips__check" src="<?php echo esc_url( get_template_directory_uri() . '/images/tick.png' ); ?>" width="32" height="32" alt="" aria-hidden="true">
        <span><?php echo esc_html( $mits_tip ); ?></span>
      </li>
	<?php endforeach; ?>
    </ul>
    <div class="mits-tips__cta">
      <a href="#contact-form" class="mits-btn mits-btn--primary"><?php mits_te( 'nav.cta' ); ?></a>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- ===================== НОВИНИ ===================== -->
<?php mits_news_section(); ?>
<?php mits_contacts_section(); ?>
