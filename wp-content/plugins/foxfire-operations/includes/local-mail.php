<?php
/**
 * Route email to the local Mailpit inbox during local development only.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine whether the explicitly configured local mail catcher is enabled.
 *
 * @return bool
 */
function foxfire_operations_local_mail_is_enabled(): bool {
	return 'local' === wp_get_environment_type() && '1' === getenv( 'FOXFIRE_LOCAL_MAIL_ENABLED' );
}

/**
 * Configure WordPress mail for the isolated local Docker environment.
 *
 * Production and staging environments are deliberately untouched. They must use
 * an authenticated transactional mail provider configured outside this plugin.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer WordPress mailer instance.
 * @return void
 */
function foxfire_operations_configure_local_mail( $phpmailer ): void {
	if ( ! foxfire_operations_local_mail_is_enabled() ) {
		return;
	}

	$host = getenv( 'FOXFIRE_LOCAL_SMTP_HOST' );
	if ( ! is_string( $host ) || '' === trim( $host ) ) {
		return;
	}

	$host     = sanitize_text_field( trim( $host ) );
	$port_raw = getenv( 'FOXFIRE_LOCAL_SMTP_PORT' );
	$port     = is_string( $port_raw ) ? absint( $port_raw ) : 1025;
	$port     = $port >= 1 && $port <= 65535 ? $port : 1025;

	$phpmailer->isSMTP();
	$phpmailer->Host        = $host;
	$phpmailer->Port        = $port;
	$phpmailer->SMTPAuth    = false;
	$phpmailer->SMTPSecure  = '';
	$phpmailer->SMTPAutoTLS = false;
}
add_action( 'phpmailer_init', 'foxfire_operations_configure_local_mail', 20 );

/**
 * Use a syntactically valid, non-deliverable sender in local development.
 *
 * The default wordpress@localhost address is rejected by modern PHPMailer
 * before it can reach Mailpit.
 *
 * @param string $from_email Existing sender address.
 * @return string
 */
function foxfire_operations_local_mail_from( $from_email ): string {
	return foxfire_operations_local_mail_is_enabled() ? 'wordpress@foxfire.test' : $from_email;
}
add_filter( 'wp_mail_from', 'foxfire_operations_local_mail_from', 20 );

/**
 * Make locally captured messages easy to identify.
 *
 * @param string $from_name Existing sender name.
 * @return string
 */
function foxfire_operations_local_mail_from_name( $from_name ): string {
	return foxfire_operations_local_mail_is_enabled() ? 'Foxfire Peptides (Local)' : $from_name;
}
add_filter( 'wp_mail_from_name', 'foxfire_operations_local_mail_from_name', 20 );
