<?php

/**
 * DaTask Functions
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */

/**
 * Add the user id on the task post types and the task post types in the user meta
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @param     integer $task_id ID of task post type.
 * @return    bool true
 */
function dt_set_completed_task_for_user_id( $user_id, $task_id ) {
	$plugin = DaTask::get_instance();
	$users_of_task = get_users_by_task( $task_id );
	if ( !isset( $users_of_task[ $user_id ] ) ) {
		$users_of_task[ $user_id ] = true;
		update_post_meta( $task_id, $plugin->get_fields( 'users_of_task' ), serialize( $users_of_task ) );
	}
	$counter = get_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), true );
	if ( empty( $counter ) ) {
		$counter = 1;
	} else {
		$counter++;
	}
	update_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), $counter );
	$tasks_of_user = get_tasks_by_user( $user_id );
	if ( !isset( $tasks_of_user[ $task_id ] ) ) {
		$tasks_of_user[ $task_id ] = time();
		update_user_meta( $user_id, $plugin->get_fields( 'tasks_done_of_user' ), serialize( $tasks_of_user ) );
	}
	$tasks_later_of_user = get_tasks_later_by_user( $user_id );
	if ( isset( $tasks_later_of_user[ $task_id ] ) ) {
		unset( $tasks_later_of_user[ $task_id ] );
		update_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), serialize( $tasks_later_of_user ) );
	}

	if ( class_exists( 'BadgeOS' ) ) {
		do_action( 'datask_badgeos_trigger' );
	}

	/*
	 * Fires before the end of function `dt_set_completed_task_for_user_id`
	 *
	 * @since 1.0.0
	 */
	do_action( 'dt_set_completed_task' );
	return true;
}

/**
 * Add in the profile the ids of the task for later
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @param     integer $task_id ID of task post type.
 * @return    bool true
 */
function dt_set_task_later_for_user_id( $user_id, $task_id ) {
	$plugin = DaTask::get_instance();
	$tasks_later_of_user = get_tasks_later_by_user( $user_id );
	if ( !isset( $tasks_later_of_user[ $task_id ] ) ) {
		$tasks_later_of_user[ $task_id ] = time();
		update_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), serialize( $tasks_later_of_user ) );
	}

	/*
	 * Fires before the end of function `dt_set_task_later_for_user_id`
	 *
	 * @since 1.0.0
	 */
	do_action( 'dt_set_task_later' );
	return true;
}

/**
 * Add the user id on the task post types and the task post types in the user meta
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @param     integer $task_id ID of task post type.
 * @return    bool true
 */
function dt_remove_complete_task_for_user_id( $user_id, $task_id ) {
	$plugin = DaTask::get_instance();
	$users_of_task = get_users_by_task( $task_id );
	if ( isset( $users_of_task[ $user_id ] ) ) {
		unset( $users_of_task[ $user_id ] );
		update_post_meta( $task_id, $plugin->get_fields( 'users_of_task' ), serialize( $users_of_task ) );
	}
	$counter = get_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), true );
	if ( empty( $counter ) ) {
		$counter = 1;
	} else {
		$counter--;
	}
	update_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), $counter );
	$tasks_of_user = get_tasks_by_user( $user_id );
	if ( isset( $tasks_of_user[ $task_id ] ) ) {
		unset( $tasks_of_user[ $task_id ] );
		update_user_meta( $user_id, $plugin->get_fields( 'tasks_done_of_user' ), serialize( $tasks_of_user ) );
	}

	/*
	 * Fires before the end of function `dt_set_completed_task_for_user_id`
	 *
	 * @since 1.0.0
	 */
	do_action( 'dt_remove_complete_task' );
	return true;
}

/**
 * Get the task done from the user with html
 *
 * @since     1.0.0
 *
 * @return    @string html
 */
