<?php
/**
 * Single template for the "teacher" post type.
 *
 * @package Betheme
 */

get_header();
?>

<div id="Content">
<div class="content_wrapper clearfix">
<main class="sections_group">
<div class="entry-content">

<?php while ( have_posts() ) : the_post();

    $position     = get_post_meta( get_the_ID(), '_teacher_position',     true );
    $grade        = get_post_meta( get_the_ID(), '_teacher_grade',        true );
    $description  = get_post_meta( get_the_ID(), '_teacher_description',  true );
    $photo_id     = get_post_meta( get_the_ID(), '_teacher_photo_id',     true );
    $photo_url    = $photo_id ? wp_get_attachment_image_url( $photo_id, 'large' ) : '';
    $subjects_raw = get_post_meta( get_the_ID(), '_teacher_subjects', true );
    $subjects     = is_array( $subjects_raw ) ? $subjects_raw : array();
    $pubs_raw     = get_post_meta( get_the_ID(), '_teacher_publications', true );
    $publications = is_array( $pubs_raw ) ? $pubs_raw : array();

?>

<section class="mits-section mits-teacher-single">
  <div class="mits-container">

    <!-- Header row: photo + main info -->
    <div class="mits-teacher-single__header">

      <?php if ( $photo_url ) : ?>
        <div class="mits-teacher-single__photo">
          <img src="<?php echo esc_url( $photo_url ); ?>"
               alt="<?php echo esc_attr( get_the_title() ); ?>">
        </div>
      <?php endif; ?>

      <div class="mits-teacher-single__info">
        <h1 class="mits-teacher-single__name"><?php the_title(); ?></h1>

        <?php if ( $position ) : ?>
          <p class="mits-teacher-single__position"><?php echo esc_html( $position ); ?></p>
        <?php endif; ?>

        <?php if ( $grade ) : ?>
          <p class="mits-teacher-single__grade"><?php echo esc_html( $grade ); ?></p>
        <?php endif; ?>

        <?php if ( $subjects ) : ?>
          <div class="mits-teacher-single__subjects">
            <h3>Предмети</h3>
            <ul>
              <?php foreach ( $subjects as $subject ) : ?>
                <li><?php echo esc_html( $subject ); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>

    </div><!-- /.header -->

    <?php if ( $description ) : ?>
      <div class="mits-teacher-single__description">
        <h2>Про викладача</h2>
        <div class="mits-teacher-single__description-text">
          <?php echo wp_kses_post( wpautop( $description ) ); ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ( $publications ) : ?>
      <div class="mits-teacher-single__publications">
        <h2>Публікації</h2>
        <ol>
          <?php foreach ( $publications as $pub ) : ?>
            <li><?php echo esc_html( $pub ); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>
    <?php endif; ?>

    <div class="mits-teacher-single__back">
      <a href="<?php echo esc_url( get_post_type_archive_link( 'teacher' ) ); ?>"
         class="mits-btn mits-btn--outline">&larr; Всі викладачі</a>
    </div>

  </div><!-- /.mits-container -->
</section>

<?php endwhile; ?>

</div>
</main>
</div>
</div>

<?php get_footer(); ?>
