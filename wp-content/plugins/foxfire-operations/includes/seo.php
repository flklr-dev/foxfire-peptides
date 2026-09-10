<?php
/**
 * Search metadata, crawl controls, and core sitemap hardening.
 *
 * This intentionally provides a small, deterministic baseline rather than
 * attempting to replace the editorial tooling in a dedicated SEO plugin. If a
 * supported SEO plugin is activated, title/meta/canonical/social output stands
 * down automatically so the site never emits competing directives.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Foxfire should own search metadata for this request.
 */
function foxfire_operations_builtin_seo_enabled(): bool {
	$known_seo_plugin_loaded = defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' )
		|| defined( 'SLIM_SEO_VER' );

	/**
	 * Allow a host or future SEO integration to disable the built-in layer.
	 *
	 * @param bool $enabled Whether Foxfire search metadata should be emitted.
	 */
	return (bool) apply_filters( 'foxfire_operations_builtin_seo_enabled', ! $known_seo_plugin_loaded );
}

/**
 * Return a managed content value without coupling this file to load order.
 */
function foxfire_operations_seo_content( string $key, string $fallback ): string {
	if ( function_exists( 'foxfire_operations_get_public_content' ) ) {
		return foxfire_operations_get_public_content( $key, $fallback );
	}

	return $fallback;
}

/**
 * Normalize human-written copy for use in a metadata attribute.
 */
function foxfire_operations_normalize_meta_text( string $value, int $limit = 160 ): string {
	$value = strip_shortcodes( $value );
	$value = html_entity_decode( wp_strip_all_tags( $value, true ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ?: 'UTF-8' );
	$value = trim( (string) preg_replace( '/\s+/u', ' ', $value ) );

	if ( '' === $value || $limit < 1 ) {
		return '';
	}

	$length = function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
	if ( $length <= $limit ) {
		return $value;
	}

	$truncated = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	$at_word   = preg_replace( '/\s+\S*$/u', '', $truncated );

	return trim( is_string( $at_word ) && '' !== $at_word ? $at_word : $truncated );
}

/**
 * Detect internal copy markers that must never become a search snippet.
 */
function foxfire_operations_is_placeholder_meta_text( string $value ): bool {
	return 1 === preg_match( '/\[(?:placeholder|provisional|tbd)(?:[^\]]*)\]|\(tbd\)/iu', $value );
}

/**
 * Check the current page against one or more stable slugs.
 *
 * @param string[] $slugs Page slugs.
 */
function foxfire_operations_is_page_slug( array $slugs ): bool {
	if ( ! is_page() ) {
		return false;
	}

	$object = get_queried_object();

	return $object instanceof WP_Post && in_array( $object->post_name, $slugs, true );
}

/**
 * Commerce and utility pages that must never be search landing pages.
 */
function foxfire_operations_is_noindex_request(): bool {
	if ( is_search() || is_404() || is_feed() || is_trackback() ) {
		return true;
	}

	return ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );
}

/**
 * Build the concise, branded document title for supported public page types.
 */
function foxfire_operations_get_seo_title(): string {
	$brand = foxfire_operations_normalize_meta_text( get_bloginfo( 'name' ), 80 );
	$brand = '' !== $brand ? $brand : 'Foxfire Peptides';
	$base  = '';

	if ( is_front_page() ) {
		$base = foxfire_operations_seo_content( 'seo_home_title', 'Research Peptides & Batch COA Access' );
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$base = foxfire_operations_seo_content( 'seo_shop_title', 'Shop Research Peptides' );
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$base = single_post_title( '', false );
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term_name = single_term_title( '', false );
		$base      = sprintf( __( '%s Research Products', 'foxfire-operations' ), $term_name );
	} elseif ( foxfire_operations_is_page_slug( array( 'testing-coa' ) ) ) {
		$base = foxfire_operations_seo_content( 'seo_testing_title', 'Testing & Certificate of Analysis' );
	} elseif ( foxfire_operations_is_page_slug( array( 'about', 'about-us' ) ) ) {
		$base = foxfire_operations_seo_content( 'seo_about_title', 'About' );
	} elseif ( foxfire_operations_is_page_slug( array( 'contact', 'contact-us' ) ) ) {
		$base = foxfire_operations_seo_content( 'seo_contact_title', 'Contact' );
	} elseif ( is_singular() ) {
		$base = single_post_title( '', false );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$base = single_term_title( '', false );
	} elseif ( is_post_type_archive() ) {
		$base = post_type_archive_title( '', false );
	}

	$base = foxfire_operations_normalize_meta_text( (string) $base, 90 );
	if ( '' === $base ) {
		return '';
	}

	if ( false !== stripos( $base, $brand ) ) {
		return $base;
	}

	return sprintf( '%1$s | %2$s', $base, $brand );
}

