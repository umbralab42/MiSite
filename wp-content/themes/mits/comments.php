<?php
/**
 * Коментарі під новиною (як на старому сайті). Нові коментарі потребують схвалення.
 *
 * @package MITS
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}

$mits_req = '<span class="required">*</span>';
?>
<div id="comments">
<?php if ( have_comments() ) : ?>
	<ol class="commentlist">
		<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true, 'avatar_size' => 48 ) ); ?>
	</ol>
	<?php the_comments_navigation(); ?>
<?php endif; ?>
<?php
comment_form( array(
	'title_reply'          => esc_html( mits_t( 'comments.reply' ) ),
	/* translators: %s: ім'я автора коментаря. */
	'title_reply_to'       => esc_html( mits_t( 'comments.reply_to' ) ),
	'cancel_reply_link'    => esc_html( mits_t( 'comments.cancel' ) ),
	'label_submit'         => mits_t( 'comments.submit' ),
	'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">' . esc_html( mits_t( 'comments.notes' ) ) . '</span> <span class="required-field-message">' . sprintf( esc_html( mits_t( 'comments.required' ) ), $mits_req ) . '</span></p>',
	'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . esc_html( mits_t( 'comments.comment' ) ) . ' ' . $mits_req . '</label> <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required"></textarea></p>',
	'fields'               => array(
		'author'  => '<p class="comment-form-author"><label for="author">' . esc_html( mits_t( 'comments.name' ) ) . ' ' . $mits_req . '</label> <input id="author" name="author" type="text" value="" size="30" maxlength="245" autocomplete="name" required="required" /></p>',
		'email'   => '<p class="comment-form-email"><label for="email">' . esc_html( mits_t( 'comments.email' ) ) . ' ' . $mits_req . '</label> <input id="email" name="email" type="text" value="" size="30" maxlength="100" aria-describedby="email-notes" autocomplete="email" required="required" /></p>',
		'url'     => '<p class="comment-form-url"><label for="url">' . esc_html( mits_t( 'comments.url' ) ) . '</label> <input id="url" name="url" type="text" value="" size="30" maxlength="200" autocomplete="url" /></p>',
		'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" /> <label for="wp-comment-cookies-consent">' . esc_html( mits_t( 'comments.cookies' ) ) . '</label></p>',
	),
) );
?>
</div>
