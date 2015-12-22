<?php

/**
 * Function wrapper for register,unregister,get language and get string for WPML, Polylang and Ceceppa Multilingua
 * 
 * example use https://gist.github.com/Mte90/fe687ceed408ab743238
 * 
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @copyright 2015 
 */

/**
 * Load the plugin text domain for translation.
 *
 * @since    1.0.0
 */
function dt_load_plugin_textdomain() {
	$plugin = DaTask::get_instance();
	$domain = $plugin->get_plugin_slug();
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'dt_load_plugin_textdomain', 1 );
