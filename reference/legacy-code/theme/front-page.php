<?php
/**
 * Custom front page template – MITS Figma mockup (dark theme).
 *
 * @package Betheme
 */

get_header();
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

<?php if ( function_exists( 'mits_hero_render' ) ) mits_hero_render(); ?>

<!-- ===================== ЧОМУ МИ? ===================== -->
<section class="mits-section mits-why">
  <div class="mits-container">
    <h2 class="mits-section__title">
      <?php echo esc_html( function_exists( 'mits_why_get' ) ? mits_why_get( 'title', 'Чому ми?' ) : 'Чому ми?' ); ?>
    </h2>
    <div class="mits-why__text">
      <p><?php echo wp_kses_post( function_exists( 'mits_why_get' ) ? mits_why_get( 'text', '' ) : '' ); ?></p>
    </div>
    <div class="mits-stats">
      <?php
      $why_defaults = array(
        array( 'num' => '800+', 'label_uk' => 'Наукових публікацій',  'label_en' => 'Scientific publications' ),
        array( 'num' => '15+',  'label_uk' => 'Років досвіду',         'label_en' => 'Years of experience' ),
        array( 'num' => '150+', 'label_uk' => 'Студентів щорічно',    'label_en' => 'Students per year' ),
        array( 'num' => '600+', 'label_uk' => 'Випускників',           'label_en' => 'Graduates' ),
      );
      for ( $i = 1; $i <= 4; $i++ ) :
        $d   = $why_defaults[ $i - 1 ];
        $num = function_exists( 'mits_why_get' ) ? mits_why_get( 'stat' . $i . '_num', $d['num'] ) : $d['num'];
        $lbl = function_exists( 'mits_why_get' ) ? mits_why_get( 'stat' . $i . '_label', $d['label_uk'] ) : $d['label_uk'];
      ?>
      <div class="mits-stat">
        <span class="mits-stat__number"><?php echo esc_html( $num ); ?></span>
        <span class="mits-stat__label"><?php echo esc_html( $lbl ); ?></span>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ===================== ПЕРЕВАГИ ===================== -->
<?php
$_adv_lang  = function_exists( 'pll_current_language' ) ? pll_current_language() : 'uk';
$_adv_opts  = get_option( 'mits_advantages', array() );
$_adv_sfx   = ( $_adv_lang !== 'uk' ) ? '_' . $_adv_lang : '';
$_adv_title = $_adv_opts[ 'title' . $_adv_sfx ] ?? 'Переваги';
$_adv_q     = new WP_Query( array(
    'post_type'      => 'mits_advantage',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'lang'           => $_adv_lang,
    'no_found_rows'  => true,
) );
?>
<section class="mits-section mits-advantages">
  <div class="mits-container">
    <h2 class="mits-section__title"><?php echo esc_html( $_adv_title ); ?></h2>
    <div class="mits-advantages__accordion">
      <?php while ( $_adv_q->have_posts() ) : $_adv_q->the_post();
        $_adv_details = get_post_meta( get_the_ID(), '_adv_details', true );
      ?>
      <div class="mits-adv-item">
        <button class="mits-adv-item__header" aria-expanded="false"<?php if ( ! $_adv_details ) echo ' data-no-content'; ?>>
          <span><?php the_title(); ?></span>
          <?php if ( $_adv_details ) : ?>
          <span class="mits-adv-item__chevron">›</span>
          <?php endif; ?>
        </button>
        <?php if ( $_adv_details ) : ?>
        <div class="mits-adv-item__body">
          <div class="mits-adv-item__body-inner"><?php echo wp_kses_post( $_adv_details ); ?></div>
        </div>
        <?php endif; ?>
      </div>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<script>
(function () {
  document.querySelectorAll('.mits-adv-item__header:not([data-no-content])').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var isOpen = this.getAttribute('aria-expanded') === 'true';
      document.querySelectorAll('.mits-adv-item__header').forEach(function (b) {
        b.setAttribute('aria-expanded', 'false');
      });
      if (!isOpen) { this.setAttribute('aria-expanded', 'true'); }
    });
  });
}());
</script>

<!-- ===================== IT НАВЧАННЯ ===================== -->
<section class="mits-section mits-education">
  <div class="mits-container">
    <h2 class="mits-education__title">
      <span class="grad-orange">Якісне, фундаментальне</span><br>
      <span class="grad-cyan">та online IT-навчання у Харкові</span>
    </h2>
    <div class="mits-education__card">
      <p><strong>Спеціальність: F7 КОМП'ЮТЕРНА ІНЖЕНЕРІЯ</strong></p>
      <p>Конкурсна пропозиція:</p>
      <ul>
        <li>ПРИКЛАДНА КОМП'ЮТЕРНА ІНЖЕНЕРІЯ</li>
      </ul>
      <p>Вибірковий блок дисциплін:</p>
      <ul>
        <li>МУЛЬТИМЕДІЙНІ ІНФОРМАЦІЙНІ ТЕХНОЛОГІЇ І СИСТЕМИ</li>
        <li>ПРОГРАМНЕ ЗАБЕЗПЕЧЕННЯ ІНФОРМАЦІЙНИХ ТЕХНОЛОГІЙ ІНТЕРНЕТУ РЕЧЕЙ</li>
      </ul>
    </div>
    <div class="mits-education__cta">
      <a href="#contact-form" class="mits-btn mits-btn--primary">Консультація</a>
    </div>
  </div>