/**
 * Override WordPress's generic titles only while the built-in layer is active.
 */
function foxfire_operations_filter_document_title( string $title ): string {
	if ( is_admin() || ! foxfire_operations_builtin_seo_enabled() ) {
		return $title;
	}

	$seo_title = foxfire_operations_get_seo_title();

	return '' !== $seo_title ? $seo_title : $title;
}
add_filter( 'pre_get_document_title', 'foxfire_operations_filter_document_title', 20 );

/**
 * Build a unique description from managed copy or page-specific catalog data.
 */
function foxfire_operations_get_meta_description(): string {
	$description = '';

	if ( is_front_page() ) {
		$description = foxfire_operations_seo_content(
			'seo_home_description',
			'Browse laboratory research peptides with clear product information and access to available batch and Certificate of Analysis documentation.'
		);
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$description = foxfire_operations_seo_content(
			'seo_shop_description',
			'Browse Foxfire Peptides research compounds, compare available vial quantities, and review batch and testing information before ordering.'
		);
	} elseif ( function_exists( 'is_product' ) && is_product() && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product instanceof WC_Product ) {
			$description = $product->get_short_description();
			if ( '' === trim( wp_strip_all_tags( $description ) ) || foxfire_operations_is_placeholder_meta_text( $description ) ) {
				$description = sprintf(
					/* translators: %s: product name. */
					__( '%s laboratory research product with clear ordering information and access to available batch and Certificate of Analysis documentation.', 'foxfire-operations' ),
					$product->get_name()
				);
			}
		}
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$description = term_description( $term );
			if ( '' === trim( wp_strip_all_tags( $description ) ) || foxfire_operations_is_placeholder_meta_text( $description ) ) {
				$description = sprintf(
					/* translators: %s: product category name. */
					__( 'Browse %s research products from Foxfire Peptides with straightforward ordering and available batch testing information.', 'foxfire-operations' ),
					$term->name
				);
			}
		}
	} elseif ( foxfire_operations_is_page_slug( array( 'testing-coa' ) ) ) {
		$description = foxfire_operations_seo_content(
			'seo_testing_description',
			'Look up Foxfire Peptides batch and lot numbers to access available testing records and Certificate of Analysis documentation.'
		);
	} elseif ( foxfire_operations_is_page_slug( array( 'about', 'about-us' ) ) ) {
		$description = foxfire_operations_seo_content(
			'seo_about_description',
			'Learn about Foxfire Peptides and our approach to transparent product information, accessible testing documentation, and dependable service.'
		);
	} elseif ( foxfire_operations_is_page_slug( array( 'contact', 'contact-us' ) ) ) {
		$description = foxfire_operations_seo_content(
			'seo_contact_description',
			'Contact Foxfire Peptides for help with products, batch documentation, existing orders, or general research customer questions.'
		);
	} elseif ( is_page() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$policy_descriptions = array(
				'privacy-policy'            => __( 'Read the Foxfire Peptides Privacy Policy, including how account, order, contact, and website data is collected, used, retained, and protected.', 'foxfire-operations' ),
				'terms-and-conditions'       => __( 'Read the terms and conditions that apply when using the Foxfire Peptides website, customer account, catalog, and ordering services.', 'foxfire-operations' ),
				'refund-and-returns-policy' => __( 'Review the Foxfire Peptides refund and returns policy, including eligibility, issue reporting, review steps, and customer support contact details.', 'foxfire-operations' ),
				'shipping-policy'           => __( 'Review the Foxfire Peptides shipping policy, including order processing, available destinations, rates, tracking, delivery, and support information.', 'foxfire-operations' ),
			);

			$description = $policy_descriptions[ $post->post_name ] ?? ( has_excerpt( $post ) ? get_the_excerpt( $post ) : $post->post_content );
		}
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$description = has_excerpt( $post ) ? get_the_excerpt( $post ) : $post->post_content;
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$description = term_description();
	}

	return foxfire_operations_normalize_meta_text( (string) $description, 160 );
}

