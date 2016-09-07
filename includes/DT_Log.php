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

}

new DT_Log();
