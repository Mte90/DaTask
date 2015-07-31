<?php

/**
 * This class contain all the ajax requests for the task system.
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_AJAX_Task {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_dt_complete_task', array( $this, 'dt_complete_task' ) );
		add_action( 'wp_ajax_dt_task_later', array( $this, 'dt_task_later' ) );
		add_action( 'wp_ajax_dt_remove_task', array( $this, 'dt_remove_task' ) );
		add_action( 'wp_ajax_dt_contact_user', array( $this, 'dt_contact_user' ) );
	}

	/**
	 * Add a complete task
	 *
	 * @since    1.0.0
	 *
	 * @return    void
	 */
	public function dt_complete_task() {
		// Based on check_ajax_referer
		if ( isset( $_GET[ '_wpnonce' ] ) ) {
			$nonce = $_GET[ '_wpnonce' ];
		}

		$result = wp_verify_nonce( $nonce, 'dt-task-action' );

		if ( false === $result ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_die( -1 );
			} else {
				die( '-1' );
			}
		}
		if ( is_user_logged_in() ) {
			dt_set_completed_task_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
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
	 * @return   void
	 */
	public function dt_task_later() {
		// Based on check_ajax_referer
		if ( isset( $_GET[ '_wpnonce' ] ) ) {
			$nonce = $_GET[ '_wpnonce' ];
		}

		$result = wp_verify_nonce( $nonce, 'dt-task-action' );

		if ( false === $result ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_die( -1 );
			} else {
				die( '-1' );
			}
		}
		if ( is_user_logged_in() ) {
			dt_set_task_later_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
			echo 'done!';
		} else {
			echo 'error!';
		}
		wp_die();
	}

	/**
	 * Remove a complete task
	 *
	 * @since    1.0.0
	 *
	 * @return    void
	 */
	public function dt_remove_task() {
		// Based on check_ajax_referer
		if ( isset( $_GET[ '_wpnonce' ] ) ) {
			$nonce = $_GET[ '_wpnonce' ];
		}

		$result = wp_verify_nonce( $nonce, 'dt-task-action' );

		if ( false === $result ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_die( -1 );
			} else {
				die( '-1' );
			}
		}
		if ( is_user_logged_in() ) {
			dt_remove_complete_task_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
			echo 'done!';
		} else {
			echo 'error!';
		}
		wp_die();
	}

	/**
	 * Sent an email to the user
	 *
	 * @since    1.0.0
	 *
	 * @return    void
	 */
	public function dt_contact_user() {
		// Based on check_ajax_referer
		if ( isset( $_POST[ '_wpnonce' ] ) ) {
			$nonce = $_POST[ '_wpnonce' ];
		}

		$result = wp_verify_nonce( $nonce, 'dt_contact_user' );

		if ( false === $result ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_die( -1 );
			} else {
				die( '-1' );
			}
		}
		if ( is_user_logged_in() && !empty( $_POST[ 'content' ] ) ) {
			//Receiver user
			$user = get_user_by( 'login', $_POST[ 'user_login' ] );
			//Sender user
			$current_user = wp_get_current_user();
			if ( $current_user->user_login !== $user->user_login ) {
				$plugin = DaTask::get_instance();
				//Body
				$message = sprintf( __( 'Contact from %s by %s', $plugin->get_plugin_slug() ), '<b>' . get_bloginfo( 'name' ) . '</b>', '<i>' . $current_user->user_login . '</i>' );
				$message .= '<br>' . __( 'Profile', $plugin->get_plugin_slug() );
				$message .= ': <a href="' . home_url( '/member/' . $current_user->user_login ) . '">' . home_url( '/member/' . $current_user->user_login ) . '</a>';
				$message .= wpautop( esc_html( $_POST[ 'content' ] ) );
				//Headers
				$headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $current_user->user_login . ' <' . $current_user->user_email . '>' );
				//Send email
				wp_mail( $user->user_email, sprintf( __( 'Contact from %s by %s', $plugin->get_plugin_slug() ), get_bloginfo( 'name' ), $current_user->user_login ), $message, $headers );
				echo 'done!';
				wp_die();
			}
			echo 'error!';
			wp_die();
		} else {
			echo 'error!';
		}
	}

}

new DT_AJAX_Task();
