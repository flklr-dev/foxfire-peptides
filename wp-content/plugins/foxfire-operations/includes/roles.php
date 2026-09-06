<?php
/**
 * Foxfire Operations role and capability ownership.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Capabilities explicitly denied to routine store operators.
 *
 * WooCommerce's Shop Manager role already omits most of these. Removing them
 * explicitly prevents privilege drift caused by another plugin or role editor.
 *
 * @return string[]
 */
function foxfire_operations_forbidden_manager_capabilities(): array {
	return array(
		'activate_plugins',
		'create_users',
		'delete_plugins',
		'delete_themes',
		'delete_users',
		'edit_dashboard',
		'edit_files',
		'edit_plugins',
		'edit_theme_options',
		'edit_themes',
		'export',
		'import',
		'install_languages',
		'install_plugins',
		'install_themes',
		'list_users',
		'manage_network',
		'manage_options',
		'promote_users',
		'remove_users',
		'switch_themes',
		'unfiltered_html',
		'update_core',
		'update_plugins',
		'update_themes',
	);
}

/**
 * Grant Foxfire-owned capabilities and harden the WooCommerce Shop Manager.
 */
function foxfire_operations_sync_role_capabilities(): void {
	$administrator = get_role( 'administrator' );
	if ( $administrator instanceof WP_Role ) {
		$administrator->add_cap( FOXFIRE_OPERATIONS_CAPABILITY );
		$administrator->add_cap( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY );
	}

	$shop_manager = get_role( 'shop_manager' );
	if ( ! $shop_manager instanceof WP_Role ) {
		return;
	}

	$shop_manager->add_cap( FOXFIRE_OPERATIONS_CAPABILITY );
	$shop_manager->remove_cap( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY );

	foreach ( foxfire_operations_forbidden_manager_capabilities() as $capability ) {
		$shop_manager->remove_cap( $capability );
	}
}

/**
 * Remove only the custom capabilities introduced by this plugin.
 */
function foxfire_operations_remove_role_capabilities(): void {
	foreach ( array( 'administrator', 'shop_manager' ) as $role_name ) {
		$role = get_role( $role_name );
		if ( ! $role instanceof WP_Role ) {
			continue;
		}

		$role->remove_cap( FOXFIRE_OPERATIONS_CAPABILITY );
		$role->remove_cap( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY );
	}
}

/**
 * Whether the current operator is intentionally restricted from sensitive settings.
 */
function foxfire_operations_is_restricted_manager(): bool {
	return current_user_can( FOXFIRE_OPERATIONS_CAPABILITY )
		&& ! current_user_can( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY );
}

/**
 * Enforce the Shop Manager boundary at runtime as defense against role drift.
 *
 * Another plugin may accidentally grant a dangerous capability after activation.
 * A non-Administrator Shop Manager must still never receive those capabilities.
 *
 * @param array<string, bool> $allcaps Effective capabilities.
 * @param string[]            $caps    Primitive capabilities being checked.
 * @param array<int, mixed>   $args    Capability arguments.
 * @param WP_User             $user    User being evaluated.
 * @return array<string, bool>
 */
function foxfire_operations_enforce_manager_boundary( array $allcaps, array $caps, array $args, WP_User $user ): array {
	unset( $caps, $args );

	$is_shop_manager = in_array( 'shop_manager', (array) $user->roles, true );
	$is_admin         = in_array( 'administrator', (array) $user->roles, true );
	if ( ! $is_shop_manager || $is_admin ) {
		return $allcaps;
	}

	$allcaps[ FOXFIRE_OPERATIONS_CAPABILITY ]           = true;
	$allcaps[ FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ] = false;

	foreach ( foxfire_operations_forbidden_manager_capabilities() as $capability ) {
		$allcaps[ $capability ] = false;
	}

	return $allcaps;
}
add_filter( 'user_has_cap', 'foxfire_operations_enforce_manager_boundary', 20, 4 );
