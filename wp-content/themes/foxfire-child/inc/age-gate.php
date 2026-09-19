<?php
/**
 * Site-wide 21+ research-use acknowledgement gate.
 *
 * This is a visitor self-attestation, not identity or age verification.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

const FOXFIRE_AGE_GATE_COOKIE        = 'foxfire_age_verified';
const FOXFIRE_AGE_GATE_COOKIE_VALUE  = '21';
const FOXFIRE_AGE_GATE_COOKIE_MAX_AGE = 30 * DAY_IN_SECONDS;

/**
 * Whether the current browser previously accepted the 21+ gate.
 */
function foxfire_age_gate_is_verified(): bool {
	if ( ! isset( $_COOKIE[ FOXFIRE_AGE_GATE_COOKIE ] ) || ! is_string( $_COOKIE[ FOXFIRE_AGE_GATE_COOKIE ] ) ) {
		return false;
	}

	return FOXFIRE_AGE_GATE_COOKIE_VALUE === sanitize_text_field( wp_unslash( $_COOKIE[ FOXFIRE_AGE_GATE_COOKIE ] ) );
}

/**
 * Set the verification cookie after an explicit confirmation and return to the
 * same safe Foxfire URL. No personal information is stored.
 */
function foxfire_age_gate_handle_confirmation(): void {
	if ( 'POST' !== strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) ) {
		return;
	}

	$confirmation = isset( $_POST['foxfire_age_confirmation'] ) && is_string( $_POST['foxfire_age_confirmation'] )
		? sanitize_text_field( wp_unslash( $_POST['foxfire_age_confirmation'] ) )
		: '';

	if ( FOXFIRE_AGE_GATE_COOKIE_VALUE !== $confirmation ) {
		return;
	}

	$cookie_options = array(
		'expires'  => time() + FOXFIRE_AGE_GATE_COOKIE_MAX_AGE,
		'path'     => '/',
		'secure'   => is_ssl(),
		'httponly' => false,
		'samesite' => 'Lax',
	);

	if ( defined( 'COOKIE_DOMAIN' ) && is_string( COOKIE_DOMAIN ) && '' !== COOKIE_DOMAIN ) {
		$cookie_options['domain'] = COOKIE_DOMAIN;
	}

	setcookie( FOXFIRE_AGE_GATE_COOKIE, FOXFIRE_AGE_GATE_COOKIE_VALUE, $cookie_options );
	$_COOKIE[ FOXFIRE_AGE_GATE_COOKIE ] = FOXFIRE_AGE_GATE_COOKIE_VALUE;

	$redirect = isset( $_POST['foxfire_age_return'] ) && is_string( $_POST['foxfire_age_return'] )
		? wp_validate_redirect( wp_unslash( $_POST['foxfire_age_return'] ), home_url( '/' ) )
		: home_url( '/' );

	wp_safe_redirect( $redirect, 303, 'Foxfire Age Gate' );
	exit;
}
add_action( 'template_redirect', 'foxfire_age_gate_handle_confirmation', 0 );

/**
 * Enqueue the small global gate bundle on public HTML pages.
 */
function foxfire_age_gate_enqueue_assets(): void {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() || is_feed() || is_robots() ) {
		return;
	}

	$css_path = FOXFIRE_CHILD_DIR . '/assets/css/age-gate.css';
	$js_path  = FOXFIRE_CHILD_DIR . '/assets/js/age-gate.js';

	wp_enqueue_style(
		'foxfire-age-gate',
		FOXFIRE_CHILD_URI . '/assets/css/age-gate.css',
		array( 'foxfire-fonts' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : FOXFIRE_CHILD_VERSION
	);

	wp_enqueue_script(
		'foxfire-age-gate',
		FOXFIRE_CHILD_URI . '/assets/js/age-gate.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : FOXFIRE_CHILD_VERSION,
		false
	);
}
add_action( 'wp_enqueue_scripts', 'foxfire_age_gate_enqueue_assets', 15 );

/**
 * Render one global dialog. It is visible by default so JavaScript failure
 * cannot silently bypass the acknowledgement.
 */
function foxfire_age_gate_render(): void {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() || is_feed() || is_robots() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] )
		? wp_unslash( $_SERVER['REQUEST_URI'] )
		: '/';
	$return_url  = wp_validate_redirect( home_url( $request_uri ), home_url( '/' ) );
	$exit_url    = (string) apply_filters( 'foxfire_age_gate_exit_url', 'https://www.google.com/' );
	?>
	<div
		id="ff-age-gate"
		class="ff-age-gate"
		role="dialog"
		aria-modal="true"
		aria-labelledby="ff-age-gate-title"
		aria-describedby="ff-age-gate-description"
	>
		<div class="ff-age-gate__panel">
			<p class="ff-age-gate__brand"><?php esc_html_e( 'Foxfire Peptides', 'foxfire-child' ); ?></p>
			<h2 id="ff-age-gate-title" class="ff-age-gate__title">
				<?php esc_html_e( 'You must be 21 years of age or older to enter this website.', 'foxfire-child' ); ?>
			</h2>
			<p class="ff-age-gate__classification"><?php esc_html_e( '21+ | Research Use Only', 'foxfire-child' ); ?></p>
			<p id="ff-age-gate-description" class="ff-age-gate__description">
				<?php esc_html_e( 'Products offered by Foxfire Peptides are intended strictly for laboratory research and analytical purposes only and are not intended for human consumption or medical use.', 'foxfire-child' ); ?>
			</p>
			<div class="ff-age-gate__actions">
				<form class="ff-age-gate__form" method="post" action="">
					<input type="hidden" name="foxfire_age_confirmation" value="21">
					<input type="hidden" name="foxfire_age_return" value="<?php echo esc_url( $return_url ); ?>">
					<button class="ff-age-gate__enter" type="submit">
						<?php esc_html_e( 'I AM 21 OR OLDER — ENTER', 'foxfire-child' ); ?>
					</button>
				</form>
				<a class="ff-age-gate__exit" href="<?php echo esc_url( $exit_url ); ?>" rel="noreferrer">
					<?php esc_html_e( 'I AM UNDER 21 — EXIT', 'foxfire-child' ); ?>
				</a>
			</div>
			<p class="ff-age-gate__memory">
				<?php esc_html_e( 'Your confirmation will be remembered for 30 days on this browser.', 'foxfire-child' ); ?>
			</p>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'foxfire_age_gate_render', PHP_INT_MAX );
