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
function datask_buttons() {
	$plugin = DaTask::get_instance();
	if ( is_user_logged_in() ) {
		?>
		<div class="dt-buttons">
		    <?php wp_nonce_field( 'dt-task-action', 'dt-task-nonce' ); ?>
		    <button type="submit" class="button btn btn-primary complete <?php
		    if ( has_task( get_the_ID() ) && !has_later_task( get_the_ID() ) ) {
			    echo 'disabled';
		    }
		    ?>" id="complete-task" data-complete="<?php the_ID(); ?>"><i class="dt-refresh-hide fa fa-refresh"></i>
			    <?php
			    if ( has_later_task( get_the_ID() ) ) {
				    echo '<i class="fa fa-exclamation-circle"></i>';
			    }
			    if ( has_task( get_the_ID() ) && !has_later_task( get_the_ID() ) ) {
				    echo '<i class="fa fa-check"></i>';
			    }
			    ?><?php _e( 'Complete this task', $plugin->get_plugin_slug() ); ?></button>
		    <button type="submit" class="button btn btn-secondary save-later <?php
		    if ( has_later_task( get_the_ID() ) ) {
			    echo 'disabled';
		    }
		    ?>" id="save-for-later" data-save-later="<?php the_ID(); ?>"><i class="dt-refresh-hide fa fa-refresh"></i>
			    <?php
			    if ( has_later_task( get_the_ID() ) ) {
				    echo '<i class="fa fa-check"></i>';
			    }
			    ?><?php _e( 'Save for later', $plugin->get_plugin_slug() ); ?></button>
		    <button type="submit" class="button btn btn-warning remove <?php
		    if ( has_task( get_the_ID() ) && has_later_task( get_the_ID() ) ) {
			    echo 'disabled';
		    }
		    ?>" id="remove-task" data-remove="<?php the_ID(); ?>"><i class="dt-refresh-hide fa fa-refresh"></i><?php _e( 'Remove complete task', $plugin->get_plugin_slug() ); ?></button>
		</div>
		<?php
	} else {
		echo '<h3 class="alert alert-danger">';
		_e( 'Save your history of tasks done or in progress with a free account!', $plugin->get_plugin_slug() );
		echo '</h3>';
	}
}

/**
 * User contact form
 * 
 * @since    1.0.0
 */
function datask_user_form() {
	if ( is_user_logged_in() ) {
		$user = get_user_by( 'login', get_user_of_profile() );
		$current_user = wp_get_current_user();
		if ( $user->roles[ 0 ] != 'subscriber' && $current_user->user_login !== $user->user_login) {
			$plugin = DaTask::get_instance();
			$content = '<div class="panel panel-warning" id="user-contact-form">';
			$content .= '<div class="panel-heading">';
			$content .= __( 'Contact', $plugin->get_plugin_slug() ) . ' ' . $user->display_name;
			$content .= '</div>';
			$content .= '<div class="panel-content">';
			$content .= '<div class="form-group"><textarea class="form-control" name="datask-email-subject" cols="45" rows="8" aria-required="true" autocomplete="off"></textarea></div>';
			$content .= wp_nonce_field( 'dt_contact_user', 'dt_user_nonce', true, false );
			$content .= '<button type="submit" data-user="' . get_user_of_profile() . '" class="button btn btn-warning"><i class="dashicons-email-alt"></i>' . __( 'Sent', $plugin->get_plugin_slug() ) . '</button>';
			$content .= '</div>';
			$content .= '</div>';
			echo $content;
		}
	}
}
