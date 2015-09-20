<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
// If uninstall not called from WordPress, then exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb, $wp_roles;

$plugin_roles = array(
    'administrator' => array(
	'edit_tasks' => true,
	'edit_others_tasks' => true,
    ), 'editor' => array(
	'edit_demo' => true,
	'edit_others_demo' => true,
    ), 'author' => array(
	'edit_demo' => true,
	'edit_others_demo' => false,
    ), 'subscriber' => array(
	'edit_demo' => false,
	'edit_others_demo' => false,
    ),
);

if ( is_multisite() ) {
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );

	if ( $blogs ) {

		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );

			if ( !isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles;
			}

			foreach ( $wp_roles->role_names as $role => $label ) {
				// If the role is a standard role, map the default caps, otherwise, map as a subscriber
				$caps = ( array_key_exists( $role, $plugin_roles ) ) ? $plugin_roles[ $role ] : $plugin_roles[ 'subscriber' ];

				// Loop and assign
				foreach ( $caps as $cap => $grant ) {
					//check to see if the user already has this capability, if so, don't re-add as that would override grant
					if ( !isset( $wp_roles->roles[ $role ][ 'capabilities' ][ $cap ] ) ) {
						$wp_roles->remove_cap( $cap );
					}
				}
			}

			restore_current_blog();
		}
	}
} else {
	if ( !isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles;
	}

	foreach ( $wp_roles->role_names as $role => $label ) {
		// If the role is a standard role, map the default caps, otherwise, map as a subscriber
		$caps = ( array_key_exists( $role, $plugin_roles ) ) ? $plugin_roles[ $role ] : $plugin_roles[ 'subscriber' ];

		// Loop and assign
		foreach ( $caps as $cap => $grant ) {
			// Check to see if the user already has this capability, if so, don't re-add as that would override grant
			if ( !isset( $wp_roles->roles[ $role ][ 'capabilities' ][ $cap ] ) ) {
				$wp_roles->remove_cap( $cap );
			}
		}
	}
}

delete_option( $this->plugin_slug . '-settings' );
delete_option( $this->plugin_slug . '-settings-extra' );