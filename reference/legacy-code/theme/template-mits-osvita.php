<?php
/**
 * Template Name: MITS — Освіта
 *
 * Головна сторінка розділу «Освіта».
 *
 * @package Betheme
 */

get_header();

$post_id    = get_the_ID();
$is_en      = function_exists( 'pll_current_language' ) && pll_current_language() === 'en';
$hero_pin   = absint( get_post_meta( $post_id, '_osvita_hero_id', true ) );
$_osvita_slogan = get_post_meta( $post_id, '_osvita_slogan', true );
$_osvita_desc   = get_post_meta( $post_id, '_osvita_desc', true );
$_tech_items = json_decode( get_option( 'mits_osvita_tech_items' ) ?: '[]', true );
if ( ! is_array( $_tech_items ) ) $_tech_items = [];

// ── Contacts page ────────────────────────────────────────────────
$contacts_id = 24;
if ( function_exists( 'pll_get_post' ) ) {
    $translated = pll_get_post( $contacts_id );
    if ( $translated ) $contacts_id = $translated;
}
$contacts_page = get_post( $contacts_id );

// ── I18n labels ──────────────────────────────────────────────────
$news_title = $is_en ? 'News' : 'Новини';
?>

<div id="Content">
<div class="content_wrapper clearfix">
<div class="sections_group">
<div class="entry-content" id="mits-front">

  <!-- ===================== HERO SLIDER ===================== -->
  <?php if ( function_exists( 'mits_hero_render' ) ) mits_hero_render( '#contact-form', '', $hero_pin ); ?>

  <!-- ===================== ТЕХНОЛОГІЇ ===================== -->
  <?php if ( ! empty( $_tech_items ) ) : ?>
  <div class="mits-tech">
    <div class="mits-container">
      <div class="mits-tech__grid">
        <?php foreach ( $_tech_items as $_tech ) :
          $_timg  = esc_url( $_tech['img'] ?? '' );
          $_tlink = esc_url( $_tech['link'] ?? '' );
          if ( ! $_timg ) continue;
        ?>
        <?php if ( $_tlink ) : ?>
        <a href="<?php echo $_tlink; ?>" class="mits-tech__item" target="_blank" rel="noopener noreferrer"><img src="<?php echo $_timg; ?>" alt="" class="mits-tech__img"></a>
        <?php else : ?>
        <span class="mits-tech__item"><img src="<?php echo $_timg; ?>" alt="" class="mits-tech__img"></span>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ===================== КОРОТКИЙ ОПИС ===================== -->
  <?php
  $_zagalni_page = get_page_by_path( 'osvita/zahalni-polozhennia' )
                ?? get_page_by_path( 'освіта/загальні-положення' );
  if ( ! $_zagalni_page ) {
      // fallback: find by parent
      $_osvita_obj = get_page_by_path( 'освіта' );
      if ( $_osvita_obj ) {
          $_kids = get_pages( array( 'parent' => $_osvita_obj->ID, 'post_status' => 'publish', 'number' => 10 ) );
          foreach ( $_kids as $_kid ) {
              if ( stripos( $_kid->post_title, 'загальн' ) !== false ) {
                  $_zagalni_page = $_kid;
                  break;
              }
          }
      }
  }
  $_zagalni_url = $_zagalni_page ? get_permalink( $_zagalni_page->ID ) : '#';
  $_more_text   = $is_en ? 'More' : 'Детальніше';
  ?>
  <?php if ( $_osvita_desc ) : ?>
  <section class="mits-section mits-osvita-lead">
    <div class="mits-container">
      <div class="mits-osvita-desc-card">
        <div class="mits-osvita-desc-card__body"><?php echo apply_filters( 'the_content', $_osvita_desc ); ?></div>
        <a href="<?php echo esc_url( $_zagalni_url ); ?>" class="mits-osvita-desc-card__link"><?php echo esc_html( $_more_text ); ?></a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== СЛОГАН ===================== -->
  <?php if ( $_osvita_slogan ) : ?>
  <section class="mits-section mits-osvita-slogan">
    <div class="mits-container">
      <p class="mits-osvita-lead__slogan"><?php echo esc_html( $_osvita_slogan ); ?></p>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== ІНІЦІАЛІЗАЦІЯ LOOP (потрібна для WP) ===================== -->
  <?php while ( have_posts() ) : the_post(); endwhile; ?>

  <!-- ===================== ДОКУМЕНТИ ===================== -->
  <?php
  $_docs_title = get_option( 'mits_osvita_docs_title', '' ) ?: ( $is_en ? 'Regulatory documents' : 'Нормативні документи' );
  $_docs_desc  = get_option( 'mits_osvita_docs_desc', '' );
  $_docs_items = json_decode( get_option( 'mits_osvita_docs_items' ) ?: '[]', true );
  if ( ! is_array( $_docs_items ) ) $_docs_items = [];
  if ( $_docs_desc || ! empty( $_docs_items ) ) : ?>
  <section class="mits-section mits-osvita-docs">
    <div class="mits-container">
      <h2 class="mits-osvita-docs__title"><?php echo esc_html( $_docs_title ); ?></h2>
      <?php if ( $_docs_desc ) : ?>
      <div class="mits-osvita-docs__card">
        <?php echo apply_filters( 'the_content', $_docs_desc ); ?>
      </div>
      <?php endif; ?>
      <?php if ( ! empty( $_docs_items ) ) : ?>
      <div class="mits-osvita-docs__list-wrap">
      <ul class="mits-osvita-docs__list">
        <?php foreach ( $_docs_items as $_doc ) :
          $_durl  = esc_url( $_doc['url']  ?? '' );
          $_dname = wp_kses( $_doc['name'] ?? '', array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) );
          if ( ! $_dname ) continue;
          // If URL is provided and name has no links yet — wrap whole name in <a>
          $_has_link = str_contains( $_dname, '<a ' );
          if ( $_durl && ! $_has_link ) {
              $_dname = '<a href="' . $_durl . '" target="_blank" rel="noopener noreferrer">' . $_dname . '</a>';
          }
        ?>
        <li class="mits-osvita-docs__item"><?php echo $_dname; ?></li>
        <?php endforeach; ?>
      </ul>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== РІВНІ НАВЧАННЯ ===================== -->
  <?php
  $_lv_defaults = array(
    array( 'title' => 'Бакалавр',    'color' => '#4DC9C6', 'vote_url' => '', 'schedule' => array( 'name' => 'Розклад занять', 'url' => '' ), 'exams' => array( 'name' => 'Розклад екзаменів', 'url' => '' ), 'docs' => array() ),
    array( 'title' => 'Магістратура', 'color' => '#E87A2F', 'vote_url' => '', 'schedule' => array( 'name' => 'Розклад занять', 'url' => '' ), 'exams' => array( 'name' => 'Розклад екзаменів', 'url' => '' ), 'docs' => array() ),
    array( 'title' => 'Аспірантура',  'color' => '#D86FA0', 'vote_url' => '', 'schedule' => array( 'name' => 'Розклад занять', 'url' => '' ), 'exams' => array( 'name' => 'Розклад екзаменів', 'url' => '' ), 'docs' => array() ),
  );
  $_lv_saved  = json_decode( get_option( 'mits_osvita_level_blocks' ) ?: '[]', true );
  if ( ! is_array( $_lv_saved ) || count( $_lv_saved ) < 3 ) $_lv_saved = $_lv_defaults;
  $_lv_blocks = array_replace_recursive( $_lv_defaults, $_lv_saved );
  $_vote_label = $is_en ? 'Vote' : 'Проголосувати';
  ?>
  <section class="mits-section mits-level-blocks">
    <div class="mits-container">
      <div class="mits-level-blocks__row">
        <?php foreach ( $_lv_blocks as $_lv ) :
          $_lv_vote_url   = esc_url(  $_lv['vote_url'] ?? '' );
          $_lv_sched_name = esc_html( $_lv['schedule']['name'] ?? ( $is_en ? 'Class schedule'  : 'Розклад занять' ) );
          $_lv_sched_url  = esc_url(  $_lv['schedule']['url']  ?? '' );
          $_lv_exams_name = esc_html( $_lv['exams']['name']    ?? ( $is_en ? 'Exam schedule'   : 'Розклад екзаменів' ) );
          $_lv_exams_url  = esc_url(  $_lv['exams']['url']     ?? '' );
        ?>
        <div class="mits-level-card">
          <h3 class="mits-level-card__title" style="color:<?php echo esc_attr( $_lv['color'] ); ?>"><?php echo esc_html( strtoupper( $_lv['title'] ) ); ?></h3>
          <?php if ( ! empty( $_lv['docs'] ) ) : ?>
          <ul class="mits-level-card__docs">
            <?php foreach ( (array) $_lv['docs'] as $_d ) :
              $_dname = esc_html( $_d['name'] ?? '' );
              $_durl  = esc_url( $_d['url'] ?? '' );
              if ( ! $_dname ) continue;
            ?>
            <li><?php if ( $_durl ) : ?><a href="<?php echo $_durl; ?>" target="_blank" rel="noopener noreferrer"><?php echo $_dname; ?></a><?php else : echo $_dname; endif; ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
          <?php if ( $_lv_vote_url ) : ?>
          <a href="<?php echo $_lv_vote_url; ?>" class="mits-level-card__vote-btn"><?php echo esc_html( $_vote_label ); ?></a>
          <?php endif; ?>
          <?php if ( $_lv_sched_url || $_lv_exams_url ) : ?>
          <div class="mits-level-card__schedule">
            <?php if ( $_lv_sched_url ) : ?>
            <a href="<?php echo $_lv_sched_url; ?>" target="_blank" rel="noopener noreferrer"><?php echo $_lv_sched_name; ?></a>
            <?php endif; ?>
            <?php if ( $_lv_exams_url ) : ?>
            <a href="<?php echo $_lv_exams_url; ?>" target="_blank" rel="noopener noreferrer"><?php echo $_lv_exams_name; ?></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ===================== НОВИНИ ===================== -->
  <?php
  $mits_news = new WP_Query( array(
      'post_type'      => 'news',
      'posts_per_page' => 3,
      'post_status'    => 'publish',
      'orderby'        => 'date',
      'order'          => 'DESC',
      'no_found_rows'  => true,
  ) );
  if ( $mits_news->have_posts() ) : ?>
  <section class="mits-section mits-news">
    <div class="mits-container">
      <h2 class="mits-section__title"><?php echo esc_html( $news_title ); ?></h2>
      <div class="mits-news__grid">
        <?php while ( $mits_news->have_posts() ) : $mits_news->the_post(); ?>
        <div class="mits-news__item">
          <?php if ( has_post_thumbnail() ) : ?>
            <div class="mits-news__img"><?php the_post_thumbnail( 'medium_large' ); ?></div>
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
<?php if ( $contacts_page ) : ?>
<section class="mits-section mits-contacts" id="contact-form">
  <div class="mits-container">
    <h2 class="mits-page__title"><?php echo esc_html( $contacts_page->post_title ); ?></h2>
    <?php echo apply_filters( 'the_content', $contacts_page->post_content ); ?>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
