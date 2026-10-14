<?php
/**
 * Повторювані частини розмітки: слайдер, новини, контакти, картки.
 *
 * Розмітка відповідає reference/static-site/ (класи, вкладеність, порядок атрибутів).
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Слайдер угорі сторінки.
 *
 * @param int   $pin  ID слайда (українського); 0 — усі слайди мовою сторінки.
 * @param array $opts Варіант оформлення: 'gradient' (клас замість класу слайда),
 *                    'icon' (SVG перед заголовком, один рядок), 'social' (завжди показувати соцмережі).
 */
function mits_hero( $pin = 0, $opts = array() ) {
	$opts = wp_parse_args( $opts, array( 'gradient' => '', 'icon' => '', 'social' => false ) );
	$args = array(
		'post_type'      => 'mits_hero_slide',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'no_found_rows'  => true,
	);
	if ( $pin ) {
		$args['post__in']       = array( mits_translated_id( $pin ) );
		$args['posts_per_page'] = 1;
		$args['lang']           = '';
	}
	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return;
	}
	$i = 0;
	?>
    <section class="mits-hero">
      <div class="mits-hero__track">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			$id       = get_the_ID();
			$gradient = get_post_meta( $id, '_mits_slide_gradient', true );
			$gradient = $opts['gradient'] ? $opts['gradient'] : ( $gradient ? $gradient : 'gradient-orange-cyan' );
			$line1    = (string) get_post_meta( $id, '_mits_slide_line1', true );
			$line2    = (string) get_post_meta( $id, '_mits_slide_line2', true );
			$size1    = absint( get_post_meta( $id, '_mits_slide_line1_size', true ) );
			$size2    = absint( get_post_meta( $id, '_mits_slide_line2_size', true ) );
			$social   = trim( (string) get_post_meta( $id, '_mits_slide_social', true ) );
			$subtitle = (string) get_post_meta( $id, '_mits_slide_subtitle', true );
			$subtitle = '' !== $subtitle ? $subtitle : mits_t( 'hero.subtitle' );
			$img      = get_the_post_thumbnail_url( $id, 'large' );
			?>
        <div class="mits-hero__slide <?php echo esc_attr( $gradient ); ?><?php echo 0 === $i ? ' is-active' : ''; ?>">
          <div class="mits-hero__content">
            <p class="mits-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php if ( $opts['icon'] ) : ?>
            <h1 class="mits-hero__title mits-hero__title--icon"><?php echo $opts['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- SVG із файлу теми. ?><span class="mits-hero__line"><?php echo esc_html( trim( $line1 . ' ' . $line2 ) ); ?></span></h1>
			<?php else : ?>
            <h1 class="mits-hero__title">
              <span class="mits-hero__line"<?php echo $size1 ? ' style="font-size:' . $size1 . 'px"' : ''; ?>><?php echo esc_html( $line1 ); ?></span>
              <span class="mits-hero__line"<?php echo $size2 ? ' style="font-size:' . $size2 . 'px"' : ''; ?>><?php echo esc_html( $line2 ); ?></span>
            </h1>
			<?php endif; ?>
			<?php if ( '' !== $social || $opts['social'] ) : ?>
            <div class="mits-hero__social"><?php echo mits_social_links(); // phpcs:ignore WordPress.Security.EscapeOutput -- готова розмітка з екранованими даними. ?></div>
			<?php endif; ?>
          </div>
			<?php if ( $img ) : ?>
          <div class="mits-hero__image">
            <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
          </div>
			<?php endif; ?>
        </div>
			<?php
			++$i;
		endwhile;
		wp_reset_postdata();
		?>
      </div>
      <a href="#contact-form" class="mits-btn mits-btn--primary mits-hero__cta"><?php mits_te( 'nav.cta' ); ?></a>
		<?php if ( $query->post_count > 1 ) : ?>
      <div class="mits-hero__dots" role="tablist" aria-label="<?php echo esc_attr( mits_t( 'hero.slides' ) ); ?>">
			<?php for ( $d = 0; $d < $query->post_count; $d++ ) : ?>
        <button class="mits-hero__dot<?php echo 0 === $d ? ' is-active' : ''; ?>" data-index="<?php echo (int) $d; ?>" aria-label="<?php echo esc_attr( sprintf( mits_t( 'hero.slide' ), $d + 1 ) ); ?>"></button>
			<?php endfor; ?>
      </div>
		<?php endif; ?>
    </section>
	<?php
}

/**
 * Секція «Новини» з трьома останніми новинами мовою сторінки.
 *
 * @param bool $link_images Чи обгортати зображення посиланням (як на головній).
 */
