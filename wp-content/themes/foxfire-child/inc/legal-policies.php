<?php
/** Client-supplied policy content in the shared, revision-friendly legal layout. */
defined( 'ABSPATH' ) || exit;

function foxfire_legal_policies(): array {
	static $policies = null;
	if ( null === $policies ) {
		$policies = json_decode( file_get_contents( __DIR__ . '/legal-policies.json' ), true, 512, JSON_THROW_ON_ERROR );
	}
	return $policies;
}

/** Escape copy before adding only known policy, website and email links. */
function foxfire_legal_link_text( string $text ): string {
	$text = esc_html( $text );
	$links = array(
		'Terms &amp; Conditions' => foxfire_get_page_url( 'terms-and-conditions', '/terms-and-conditions/' ),
		'Terms and Conditions' => foxfire_get_page_url( 'terms-and-conditions', '/terms-and-conditions/' ),
		'Privacy Policy' => foxfire_get_page_url( 'privacy-policy', '/privacy-policy/' ),
		'Refund &amp; Returns Policy' => foxfire_get_page_url( 'refund-and-returns-policy', '/refund-and-returns-policy/' ),
		'Shipping Policy' => foxfire_get_page_url( 'shipping-policy', '/shipping-policy/' ),
		'Contact page' => foxfire_get_page_url( 'contact', '/contact/' ),
		'FoxfirePeptides.com' => home_url( '/' ),
		'info@foxfirepeptides.com' => 'mailto:info@foxfirepeptides.com',
		'support@foxfirepeptides.com' => 'mailto:support@foxfirepeptides.com',
	);
	// strtr performs one replacement pass, without relinking inserted markup.
	foreach ( $links as $label => $url ) {
		$links[ $label ] = '<a href="' . esc_url( $url ) . '">' . $label . '</a>';
	}
	return strtr( $text, $links );
}

function foxfire_legal_section_html( array $section ): string {
	$html = '';
	foreach ( $section['blocks'] as $block ) {
		if ( 'ul' === $block['type'] ) {
			$html .= '<ul>';
			foreach ( $block['items'] as $item ) $html .= '<li>' . foxfire_legal_link_text( $item ) . '</li>';
			$html .= '</ul>';
		} else {
			$html .= '<p>' . foxfire_legal_link_text( $block['text'] ) . '</p>';
		}
	}
	return $html;
}

/** A normal editor body: headings and paragraphs, without duplicated page chrome. */
function foxfire_legal_editor_content( string $key ): string {
	$html = '';
	foreach ( foxfire_legal_policies()[ $key ]['sections'] as $i => $section ) {
		$html .= '<h2 id="' . esc_attr( $section['id'] ) . '">' . esc_html( ( $i + 1 ) . '. ' . $section['title'] ) . '</h2>' . "\n";
		$html .= foxfire_legal_section_html( $section ) . "\n";
	}
	return $html;
}

/** Build the same TOC and section styling after an administrator edits the body. */
function foxfire_legal_editor_sections( string $content ): array {
	$content = wp_kses_post( apply_filters( 'the_content', $content ) );
	preg_match_all( '/<h2\b[^>]*>(.*?)<\/h2>/is', $content, $matches, PREG_OFFSET_CAPTURE );
	$sections = array();
	$taken = array();
	foreach ( $matches[0] as $i => $heading ) {
		$title = html_entity_decode( wp_strip_all_tags( $matches[1][ $i ][0] ), ENT_QUOTES, 'UTF-8' );
		$title = preg_replace( '/^\s*\d+\.\s*/u', '', $title );
		$processor = new WP_HTML_Tag_Processor( $heading[0] );
		$processor->next_tag( 'H2' );
		$id = sanitize_title( (string) $processor->get_attribute( 'id' ) );
		$id = $id ?: ( sanitize_title( $title ) ?: 'policy-section' );
		$base = $id;
		for ( $suffix = 2; isset( $taken[ $id ] ); ++$suffix ) $id = $base . '-' . $suffix;
		$taken[ $id ] = true;
		$start = $heading[1] + strlen( $heading[0] );
		$end = $matches[0][ $i + 1 ][1] ?? strlen( $content );
		$sections[] = array( 'id' => $id, 'title' => $title, 'html' => substr( $content, $start, $end - $start ) );
	}
	$intro_end = $matches[0][0][1] ?? strlen( $content );
	return array( 'intro' => substr( $content, 0, $intro_end ), 'sections' => $sections );
}

