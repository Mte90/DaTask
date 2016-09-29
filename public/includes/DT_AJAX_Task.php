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
   * @since 1.0.0
   * @return mixed
   */
  public function dt_complete_task() {
    // Based on check_ajax_referer
    if ( isset( $_GET[ '_wpnonce' ] ) ) {
	$nonce = wp_unslash( $_GET[ '_wpnonce' ] );
    }

    $result = wp_verify_nonce( $nonce, 'dt-task-action' );

    if ( false === $result ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	  wp_die( -1 );
	}
    }

    if ( get_post_status( ( int ) $_GET[ 'ID' ] ) === 'archived' ) {
	wp_send_json_error();
    }

    if ( is_user_logged_in() ) {
	dt_set_completed_task_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
	$approval = datask_require_approval( ( int ) $_GET[ 'ID' ] );
	if ( !empty( $approval ) && $approval !== 'none' ) {
	  DT_Log::log_message( ( int ) $_GET[ 'ID' ], get_the_title( ( int ) $_GET[ 'ID' ] ), array( 'DaTask', 'Pending' ) );
	} else {
	  DT_Log::log_message( ( int ) $_GET[ 'ID' ], get_the_title( ( int ) $_GET[ 'ID' ] ) );
	}
	wp_send_json_success();
    } else {
	DT_Log::log_message( ( int ) $_GET[ 'ID' ], get_the_title( ( int ) $_GET[ 'ID' ] ), array( 'DaTask', 'Error' ) );
	wp_send_json_error();
    }
  }

  /**
   * Add a task later
   *
   * @since 1.0.0
   * @return mixed
   */
  public function dt_task_later() {
    // Based on check_ajax_referer
    if ( isset( $_GET[ '_wpnonce' ] ) ) {
	$nonce = wp_unslash( $_GET[ '_wpnonce' ] );
    }

    $result = wp_verify_nonce( $nonce, 'dt-task-action' );

    if ( false === $result ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	  wp_die( -1 );
	}
    }

    if ( get_post_status( ( int ) $_GET[ 'ID' ] ) === 'archived' ) {
	wp_send_json_error();
    }

    if ( is_user_logged_in() ) {
	dt_set_task_later_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
	wp_send_json_success();
    } else {
	wp_send_json_error();
    }
  }

  /**
   * Remove a complete task
   *
   * @since 1.0.0
   * @return mixed
   */
  public function dt_remove_task() {
    // Based on check_ajax_referer
    if ( isset( $_GET[ '_wpnonce' ] ) ) {
	$nonce = wp_unslash( $_GET[ '_wpnonce' ] );
    }

    $result = wp_verify_nonce( $nonce, 'dt-task-action' );

    if ( false === $result ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	  wp_die( -1 );
	}
    }

    if ( get_post_status( ( int ) $_GET[ 'ID' ] ) === 'archived' ) {
	wp_send_json_error();
    }

    if ( is_user_logged_in() ) {
	dt_remove_complete_task_for_user_id( get_current_user_id(), ( int ) $_GET[ 'ID' ] );
	wp_send_json_success();
    } else {
	wp_send_json_error();
    }
  }

  /**
   * Sent an email to the user
   *
   * @since 1.0.0
   * @return mixed
   */
  public function dt_contact_user() {
    // Based on check_ajax_referer
    if ( isset( $_POST[ '_wpnonce' ] ) ) {
	$nonce = wp_unslash( $_POST[ '_wpnonce' ] );
    }

    $result = wp_verify_nonce( $nonce, 'dt_contact_user' );

    if ( false === $result ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	  wp_die( -1 );
	}
    }
    if ( is_user_logged_in() && !empty( esc_html( wp_unslash( $_POST[ 'content' ] ) ) ) ) {
	// Receiver user
	$user = get_user_by( 'login', esc_html( $_POST[ 'user_login' ] ) );
	// Sender user
	$current_user = wp_get_current_user();
	if ( $current_user->user_login !== $user->user_login ) {
	  // Body
	  $message = sprintf( __( 'Contact from %1$s by %2$s', DT_TEXTDOMAIN ), '<b>' . get_bloginfo( 'name' ) . '</b>', '<i>' . $current_user->user_login . '</i>' );
	  $message .= '<br>' . __( 'Profile', DT_TEXTDOMAIN );
	  $message .= ': <a href="' . home_url( '/member/' . $current_user->user_login ) . '">' . home_url( '/member/' . $current_user->user_login ) . '</a>';
	  $message .= wpautop( esc_html( $_POST[ 'content' ] ) );
	  // Headers
	  $headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $current_user->user_login . ' <' . $current_user->user_email . '>' );
	  // Send email
	  wp_mail( $user->user_email, sprintf( __( 'Contact from %1$s by %2$s', DT_TEXTDOMAIN ), get_bloginfo( 'name' ), $current_user->user_login ), $message, $headers );
	  wp_send_json_success();
	}
    }
    wp_send_json_error();
  }

}

new DT_AJAX_Task();
