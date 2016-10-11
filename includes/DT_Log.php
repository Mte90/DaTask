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

  protected $log = '';

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_filter( 'wds_log_post_types', array( $this, 'add_post_type' ) );
    add_filter( 'wds_log_datask_log_user_can_see', array( $this, 'enable_editors' ) );
    add_filter( 'wds_log_post_log_types', array( $this, 'datask_label' ) );
    //add_action( 'admin_menu', array( $this, 'change_post_menu_label' ) );
    add_action( 'the_posts', array( $this, 'add_id_task' ) );

    $log_columns = new CPT_columns( 'datask-log' );
    $log_columns->add_column( 'By', array(
	  'label' => __( 'By', DT_TEXTDOMAIN ),
	  'type' => 'custom_value',
	  'callback' => array( $this, 'author_of_log' ),
	  'sortable' => true,
	  'prefix' => '<b>',
	  'suffix' => '</b>',
	  'order' => '-1',
	  'meta_key' => 'post_author'
		)
    );
    $log_columns->add_column( 'Approver', array(
	  'label' => __( 'Approver', DT_TEXTDOMAIN ),
	  'type' => 'custom_value',
	  'callback' => array( $this, 'approver' ),
	  'sortable' => true,
	  'prefix' => '<b>',
	  'suffix' => '</b>',
	  'order' => '-1',
	  'meta_key' => DT_TEXTDOMAIN . '_approver'
		)
    );
    
    $log_columns->add_column( 'Delete', array(
	  'label' => __( 'Delete', DT_TEXTDOMAIN ),
	  'type' => 'custom_value',
	  'callback' => array( $this, 'delete' ),
	  'order' => '-1',
		)
    );
  }

  public function enable_editors( $user_can_see ) {
    return current_user_can( 'administrator', 'editor' );
  }

  public function datask_label( $terms ) {
    if ( !isset( $terms[ 'DaTask' ] ) ) {
	$terms[ 'Pending' ] = array(
	    'slug' => 'pending',
	    'description' => 'background-color: #ff0000; color:black; font-weight:bold;',
	);
	$terms[ 'Remove' ] = array(
	    'slug' => 'remove',
	    'description' => 'background-color: #0000ff; color:white; font-weight:bold;',
	);
    }
    return $terms;
  }

  public function add_post_type( $posttypes ) {
    $posttypes[ 'datask-log' ] = 'DT';
    return $posttypes;
  }

  public static function log_message( $id, $message, $label = array() ) {
    $id_log = WDS_Log_Post::log_message( 'datask-log', $message, '', $label );
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
    $user = get_userdata( $author_id );
    return dt_profile_link($user->user_login, trim( $user->display_name ) ? $user->display_name : $user->user_login);
  }

  /**
   * Return the approver of the task
   *
   * @since    1.0.0
   * @param    integer $log_id ID of the task.
   * @return   string The HTML link to user profile backend
   */
  public function approver( $log_id ) {
    $author_id = get_post_meta( $log_id, DT_TEXTDOMAIN . '_approver', true );
    if ( !empty( $author_id ) ) {
	$user = get_userdata( $author_id );
	return dt_profile_link($user->user_login, trim( $user->display_name ) ? $user->display_name : $user->user_login);
    }
    return '';
  }

  /**
   * Return the approver of the task
   *
   * @since    1.0.0
   * @param    integer $log_id ID of the task.
   * @return   string The HTML link to user profile backend
   */
  public function delete( $log_id ) {
    return '<button class="button dt-mark-remove-task" data-id="' . $log_id . '">' . __( 'Mark as Removed', DT_TEXTDOMAIN ) . '</button><button class="button dt-remove-log-task" data-id="' . $log_id . '">' . __( 'Remove', DT_TEXTDOMAIN ) . '</button>';
  }

  public function add_id_task( $post_object ) {
    foreach ( $post_object as $post ) {
	if ( $post->post_type === 'datask-log' ) {
	  $post->task_ID = ( int ) get_post_meta( $post->ID, DT_TEXTDOMAIN . '_id', true );
	}
    }
    return $post_object;
  }

}

new DT_Log();
