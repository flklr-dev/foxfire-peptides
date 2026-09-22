<?php
/** Shared newsletter signup preview. No address is stored or sent. */
defined( 'ABSPATH' ) || exit;

$source = isset( $args['source'] ) && 'home' === $args['source'] ? 'home' : 'footer';
$status = isset( $_GET['ff_newsletter'] ) ? sanitize_key( wp_unslash( $_GET['ff_newsletter'] ) ) : '';
$return_source = isset( $_GET['ff_newsletter_source'] ) ? sanitize_key( wp_unslash( $_GET['ff_newsletter_source'] ) ) : '';
?>
<form class="ff-newsletter-form ff-newsletter-form--<?php echo esc_attr( $source ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<label class="screen-reader-text" for="ff-newsletter-email-<?php echo esc_attr( $source ); ?>"><?php esc_html_e( 'Email address', 'foxfire-child' ); ?></label>
	<div class="ff-newsletter-form__fields">
		<input id="ff-newsletter-email-<?php echo esc_attr( $source ); ?>" type="email" name="foxfire_newsletter_email" placeholder="<?php esc_attr_e( 'Email address', 'foxfire-child' ); ?>" autocomplete="email" required />
		<button type="submit"><?php echo esc_html( 'home' === $source ? __( 'JOIN OUR RESEARCH COMMUNITY', 'foxfire-child' ) : __( 'Join the list', 'foxfire-child' ) ); ?></button>
	</div>
	<input type="hidden" name="action" value="foxfire_newsletter_preview" />
	<input type="hidden" name="foxfire_newsletter_source" value="<?php echo esc_attr( $source ); ?>" />
	<?php wp_nonce_field( 'foxfire_newsletter_preview', 'foxfire_newsletter_nonce' ); ?>
	<?php if ( $source === $return_source && in_array( $status, array( 'invalid', 'not-connected' ), true ) ) : ?>
		<p class="ff-newsletter-form__message" role="status">
			<?php echo esc_html( 'invalid' === $status ? __( 'Please enter a valid email address.', 'foxfire-child' ) : __( 'Email signup is not open yet. Your address was not saved or subscribed.', 'foxfire-child' ) ); ?>
		</p>
	<?php endif; ?>
</form>
