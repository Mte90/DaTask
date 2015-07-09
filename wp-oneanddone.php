<?php

/**
 * @package   Wp_Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2014 GPL
 *
 * @wordpress-plugin
 * Plugin Name:       WP-OneAndDone
 * Plugin URI:        @TODO
 * Description:       @TODO
 * Version:           1.0.0
 * Author:            Mte90
 * Author URI:        http://mte90.net
 * Text Domain:       wp-oneanddone
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WordPress-Plugin-Boilerplate-Powered: v1.1.0
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}

/* ----------------------------------------------------------------------------*
 * Public-Facing Functionality
 * ---------------------------------------------------------------------------- */

/*
 * Load library for simple and fast creation of Taxonomy and Custom Post Type
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/Taxonomy_Core/Taxonomy_Core.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/CPT_Core/CPT_Core.php' );

/*
 * Load template system
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/template.php' );

/*
 * Load Language wrapper function for WPML/Ceceppa Multilingua/Polylang
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/language.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/class-wp-oneanddone.php' );

/*
 * Load Widgets Helper
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/Widgets-Helper/wph-widget-class.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/recents-tasks.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/most-task-done.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Wp_Oneanddone', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Wp_Oneanddone', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'Wp_Oneanddone', 'get_instance' ) );

/* ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 * ---------------------------------------------------------------------------- */
if ( is_admin() ) {
	if ( (!defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-oneanddone-admin.php' );
		add_action( 'plugins_loaded', array( 'Wp_Oneanddone_Admin', 'get_instance' ) );
	} else {
		require_once( plugin_dir_path( __FILE__ ) . '/admin/includes/cmb2_post_search_field.php' );
	}
}