function foxfire_render_legal_policy( string $key ): void {
	$policy = foxfire_legal_policies()[ $key ];
	$page_id = get_queried_object_id();
	$sections = $policy['sections'];
	$intro = '';
	if ( foxfire_should_render_editor_page() ) {
		$editor = foxfire_legal_editor_sections( (string) get_post_field( 'post_content', $page_id ) );
		$sections = $editor['sections'];
		$intro = $editor['intro'];
	}
	$effective = get_post_meta( $page_id, '_foxfire_policy_effective_date', true ) ?: $policy['effective'];
	$updated = get_post_meta( $page_id, '_foxfire_policy_updated_date', true ) ?: $policy['updated'];
	?>
	<main id="main-content" class="ff-policy-page" tabindex="-1">
		<div class="ff-policy-page__inner">
			<header class="ff-policy-header">
				<p class="ff-policy-header__label"><?php esc_html_e( 'Legal', 'foxfire-child' ); ?></p>
				<h1 class="ff-policy-header__title"><?php echo esc_html( $policy['title'] ); ?></h1>
				<div class="ff-policy-header__meta">
					<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Effective: %s', 'foxfire-child' ), esc_html( $effective ) ); ?></span>
					<span class="ff-policy-meta-sep" aria-hidden="true">&middot;</span>
					<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Last updated: %s', 'foxfire-child' ), esc_html( $updated ) ); ?></span>
				</div>
			</header>
			<?php if ( $sections ) : ?>
				<nav class="ff-policy-toc" aria-label="<?php echo esc_attr( $policy['title'] . ' sections' ); ?>">
					<p class="ff-policy-toc__label"><?php esc_html_e( 'Contents', 'foxfire-child' ); ?></p>
					<ol class="ff-policy-toc__list">
						<?php foreach ( $sections as $section ) : ?>
							<li><a href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['title'] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>
			<div class="ff-policy-sections">
				<?php if ( '' !== trim( $intro ) ) : ?><div class="ff-policy-section"><?php echo wp_kses_post( $intro ); ?></div><?php endif; ?>
				<?php foreach ( $sections as $i => $section ) : ?>
					<section id="<?php echo esc_attr( $section['id'] ); ?>" class="ff-policy-section">
						<h2 class="ff-policy-section__heading"><?php echo esc_html( ( $i + 1 ) . '. ' . $section['title'] ); ?></h2>
						<?php echo wp_kses_post( $section['html'] ?? foxfire_legal_section_html( $section ) ); ?>
					</section>
				<?php endforeach; ?>
			</div>
			<nav class="ff-policy-related-links" aria-label="<?php esc_attr_e( 'Related legal pages', 'foxfire-child' ); ?>">
				<p class="ff-policy-related-links__label"><?php esc_html_e( 'Related', 'foxfire-child' ); ?></p>
				<ul class="ff-policy-related-links__list">
					<?php foreach ( array( 'terms' => 'terms-and-conditions', 'privacy' => 'privacy-policy', 'refund' => 'refund-and-returns-policy', 'shipping' => 'shipping-policy' ) as $other => $slug ) : ?>
						<?php if ( $other !== $key ) : ?><li><a href="<?php echo esc_url( foxfire_get_page_url( $slug, '/' . $slug . '/' ) ); ?>"><?php echo esc_html( foxfire_legal_policies()[ $other ]['title'] ); ?></a></li><?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
	</main>
	<?php
}
