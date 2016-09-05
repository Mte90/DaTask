<?php

/**
 * This class contain the Uninstall code
 *
 * @package   DaTask
 * @author    Daniele Scasciafratte <mte90net@gmail.com>
 * @license   GPL 2.0+
 * @link      http://mte90.net
 * @copyright 2015-2016
 */
class DT_Uninstall {

  /**
   * Initialize the snippet
   */
  function __construct() {
    add_action( 'after_uninstall', array( $this, 'uninstall_hook' ) );
  }

  function uninstall_hook() {
    global $wpdb;
    if ( is_multisite() ) {
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {
	  foreach ( $blogs as $blog ) {
	    switch_to_blog( $blog[ 'blog_id' ] );
	    $this->uninstall();
	    restore_current_blog();
	  }
	}
    }
    $this->uninstall();
  }

  function uninstall() {
    global $wp_roles;
    $plugin_roles = DaTask::get_plugin_roles();
    delete_option( 'datask-settings' );
delete_option( 'datask-settings-extra' );
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

}

new DT_Uninstall();
