<?php

/**
 * WP-OneAndDone.
 *
 * @package   Wp_Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */

/**
 * Add the user id on the task post types and the task post types in the user meta
 *
 * @since     1.0.0
 *
 * @return    @bool true
 */
function wo_set_completed_task_for_user_id( $user_id, $task_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	$users_of_task = get_users_by_task( $task_id );
	if ( !isset( $users_of_task[ $user_id ] ) ) {
		$users_of_task[ $user_id ] = true;
		update_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users', serialize( $users_of_task ) );
	}
	$tasks_of_user = get_tasks_by_user( $user_id );
	if ( !isset( $tasks_of_user[ $task_id ] ) ) {
		$tasks_of_user[ $task_id ] = true;
		update_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks', serialize( $tasks_of_user ) );
	}
	//Add the action for create plugins
	do_action( 'wo-set-completed-task' );
	return true;
}

/**
 * Add in the profile the ids of the task for later
 *
 * @since     1.0.0
 *
 * @return    @bool true
 */
function wo_set_task_later_for_user_id( $user_id, $task_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	$tasks_later_of_user = get_tasks_later_by_user( $user_id );
	if ( !isset( $tasks_later_of_user[ $task_id ] ) ) {
		$tasks_later_of_user[ $task_id ] = true;
		update_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks_done', serialize( $tasks_later_of_user ) );
	}
	//Add the action for create plugins
	do_action( 'wo-set-task-later' );
	return true;
}

/**
 * Get the task done from the user with html
 *
 * @since     1.0.0
 *
 * @return    @string html
 */
function wo_get_tasks_completed() {
	if ( username_exists( get_user_of_profile() ) ) {
		$plugin = Wp_Oneanddone::get_instance();
		$user_id = get_user_by( 'login', get_user_of_profile() );
		$user_id = $user_id->data->ID;
		$tasks_user = get_tasks_by_user( $user_id );
		if ( !empty( $tasks_user ) ) {
			$print = '<div class="panel panel-success">';
			$print .= '<div class="panel-heading">';
			$print .= sprintf( __( '%d Tasks Completed', $plugin->get_plugin_slug() ), count( $tasks_user ) );
			$print .= '</div>';
			$print .= '<div class="panel-content">';
			$task_implode = array_keys( $tasks_user );
			$tasks = new WP_Query( array(
			    'post_type' => 'task',
			    'post__in' => $task_implode ) );
			$print .= '<ul>';
			foreach ( $tasks->posts as $task ) {
				$print .= '<li><a href="' . get_permalink( $task->ID ) . '">' . $task->post_title . '</a></li>';
			}
			$print .= '</ul>';
			$print .= '</div>';
			$print .= '</div>';
			wp_reset_postdata();
			//Add the filter for improve this output
			$print = apply_filters( 'wo-get-completed-task', $print );
		}
	} else {
		$print = __( "This profile not exist!", $plugin->get_plugin_slug() );
	}
	return $print;
}

/**
 * Print the task done from the user with html
 *
 * @since     1.0.0
 *
 * @return    @string html
 */
function wo_tasks_completed() {
	echo wo_get_tasks_completed();
}

/**
 * Get the task later from the user with html
 *
 * @since     1.0.0
 *
 * @return    @string html
 */
function wo_get_tasks_later( $user = NULL ) {
	if ( $user === NULL ) {
		$user = get_user_of_profile();
	}
	if ( username_exists( $user ) ) {
		$current_user = wp_get_current_user();
		if ( $current_user->user_login === $user ) {
			$plugin = Wp_Oneanddone::get_instance();
			$user_id = get_user_by( 'login', $user );
			$user_id = $user_id->data->ID;
			$tasks_later_user = get_tasks_later_by_user( $user_id );
			if ( !empty( $tasks_later_user ) ) {
				$print = '<div class="panel panel-danger">';
				$print .= '<div class="panel-heading">';
				$print .= __( 'Tasks in progress', $plugin->get_plugin_slug() );
				$print .= '</div>';
				$print .= '<div class="panel-content">';
				$task_implode = array_keys( $tasks_later_user );
				$tasks = new WP_Query( array(
				    'post_type' => 'task',
				    'post__in' => $task_implode ) );
				$print .= '<ul>';
				foreach ( $tasks->posts as $task ) {
					$print .= '<li><a href="' . get_permalink( $task->ID ) . '">' . $task->post_title . '</a></li>';
				}
				$print .= '</ul>';
				$print .= '</div>';
				$print .= '</div>';
				wp_reset_postdata();
				//Add the filter for improve this output
				$print = apply_filters( 'wo-get-task-later', $print );
			}
		}
	} else {
		$print = __( "This profile not exist!", $plugin->get_plugin_slug() );
	}
	return $print;
}

/**
 * Print the task later from the user with html
 *
 * @since     1.0.0
 *
 * @return    @string html
 */
function wo_tasks_later( $user = NULL ) {
	echo wo_get_tasks_later( $user );
}

function get_user_of_profile() {
	global $wp_query;
	if ( array_key_exists( 'member', $wp_query->query_vars ) && username_exists( $wp_query->query[ 'member' ] ) ) {
		return $wp_query->query[ 'member' ];
	} elseif ( (isset( $wp_query->query[ 'name' ] ) && $wp_query->query[ 'name' ] === 'member') || (isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member') ) {
		$current_user = wp_get_current_user();
		return $current_user->user_login;
	} else {
		return NULL;
	}
}

function get_tasks_by_user( $user_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	return unserialize( get_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks', true ) );
}

function get_users_by_task( $task_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	return unserialize( get_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users', true ) );
}

function get_tasks_later_by_user( $user_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	return unserialize( get_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks_done', true ) );
}
