<?php

function set_completed_task_for_user_id( $user_id, $task_id ) {
	$plugin = Wp_Oneanddone::get_instance();
	$users = get_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users' );
	if ( !isset( $users[ $user_id ] ) ) {
		$users[ $user_id ] = true;
		update_post_meta( $task_id, '_task_' . $plugin->get_plugin_slug() . '_users', json_encode( $users ) );
	}
}
