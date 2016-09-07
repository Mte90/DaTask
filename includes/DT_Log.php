<?php

/**
 * Support for Log Post
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_Log {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_filter( 'wds_log_post_user_can_see', array( $this, 'enable_editors' ) );
    add_filter( 'wds_log_post_log_types', array( $this, 'datask_label' ) );
    add_action( 'admin_menu', array( $this, 'change_post_menu_label' ) );

    $log_columns = new CPT_columns( 'wdslp-wds-log' );
    $log_columns->add_column( 'Di', array(
	  'label' => __( 'Di', DT_TEXTDOMAIN ),
	  'type' => 'custom_value',
	  'callback' => array( $this, 'author_of_log' ),
	  'sortable' => true,
	  'prefix' => '<b>',
	  'suffix' => '</b>',
	  'order' => '-1',
	  'meta_key' => 'post_author'
		)
    );
  }

  public function enable_editors( $user_can_see ) {
    return current_user_can( 'administrator', 'editor' );
  }

  public function datask_label( $terms ) {
    if ( !isset( $terms[ 'DaTask' ] ) ) {
	$terms[ 'DaTask' ] = array(
	    'slug' => 'datask',
	    'description' => 'background-color: #00ee00; color:black; font-weight:bold;',
	);
    }
    return $terms;
  }

  public function change_post_menu_label() {
    global $menu;
    foreach ( $menu as $key => $value ) {
	if ( $menu[ $key ][ 0 ] === 'WDS Logs' ) {
	  $menu[ $key ][ 0 ] = 'DT Logs';
	}
    }
  }

  public static function log_message( $id, $message, $label = '' ) {
    if ( empty( $label ) ) {
	$label = 'DaTask';
    }
    $id_log = WDS_Log_Post::log_message( $message, '', $label );
    update_post_meta( $id_log, DT_TEXTDOMAIN . '_id', $id );
  }

  /**
   * Return the author of the task
   *
   * @since    1.0.0
   * @param    integer $log_id ID of the task.
   * @return   string The HTML link to user profile backend
   */
  public function author_of_log( $log_id ) {
    $author_id = get_post_field( 'post_author', $log_id );
    $current_user = get_userdata( $author_id );
    return '<a href="' . home_url( '/member/' . $current_user->user_login ) . '">' . $current_user->first_name . ' ' . $current_user->last_name . '</a>';
  }

}

new DT_Log();
