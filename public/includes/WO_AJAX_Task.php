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
 * This class contain all the ajax requests for the task system.
 *
 * @package Wp_Oneanddone
 * @author  Mte90 <mte90net@gmail.com>
 */
class WO_AJAX_Task {

	/**
	 * Initialize the class with allo the hooks
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		add_action( 'wp_ajax_wo_complete_task', array( $this, 'wo_complete_task' ) );
		add_action( 'wp_ajax_wo_task_later', array( $this, 'wo_task_later' ) );
	}

	/**
	 * Add a complete task
	 *
	 * @since    1.0.0
	 *
	 * @return    result
	 */
	public function wo_complete_task() {
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
		} else {
			echo 'error!';
		}
		wp_die();
	}

	/**
	 * Add a task later
	 *
	 * @since    1.0.0
	 *
	 * @return    result
	 */
	public function wo_task_later() {
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
		} else {
			echo 'error!';
		}
		wp_die();
	}

}

new WO_AJAX_Task();