function dt_get_tasks_completed() {
	$plugin = DaTask::get_instance();
	$print = '';
	if ( username_exists( get_user_of_profile() ) ) {
		$user_id = get_user_by( 'login', get_user_of_profile() );
		$user_id = $user_id->data->ID;
		$tasks_user = get_tasks_by_user( $user_id );
		if ( !empty( $tasks_user ) ) {
			$tasks_user = array_reverse( $tasks_user, true );
			$print = '<div class="panel panel-success">';
			$print .= '<div class="panel-heading">';
			$print .= sprintf( __( '%d Tasks Completed', $plugin->get_plugin_slug() ), count( $tasks_user ) );
			$print .= '</div>';
			$print .= '<div class="panel-content">';
			$task_implode = array_keys( $tasks_user );
			$tasks = new WP_Query( array(
			    'post_type' => 'task',
			    'post__in' => $task_implode,
			    'orderby' => 'post__in',
			    'posts_per_page' => -1 ) );
			$print .= '<ul>';
			foreach ( $tasks->posts as $task ) {
				$date = '';
				if ( strlen( $tasks_user[ $task->ID ] ) > 2 ) {
					$date = ' - ' . date_i18n( get_option( 'date_format' ), $tasks_user[ $task->ID ] );
				}

				$print .= '<li><a href="' . get_permalink( $task->ID ) . '">' . $task->post_title . '</a>' . $date . '</li>';
			}
			$print .= '</ul>';
			$print .= '</div>';
			$print .= '</div>';
			wp_reset_postdata();

			/*
			 * Filter the box with task done
			 *
			 * @since 1.0.0
			 *
			 * @param string $html the html output
			 */
			$print = apply_filters( 'dt_get_completed_task', $print );
		} else {
			$print .= '<h5>';
			$print .= __( 'Nothing task done :(', $plugin->get_plugin_slug() );
			$print .= '</h5>';
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
 */
function dt_tasks_completed() {
	echo dt_get_tasks_completed();
}

/**
 * Get the task later from the user with html
 *
 * @since     1.0.0
 * @param     string $user ID of the user.
 * @return    string html
 */
function dt_get_tasks_later( $user = NULL ) {
	if ( $user === NULL ) {
		$user = get_user_of_profile();
	}
	$plugin = DaTask::get_instance();
	$print = '';
	if ( username_exists( $user ) ) {
		$current_user = wp_get_current_user();
		if ( $current_user->user_login === $user ) {
			$plugin = DaTask::get_instance();
			$user_id = get_user_by( 'login', $user );
			$user_id = $user_id->data->ID;
			$tasks_later_user = array_reverse( get_tasks_later_by_user( $user_id ), true );
			$print = '<div class="panel panel-danger">';
			$print .= '<div class="panel-heading">';
			$print .= __( 'Tasks in progress', $plugin->get_plugin_slug() );
			$print .= '</div>';
			$print .= '<div class="panel-content">';
			if ( !empty( $tasks_later_user ) ) {
				$tasks_later_user = array_reverse( $tasks_later_user, true );
				$task_implode = array_keys( $tasks_later_user );
				$tasks = new WP_Query( array(
				    'post_type' => 'task',
				    'post__in' => $task_implode,
				    'orderby' => 'post__in',
				    'posts_per_page' => -1 ) );
				$print .= '<ul>';
				foreach ( $tasks->posts as $task ) {
					$area = get_the_terms( $task->ID, 'task-area' );
					$minute = get_the_terms( $task->ID, 'task-minute' );
					$print .= '<li><a href="' . get_permalink( $task->ID ) . '">' . $task->post_title . '</a> - ' . $area[ 0 ]->name . ' - ' . $minute[ 0 ]->name . ' ' . __( 'minute estimated', $plugin->get_plugin_slug() ) . '</li>';
				}
				$print .= '</ul>';
				wp_reset_postdata();
				/*
				 * Filter the box with task later
				 *
				 * @since 1.0.0
				 *
				 * @param string $html the html output
				 */
				$print = apply_filters( 'dt_get_task_later', $print );
			} else {
				$print .= __( "You don't have any task to do! Pick one!", $plugin->get_plugin_slug() );
			}
			$print .= '</div>';
			$print .= '</div>';
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
 * @param     string $user ID of the user.
 */
function dt_tasks_later( $user = NULL ) {
	echo dt_get_tasks_later( $user );
}

/**
 * Print the task later from the user with html
 *
 * @since     1.0.0
 *
 * @return    @string|NULL value Nick of the user
 */
function get_user_of_profile() {
	global $wp_query;
	// Get nick from the url of the page
	if ( array_key_exists( 'member', $wp_query->query_vars ) && username_exists( $wp_query->query[ 'member' ] ) ) {
		return $wp_query->query[ 'member' ];
		// If the url don't have the nick get the actual
	} elseif ( (isset( $wp_query->query[ 'name' ] ) && $wp_query->query[ 'name' ] === 'member') || (isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member') ) {
		$current_user = wp_get_current_user();
		return $current_user->user_login;
		// Else null
	} elseif ( array_key_exists( 'member-feed', $wp_query->query_vars ) && username_exists( $wp_query->query[ 'member-feed' ] ) ) {
		return $wp_query->query[ 'member-feed' ];
		// If the url don't have the nick get the actual
	} else {
		return NULL;
	}
}

/**
 * Return the task ids of the user
 *
 * @since     1.0.0
 * 
 * @param     integer $user_id ID of the user.
 *
 * @return    array the ids
 */
function get_tasks_by_user( $user_id ) {
	$plugin = DaTask::get_instance();
	return unserialize( get_user_meta( $user_id, $plugin->get_fields( 'tasks_done_of_user' ), true ) );
}

/**
 * Return the task later ids of the user
 *
 * @since     1.0.0
 * 
 * @param     integer $user_id ID of the user.
 *
 * @return    array the ids
 */
function get_tasks_later_by_user( $user_id ) {
	$plugin = DaTask::get_instance();
	return unserialize( get_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), true ) );
}

/**
 * Return the user ids by task
 *
 * @since     1.0.0
 * 
 * @param     integer $task_id ID of the user.
 *
 * @return    array the ids
 */
function get_users_by_task( $task_id ) {
	$plugin = DaTask::get_instance();
	return unserialize( get_post_meta( $task_id, $plugin->get_fields( 'users_of_task' ), true ) );
}

/**
 * Check if the user have done the task
 *
 * @since     1.0.0
 * 
 * @param     integer $task_id ID of the task.
 * @param     integer $user_id ID of the user.
 *
 * @return    boolean
 */
function has_task( $task_id, $user_id = NULL ) {
	if ( $user_id === NULL ) {
		$user_id = get_current_user_id();
	}
	$tasks = get_tasks_by_user( $user_id );
	if ( isset( $tasks[ $task_id ] ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if the user have the task later
 *
 * @since     1.0.0
 * 
 * @param     integer $task_id ID of the task.
 * @param     integer $user_id ID of the user.
 *
 * @return    boolean
 */
function has_later_task( $task_id, $user_id = NULL ) {
	if ( $user_id === NULL ) {
		$user_id = get_current_user_id();
	}
	$tasks = get_tasks_later_by_user( $user_id );
	if ( isset( $tasks[ $task_id ] ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Get list of Badge of BadgeOS 
 * Based on https://gist.github.com/tw2113/6c31366d094eee6d5151
 *
 * @since     1.1.0
 * 
 * @param     integer $user ID of the user.
 *
 * @return    string
 */
function datask_badgeos_user_achievements( $user ) {
	if ( class_exists( 'BadgeOS' ) ) {
		$plugin = DaTask::get_instance();
		$output = '';
		$achievements = array_unique( badgeos_get_user_earned_achievement_ids( $user, '' ) );
		$output = '<div class="panel panel-info badge-users">';
		$output .= '<div class="panel-heading">';
		$output .= __( 'Badge Earned by the user', $plugin->get_plugin_slug() );
		$output .= '</div>';
		$output .= '<div class="panel-content">';
		if ( !empty( $achievements ) ) {
			$output .= '<ul>';
			foreach ( $achievements as $achievement_id ) {
				$output .= '<li><a href="' . get_permalink( $achievement_id ) . '">' . badgeos_get_achievement_post_thumbnail( $achievement_id ) . '</a></li>';
			}
			$output .= '</ul>';
		}
		$output .= '</div>';
		$output .= '</div>';
		echo $output;
	}
}