/**
 * Return a clean self-referencing canonical for indexable HTML pages.
 */
function foxfire_operations_get_canonical_url(): string {
	if ( foxfire_operations_is_noindex_request() ) {
		return '';
	}

	$url = '';

	if ( is_paged() ) {
		$url = get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
	} elseif ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( function_exists( 'is_shop' ) && is_shop() && function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'shop' );
	} elseif ( is_singular() ) {
		$url = get_permalink( get_queried_object_id() );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term_url = get_term_link( get_queried_object() );
		$url      = is_wp_error( $term_url ) ? '' : $term_url;
	} elseif ( is_post_type_archive() ) {
		$url = get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
	}

	if ( ! is_string( $url ) || '' === $url ) {
		return '';
	}

	return (string) apply_filters( 'foxfire_operations_canonical_url', esc_url_raw( $url ) );
}

/**
 * Select a representative social image for the current page.
 */
function foxfire_operations_get_social_image_url(): string {
	$image_url = '';
	$object_id = get_queried_object_id();

	if ( $object_id > 0 && has_post_thumbnail( $object_id ) ) {
		$image_url = (string) get_the_post_thumbnail_url( $object_id, 'full' );
	} elseif ( function_exists( 'get_stylesheet_directory' ) ) {
		$image_name = foxfire_operations_is_page_slug( array( 'about', 'about-us' ) ) ? 'about-hero-team.webp' : 'hero-peptides.webp';
		$image_path = trailingslashit( get_stylesheet_directory() ) . 'assets/images/' . $image_name;
		if ( file_exists( $image_path ) ) {
			$image_url = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/' . $image_name;
		}
	}

	if ( '' === $image_url ) {
		$image_url = (string) get_site_icon_url( 512 );
	}

	if ( '' === $image_url ) {
		$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
		$image_url      = $custom_logo_id > 0 ? (string) wp_get_attachment_image_url( $custom_logo_id, 'full' ) : '';
	}

	return (string) apply_filters( 'foxfire_operations_social_image_url', esc_url_raw( $image_url ) );
}

/**
 * Print description, canonical, Open Graph, and Twitter metadata.
 */
