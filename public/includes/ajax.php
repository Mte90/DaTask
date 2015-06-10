<?php

/**
 * @package   Wp-Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2014 GPL
 */

function wo_complete_task() {
	//Based on check_ajax_referer
	if ( isset( $_GET[ '_wpnonce' ] ) ) {
		$nonce = $_GET[ '_wpnonce' ];
	}

	$result = wp_verify_nonce( $nonce, 'wo-task-action' );

	if ( false === $result ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die( -1 );
		} else {
			die( '-1' );
		}
	}
	if ( is_user_logged_in() ) {
		set_completed_task_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
		echo 'done!';
	}
	wp_die();
}

add_action( 'wp_ajax_wo_complete_task', 'wo_complete_task' );

function wo_task_later() {
	//Based on check_ajax_referer
	if ( isset( $_GET[ '_wpnonce' ] ) ) {
		$nonce = $_GET[ '_wpnonce' ];
	}

	$result = wp_verify_nonce( $nonce, 'wo-task-action' );

	if ( false === $result ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die( -1 );
		} else {
			die( '-1' );
		}
	}
	if ( is_user_logged_in() ) {
		set_task_later_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
		echo 'done!';
	}
	wp_die();
}

add_action( 'wp_ajax_wo_task_later', 'wo_task_later' );