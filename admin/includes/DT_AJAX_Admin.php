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
    add_action( 'wp_ajax_dt_approval', array( $this, 'dt_approval' ) );
    add_action( 'wp_ajax_dt_remove_approval', array( $this, 'dt_remove_approval' ) );
  }

  public function dt_approval() {
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
	wp_send_json_success();
    }
    wp_send_json_error();
  }
  
  public function dt_remove_approval() {
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
	wp_add_object_terms( esc_html( $_GET[ 'ID' ] ), 'remo', 'wds_log_type' );
	wp_send_json_success();
    }
    wp_send_json_error();
  }

}

new DT_AJAX_Admin();