function foxfire_operations_render_search_metadata(): void {
	if ( is_admin() || ! foxfire_operations_builtin_seo_enabled() || is_feed() || is_robots() || is_404() ) {
		return;
	}

	$title       = foxfire_operations_get_seo_title();
	$title       = '' !== $title ? $title : wp_get_document_title();
	$description = foxfire_operations_is_noindex_request() ? '' : foxfire_operations_get_meta_description();
	$canonical   = foxfire_operations_get_canonical_url();
	$image_url   = foxfire_operations_get_social_image_url();
	$type        = function_exists( 'is_product' ) && is_product() ? 'product' : 'website';

	if ( '' !== $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}

	if ( '' !== $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	}

	if ( foxfire_operations_is_noindex_request() ) {
		return;
	}

	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";

	if ( '' !== $image_url ) {
		echo '<meta property="og:image" content="' . esc_url( $image_url ) . '">' . "\n";
	}

	echo '<meta name="twitter:card" content="' . esc_attr( '' !== $image_url ? 'summary_large_image' : 'summary' ) . '">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";

	if ( '' !== $image_url ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'foxfire_operations_render_search_metadata', 2 );

/**
 * Replace core's singular-only canonical when Foxfire owns metadata.
 */
function foxfire_operations_prepare_canonical_output(): void {
	if ( foxfire_operations_builtin_seo_enabled() ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
add_action( 'wp', 'foxfire_operations_prepare_canonical_output', 1 );

/**
 * Keep private environments and transactional pages out of search indexes.
 *
 * @param array<string, bool|string> $robots Existing directives.
 * @return array<string, bool|string>
 */
function foxfire_operations_filter_robots( array $robots ): array {
	$private_environment = 'production' !== wp_get_environment_type();

	if ( $private_environment || foxfire_operations_is_noindex_request() ) {
		unset( $robots['index'], $robots['follow'] );
		$robots['noindex']   = true;
		$robots['nofollow']  = true;
		$robots['noarchive'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'foxfire_operations_filter_robots', 20 );

/**
 * Remove transactional WooCommerce pages from the core page sitemap.
 *
 * @param array<string, mixed> $args      Sitemap query arguments.
 * @param string               $post_type Current post type.
 * @return array<string, mixed>
 */
function foxfire_operations_filter_sitemap_pages( array $args, string $post_type ): array {
	if ( 'page' !== $post_type || ! function_exists( 'wc_get_page_id' ) ) {
		return $args;
	}

	$excluded = array_filter(
		array_map(
			'absint',
			array(
				wc_get_page_id( 'cart' ),
				wc_get_page_id( 'checkout' ),
				wc_get_page_id( 'myaccount' ),
			)
		)
	);

	$legacy_slugs = array(
		'about-us'   => 'about',
		'contact-us' => 'contact',
	);

	foreach ( $legacy_slugs as $legacy_slug => $canonical_slug ) {
		$legacy_page    = get_page_by_path( $legacy_slug );
		$canonical_page = get_page_by_path( $canonical_slug );

		if ( $legacy_page instanceof WP_Post && $canonical_page instanceof WP_Post && 'publish' === $canonical_page->post_status ) {
			$excluded[] = (int) $legacy_page->ID;
		}
	}

	$args['post__not_in'] = array_values(
		array_unique(
			array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $excluded )
		)
	);

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'foxfire_operations_filter_sitemap_pages', 20, 2 );

/**
 * Consolidate old public page aliases into their current canonical URLs.
 */
function foxfire_operations_redirect_legacy_page_aliases(): void {
	if ( ! is_page() ) {
		return;
	}

	$current = get_queried_object();
	if ( ! $current instanceof WP_Post ) {
		return;
	}

	$aliases = array(
		'about-us'   => 'about',
		'contact-us' => 'contact',
	);

	if ( ! isset( $aliases[ $current->post_name ] ) ) {
		return;
	}

	$canonical = get_page_by_path( $aliases[ $current->post_name ] );
	if ( ! $canonical instanceof WP_Post || 'publish' !== $canonical->post_status || $canonical->ID === $current->ID ) {
		return;
	}

	wp_safe_redirect( get_permalink( $canonical ), 301, 'Foxfire Operations' );
	exit;
}
add_action( 'template_redirect', 'foxfire_operations_redirect_legacy_page_aliases', 1 );

/**
 * Normalize the status for valid core sitemap routes.
 *
 * The local Apache stack exposes a WordPress core edge case where sitemap XML
 * is complete but inherits a 404 status. Search crawlers rely on the status, so
 * valid index/provider routes are explicitly normalized before core renders.
 */
function foxfire_operations_normalize_sitemap_status(): void {
	$sitemap    = sanitize_key( (string) get_query_var( 'sitemap' ) );
	$stylesheet = sanitize_key( (string) get_query_var( 'sitemap-stylesheet' ) );

	if ( '' === $sitemap && '' === $stylesheet ) {
		return;
	}

	$server = wp_sitemaps_get_server();
	if ( ! $server->sitemaps_enabled() ) {
		return;
	}

	$valid_stylesheet = in_array( $stylesheet, array( 'index', 'sitemap' ), true );
	$valid_sitemap    = 'index' === $sitemap || ( '' !== $sitemap && null !== $server->registry->get_provider( $sitemap ) );

	if ( $valid_stylesheet || $valid_sitemap ) {
		status_header( 200 );

		if ( ! headers_sent() ) {
			header_remove( 'Cache-Control' );
			header_remove( 'Expires' );
			header( 'Cache-Control: public, max-age=300, s-maxage=300' );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 300 ) . ' GMT' );
		}
	}
}
add_action( 'template_redirect', 'foxfire_operations_normalize_sitemap_status', 0 );
