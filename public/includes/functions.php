<?php

//Add the user id on the task post types and the task post types in the user meta
function set_completed_task_for_user_id( $user_id, $task_id ) {
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
}

function get_tasks_completed() {
	if ( username_exists( get_user_of_profile() ) ) {
		$plugin = Wp_Oneanddone::get_instance();
		$user_id = get_user_by( 'login', get_user_of_profile() );
		$user_id = $user_id->data->ID;
		echo '<h3>' . __( '%d Tasks Completed', $plugin->get_plugin_slug() ) . '</h3>';
		$tasks_user = get_tasks_by_user( $user_id );
		$task_implode = array_keys( $tasks_user );
		$tasks = new WP_Query( array(
		    'post_type' => 'task',
		    'post__in' => $task_implode ) );
		echo '<ul>';
		foreach ( $tasks->posts as $task ) {
			echo '<li><a href="' . get_permalink( $task->ID ) . '">' . $task->post_title . '</a></li>';
		}
		echo '</ul>';
		wp_reset_postdata();
	}
}

function get_user_of_profile() {
	global $wp_query;
	if ( array_key_exists( 'member', $wp_query->query_vars ) && username_exists( $wp_query->query[ 'member' ] ) ) {
		return $wp_query->query[ 'member' ];
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
