<?php

/**
 * Load template files of the plugin also include a filter dt_get_template_part<br>
 * Based on WooCommerce function<br>
 *
 * @package   DaTask
 * @author  Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @copyright 2014 
 * @since    1.0.0
 */
function dt_get_template_part( $slug, $name = '', $include = true ) {
	$template = '';
	$path = plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . 'templates/';
	$plugin = DaTask::get_instance();
	$plugin_slug = $plugin->get_plugin_slug() . '/templates/';

	// Look in yourtheme/slug-name.php and yourtheme/wp-datask/slug-name.php
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
	$template = apply_filters( 'dt_get_template_part', $template, $slug, $name );

	if ( $template && $include === true ) {
		load_template( $template, false );
	} else if ( $template && $include === false ) {
		return $template;
	}
}

/**
 * Echo the subtitle of the task
 * 
 * @param    @bool print or not to print
 * @return   echo or string
 * @since    1.0.0
 */
function the_task_subtitle( $echo = true ) {
	$plugin = DaTask::get_instance();
	if ( $echo ) {
		echo get_post_meta( get_the_ID(), $plugin->get_fields( 'task_subtitle' ), true );
	} else {
		return get_post_meta( get_the_ID(), $plugin->get_fields( 'task_subtitle' ), true );
	}
}

/**
 * Print Task button
 * 
 * @since    1.0.0
 */
function task_buttons() {
	if ( is_user_logged_in() ) {
		?>
		<div class="dt-buttons">
		    <?php wp_nonce_field( 'dt-task-action', 'dt-task-nonce' ); ?>
		    <button type="submit" class="button btn btn-primary complete" id="complete-task" data-complete="<?php the_ID(); ?>"><i class="fa fa-refresh"></i><?php _e( 'Complete task' ); ?></button>
		    <button type="submit" class="button btn btn-secondary save-later" id="save-for-later" data-save-later="<?php the_ID(); ?>"><i class="fa fa-refresh"></i><?php _e( 'Save for later' ); ?></button>
		    <button type="submit" class="button btn btn-warning remove" id="remove-task" data-remove="<?php the_ID(); ?>"><i class="fa fa-refresh"></i><?php _e( 'Remove complete task' ); ?></button>
		</div>
		<?php
	}
}