</section>

<!-- ===================== ДІЯЛЬНІСТЬ ===================== -->
<?php
$_act_lang   = function_exists( 'pll_current_language' ) ? pll_current_language() : 'uk';
$_act_opts   = get_option( 'mits_activities', array() );
$_act_sfx    = ( $_act_lang !== 'uk' ) ? '_' . $_act_lang : '';
$_act_title  = $_act_opts[ 'title'    . $_act_sfx ] ?? 'Діяльність';
$_act_cta    = $_act_opts[ 'cta_text' . $_act_sfx ] ?? 'Навчальна програма';
$_act_cta_url = $_act_opts['cta_url'] ?? '#';

$_act_query = new WP_Query( array(
    'post_type'      => 'mits_activity',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'lang'           => $_act_lang,
    'no_found_rows'  => true,
) );
?>
<section class="mits-section mits-activities">
  <div class="mits-container">
    <h2 class="mits-section__title"><?php echo esc_html( $_act_title ); ?></h2>
    <div class="mits-cards">
      <?php while ( $_act_query->have_posts() ) : $_act_query->the_post();
        $_id      = get_the_ID();
        $icon_url = get_post_meta( $_id, '_act_icon_url', true );
        $icon_bg  = get_post_meta( $_id, '_act_icon_bg',  true );
        $act_btn  = get_post_meta( $_id, '_act_btn',      true ) ?: 'Детальніше';
        $act_url  = get_post_meta( $_id, '_act_url',      true ) ?: '#';
        $act_desc = get_post_meta( $_id, '_act_desc',     true );
      ?>
      <div class="mits-card">
        <div class="mits-card__header">
          <?php if ( $icon_url || $icon_bg ) : ?>
          <div class="mits-card__icon"<?php if ( $icon_bg ) echo ' style="background-color:' . esc_attr( $icon_bg ) . ';"'; ?>>
            <?php if ( $icon_url ) : ?>
              <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <h3><?php the_title(); ?></h3>
        </div>
        <?php if ( $act_desc ) : ?>
        <p><?php echo wp_kses_post( $act_desc ); ?></p>
        <?php endif; ?>
        <a href="<?php echo esc_url( $act_url ); ?>" class="mits-btn mits-btn--outline"><?php echo esc_html( $act_btn ); ?></a>
      </div>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php if ( $_act_cta ) : ?>
    <div class="mits-activities__cta">
      <a href="<?php echo esc_url( $_act_cta_url ); ?>" class="mits-btn mits-btn--primary"><?php echo esc_html( $_act_cta ); ?></a>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
/* ===================== НОВИНИ ===================== */
$mits_news = new WP_Query( array(
  'post_type'      => 'news',
  'posts_per_page' => 3,
  'post_status'    => 'publish',
  'orderby'        => 'date',
  'order'          => 'DESC',
) );
if ( $mits_news->have_posts() ) : ?>
<section class="mits-section mits-news">
  <div class="mits-container">
    <h2 class="mits-section__title">Новини</h2>
    <div class="mits-news__grid">
      <?php while ( $mits_news->have_posts() ) : $mits_news->the_post(); ?>
      <div class="mits-news__item">
        <?php if ( has_post_thumbnail() ) : ?>
          <a href="<?php the_permalink(); ?>" class="mits-news__img-link">
            <div class="mits-news__img"><?php the_post_thumbnail( 'medium_large' ); ?></div>
          </a>
        <?php else : ?>
          <div class="mits-news__img mits-news__img--placeholder"></div>
        <?php endif; ?>
        <div class="mits-news__meta">
          <span class="mits-news__date"><?php echo get_the_date( 'd.m.Y' ); ?></span>
        </div>
        <h3 class="mits-news__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="mits-news__excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
      </div>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>


</div><!-- #mits-front -->
</div><!-- .entry-content -->
</div><!-- .sections_group -->
</div><!-- .content_wrapper -->
</div><!-- #Content -->

<!-- ===================== КОНТАКТИ ===================== -->
<?php
  $contacts_id = 24;
  if ( function_exists( 'pll_get_post' ) ) {
      $translated = pll_get_post( $contacts_id );
      if ( $translated ) $contacts_id = $translated;
  }
  $contacts_page = get_post( $contacts_id );
  if ( $contacts_page ) :
?>
<section class="mits-section mits-contacts" id="mits-contacts">
  <div class="mits-container">
    <h2 class="mits-page__title"><?php echo esc_html( $contacts_page->post_title ); ?></h2>
    <?php echo apply_filters( 'the_content', $contacts_page->post_content ); ?>
  </div>
</section>
<?php endif; ?>



<?php get_footer(); ?>