function mits_news_section( $link_images = false ) {
	$news = new WP_Query( array(
		'post_type'      => 'news',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => mits_visible_news_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	if ( ! $news->have_posts() ) {
		return;
	}
	?>
<section class="mits-section mits-news">
  <div class="mits-container">
    <h2 class="mits-section__title"><?php mits_te( 'news.title' ); ?></h2>
    <div class="mits-news__grid">
	<?php
	while ( $news->have_posts() ) :
		$news->the_post();
		?>
      <div class="mits-news__item">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php if ( $link_images ) : ?>
          <a href="<?php the_permalink(); ?>" class="mits-news__img-link">
            <div class="mits-news__img"><?php the_post_thumbnail( 'medium_large' ); ?></div>
          </a>
			<?php else : ?>
          <div class="mits-news__img"><?php the_post_thumbnail( 'medium_large' ); ?></div>
			<?php endif; ?>
		<?php else : ?>
          <div class="mits-news__img mits-news__img--placeholder"></div>
		<?php endif; ?>
        <div class="mits-news__meta">
          <span class="mits-news__date"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></span>
        </div>
        <h3 class="mits-news__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="mits-news__excerpt"><?php echo has_excerpt() ? esc_html( get_the_excerpt() ) : wp_trim_words( get_the_excerpt(), 20 ); // phpcs:ignore WordPress.Security.EscapeOutput -- wp_trim_words прибирає теги. ?></p>
      </div>
	<?php endwhile; ?>
    </div>
  </div>
</section>
	<?php
	wp_reset_postdata();
}

/**
 * Секція «Контакти» з вмістом сторінки «Контакти».
 *
 * @param string $id Атрибут id секції.
 */
function mits_contacts_section( $id = 'contact-form' ) {
	$page_id = mits_contacts_page_id();
	$page    = $page_id ? get_post( $page_id ) : null;
	if ( ! $page || 'publish' !== $page->post_status ) {
		return;
	}
	?>
<!-- ===================== КОНТАКТИ ===================== -->
<section class="mits-section mits-contacts" id="<?php echo esc_attr( $id ); ?>">
  <div class="mits-container">
    <h2 class="mits-page__title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
	<?php echo apply_filters( 'the_content', $page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput -- вміст сторінки. ?>
  </div>
</section>
	<?php
}

/**
 * Картки «Діяльність».
 *
 * @param int $limit Кількість карток (-1 — усі).
 */
function mits_activity_cards( $limit = -1 ) {
	$query = new WP_Query( array(
		'post_type'      => 'mits_activity',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'no_found_rows'  => true,
	) );
	while ( $query->have_posts() ) :
		$query->the_post();
		$id   = get_the_ID();
		$icon = get_post_meta( $id, '_act_icon_url', true );
		$bg   = get_post_meta( $id, '_act_icon_bg', true );
		$btn  = get_post_meta( $id, '_act_btn', true );
		$url  = get_post_meta( $id, '_act_url', true );
		$desc = get_post_meta( $id, '_act_desc', true );
		?>
      <div class="mits-card">
        <div class="mits-card__header">
		<?php if ( $icon || $bg ) : ?>
          <div class="mits-card__icon"<?php echo $bg ? ' style="background-color:' . esc_attr( $bg ) . ';"' : ''; ?>>
			<?php if ( $icon ) : ?>
              <img src="<?php echo esc_url( mits_url( $icon ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
			<?php endif; ?>
          </div>
		<?php endif; ?>
          <h3><?php the_title(); ?></h3>
        </div>
		<?php if ( $desc ) : ?>
        <p><?php echo wp_kses( $desc, mits_kses_inline() ); ?></p>
		<?php endif; ?>
        <a href="<?php echo esc_url( $url ? mits_url( $url ) : '#' ); ?>" class="mits-btn mits-btn--outline"><?php echo esc_html( $btn ? $btn : mits_t( 'front.more' ) ); ?></a>
      </div>
		<?php
	endwhile;
	wp_reset_postdata();
}

/**
 * Кнопка під картками «Діяльність».
 */
function mits_activity_cta() {
	$text = mits_get_field( 'mits_activities', 'cta_text', mits_t( 'front.activities_cta' ) );
	$url  = mits_get_field( 'mits_activities', 'cta_url', '#' );
	if ( '' === $text ) {
		return;
	}
	?>
    <div class="mits-activities__cta">
      <a href="<?php echo esc_url( mits_url( $url ) ); ?>" class="mits-btn mits-btn--primary"><?php echo esc_html( $text ); ?></a>
    </div>
	<?php
}

/**
 * Картка викладача (архів і сторінка «Кафедра»).
 */
function mits_teacher_card() {
	$id       = get_the_ID();
	$position = get_post_meta( $id, '_teacher_position', true );
	$grade    = get_post_meta( $id, '_teacher_grade', true );
	$pubs     = get_post_meta( $id, '_teacher_publications', true );
	$pubs     = is_string( $pubs ) ? $pubs : '';
	$photo    = get_the_post_thumbnail_url( $id, 'large' );
	?>
        <div class="mits-teacher-card">
          <a class="mits-teacher-card__photo-wrap" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
	<?php if ( $photo ) : ?>
              <img class="mits-teacher-card__photo"
                   src="<?php echo esc_url( $photo ); ?>"
                   alt="<?php echo esc_attr( get_the_title() ); ?>">
	<?php else : ?>
              <div class="mits-teacher-card__photo mits-teacher-card__photo--placeholder"></div>
	<?php endif; ?>
          </a>
          <div class="mits-teacher-card__info">
            <p class="mits-teacher-card__name">
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </p>
	<?php if ( $position ) : ?>
              <p class="mits-teacher-card__position"><?php echo esc_html( $position ); ?></p>
	<?php endif; ?>
	<?php if ( $grade ) : ?>
              <p class="mits-teacher-card__grade"><?php echo esc_html( $grade ); ?></p>
	<?php endif; ?>
	<?php if ( $pubs ) : ?>
              <a class="mits-teacher-card__link"
                 href="<?php echo esc_url( $pubs ); ?>"
                 target="_blank" rel="noopener"><?php mits_te( 'teachers.publications' ); ?></a>
	<?php endif; ?>
          </div>
        </div>
	<?php
}

/**
 * Пункт акордеона (переваги, FAQ, курси).
 *
 * @param string $title Заголовок.
 * @param string $body  Текст (переноси рядків зберігаються стилями).
 */
function mits_accordion_item( $title, $body ) {
	$body = trim( (string) $body );
	?>
      <div class="mits-adv-item">
        <button class="mits-adv-item__header" aria-expanded="false"<?php echo '' === $body ? ' data-no-content' : ''; ?>>
          <span><?php echo esc_html( $title ); ?></span>
	<?php if ( '' !== $body ) : ?>
          <span class="mits-adv-item__chevron">›</span>
	<?php endif; ?>
        </button>
	<?php if ( '' !== $body ) : ?>
        <div class="mits-adv-item__body">
          <div class="mits-adv-item__body-inner"><?php echo mits_kses_text( $body ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        </div>
	<?php endif; ?>
      </div>
	<?php
}

/**
 * Відкриває обгортки вмісту сторінки (як у статичній копії).
 *
 * @param string $inner_tag   div|main для .sections_group.
 * @param string $entry_attrs Атрибути .entry-content.
 */
function mits_content_open( $inner_tag = 'div', $entry_attrs = ' id="mits-front"' ) {
	$class = 'entry-content';
	if ( preg_match( '/^\s*class="([^"]*)"\s*$/', $entry_attrs, $m ) ) {
		$class      .= ' ' . $m[1];
		$entry_attrs = '';
	}
	echo "\n<div id=\"Content\">\n<div class=\"content_wrapper clearfix\">\n<" . tag_escape( $inner_tag ) . " class=\"sections_group\">\n<div class=\"" . esc_attr( $class ) . "\"" . $entry_attrs . ">\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Закриває обгортки вмісту.
 *
 * У шаблонах зі слайдером (як і на старому сайті) разом із вмістом закривається
 * #Wrapper, тож наступні секції (контакти, форма, підвал) лежать поза ним —
 * від цього залежить фон навколо заокруглених кутів блоків.
 *
 * @param string $inner_tag     div|main.
 * @param bool   $close_wrapper Закрити також #Wrapper.
 */
function mits_content_close( $inner_tag = 'div', $close_wrapper = false ) {
	echo "</div><!-- .entry-content -->\n</" . tag_escape( $inner_tag ) . "><!-- .sections_group -->\n</div><!-- .content_wrapper -->\n</div><!-- #Content -->\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	if ( $close_wrapper ) {
		mits_close_wrapper();
	}
}

/**
 * Закриває #Wrapper (один раз).
 */
function mits_close_wrapper() {
	if ( empty( $GLOBALS['mits_wrapper_closed'] ) ) {
		echo "</div><!-- #Wrapper -->\n";
		$GLOBALS['mits_wrapper_closed'] = true;
	}
}
