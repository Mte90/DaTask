<?php

/**
 * All the Ajax Admin related code.
 *
 * @package   DaTask
 * @author    Daniele Scasciafratte <mte90net@gmail.com>
 * @license   GPL 2.0+
 * @link      http://mte90.net
 * @copyright 2015-2016
 */
class DT_AJAX_Admin {

  /**
   * Initialize Ajax.
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_action( 'wp_ajax_dt_approval', array( $this, 'approval' ) );
    add_action( 'wp_ajax_dt_remove_approval', array( $this, 'remove_approval' ) );
    add_action( 'wp_ajax_dt_mark_remove', array( $this, 'mark_remove' ) );
    add_action( 'wp_ajax_dt_remove_log', array( $this, 'remove_log' ) );
  }

  public function approval() {
    if ( isset( $_GET[ '_wpnonce' ] ) ) {
	$nonce = wp_unslash( $_GET[ '_wpnonce' ] );
    }
    $result = wp_verify_nonce( $nonce, 'dt-task-admin-action' );

    if ( false === $result ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	  wp_die( -1 );
	}
    }

    if ( is_user_logged_in() ) {
	wp_remove_object_terms( esc_html( $_GET[ 'ID' ] ), 'pending', 'wds_log_type' );
	update_post_meta( esc_html( $_GET[ 'ID' ] ), DT_TEXTDOMAIN . '_approver', get_current_user_id() );
	wp_send_json_success();
    }
    wp_send_json_error();
  }

  public function remove_approval() {
    if ( isset( $_GET[ '_wpnonce' ] ) ) {
	$nonce = wp_unslash( $_GET[ '_wpnonce' ] );
    }
    $result = wp_verify_nonce( $nonce, 'dt-task-admin-action' );

    if ( false === $result ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	  wp_die( -1 );
	}
    }

    if ( is_user_logged_in() ) {
	wp_remove_object_terms( esc_html( $_GET[ 'ID' ] ), 'pending', 'wds_log_type' );
	wp_add_object_terms( esc_html( $_GET[ 'ID' ] ), 'remove', 'wds_log_type' );
	update_post_meta( esc_html( $_GET[ 'ID' ] ), DT_TEXTDOMAIN . '_approver', get_current_user_id() );
	wp_send_json_success();
    }
    wp_send_json_error();
  }

  public function mark_remove() {
    if ( defined( 'DOING_AJAX' ) && !DOING_AJAX || !current_user_can( 'manage_options' ) ) {
	wp_die( -1 );
    }
    wp_remove_object_terms( esc_html( $_GET[ 'ID' ] ), 'pending', 'wds_log_type' );
    wp_add_object_terms( esc_html( $_GET[ 'ID' ] ), 'remove', 'wds_log_type' );
    update_post_meta( esc_html( $_GET[ 'ID' ] ), DT_TEXTDOMAIN . '_approver', get_current_user_id() );
    wp_send_json_success();
  }

  public function remove_log() {
    if ( defined( 'DOING_AJAX' ) && !DOING_AJAX || !current_user_can( 'manage_options' ) ) {
	wp_die( -1 );
    }
    wp_delete_post( esc_html( $_GET[ 'ID' ] ) );
    wp_send_json_success();
  }

}

new DT_AJAX_Admin();
