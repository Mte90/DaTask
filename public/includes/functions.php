<?php

//Add the user id on the task post types and the task post types in the user meta
function set_completed_task_for_user_id( $user_id, $task_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	$users = get_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users' );
	if ( !isset( $users[ $user_id ] ) ) {
		$users[ $user_id ] = true;
		update_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users', json_encode( $users ) );
	}
	$tasks_of_user = get_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks' ); 
	if ( !isset( $tasks_of_user[ $task_id ] ) ) {
		$tasks_of_user[ $task_id ] = true;
		update_user_meta( $user_id, '_task_' . $plugin->get_plugin_slug() . '_tasks', json_encode( $tasks_of_user ) );
	}
}
