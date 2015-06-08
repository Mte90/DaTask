<?php

/**
 * Load template files of the plugin also include a filter wo_get_template_part<br>
 * Based on WooCommerce function<br>
 *
 * @package   Wp-Oneanddone
 * @author  Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @copyright 2014 
 * @since    1.0.0
 */
function wo_get_template_part( $slug, $name = '', $include = true ) {
	$template = '';
	$path = plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . 'templates/';
	$plugin = Wp_Oneanddone::get_instance();
	$plugin_slug = $plugin->get_plugin_slug() . '/';

	// Look in yourtheme/slug-name.php and yourtheme/wp-oneanddone/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", $plugin_slug . "{$slug}-{$name}.php" ) );
	} else {
		$template = locate_template( array( "{$slug}.php", $plugin_slug . "{$slug}.php" ) );
	}

	// Get default slug-name.php
	if ( !$template && $name && file_exists( $path . "{$slug}-{$name}.php" ) ) {
		$template = $path . "{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
	if ( !$template ) {
		$template = locate_template( array( "{$slug}.php", $plugin_slug . "{$slug}.php" ) );
	}

	// Allow 3rd party plugin filter template file from their plugin
	$template = apply_filters( 'wo_get_template_part', $template, $slug, $name );

	if ( $template && $include === true ) {
		load_template( $template, false );
	} else if ( $template && $include === false ) {
		return $template;
	}
}

/*
 * Echo the subtitle of the task
 * 
 * @since    1.0.0
 */

function the_task_subtitle() {
	$plugin = Wp_Oneanddone::get_instance();
	echo get_post_meta( get_the_ID(), '_task_' . $plugin->get_plugin_slug() . '_subtitle', true );
}

/*
 * Get stared button
 * 
 * @since    1.0.0
 */

function get_started_button() {
	if ( is_user_logged_in() ) {
		?>
		<form method="post" action="/en-US/tasks/3/start/">
		<?php wp_nonce_field( 'name_of_my_action', 'name_of_nonce_field' ); ?>
			<button type="submit" class="button" id="get-started"><?php _e( 'Complete task' ); ?></button>
			<button type="submit" class="button" id="save-for-later"><?php _e( 'Save for later' ); ?></button>
		</form>
		<?php
	}
}
