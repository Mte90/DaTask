<?php

//Add the user id on the task post types and the task post types in the user meta
function set_completed_task_for_user_id( $user_id, $task_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	$users = unserialize( get_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users', true ) );
	if ( !isset( $users[ $user_id ] ) ) {
		$users[ $user_id ] = true;
		update_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users', serialize( $users ) );
	}
	$tasks_of_user = unserialize( get_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks', true ) );
	if ( !isset( $tasks_of_user[ $task_id ] ) ) {
		$tasks_of_user[ $task_id ] = true;
		update_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks', serialize( $tasks_of_user ) );
	}
}

function get_tasks_completed() {
	global $wp_query;
	$plugin = Wp_Oneanddone::get_instance();
	if ( username_exists( $wp_query->query[ 'member' ] ) ) {
		$user_id = get_user_by( 'login', $wp_query->query[ 'member' ] );
		$user_id = $user_id->data->ID;
		print_r( get_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks', true ) );
	}
}
