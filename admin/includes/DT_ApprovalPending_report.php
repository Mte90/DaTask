<?php

if ( !class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * This class generate the list for the statistics
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @since     2.0.0
 * @link      http://mte90.net
 * @copyright 2016 GPL
 */
class DT_ApprovalPending extends WP_List_Table {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.1.0
   */
  public function __construct() {
    parent::__construct( [
	  'singular' => __( 'Task', DT_TEXTDOMAIN ),
	  'plural' => __( 'Tasks', DT_TEXTDOMAIN ),
	  'ajax' => false
    ] );
    $this->maybe_download();
  }

  /**
   * Return a list of tasks done
   *
   * @since     1.1.0
   *
   * @param  integer $per_page Posts per page.
   * @param  integer $page_number The page number.
   * @return array The posts.
   */
  public function get_tasks( $per_page = 5, $page_number = 1 ) {
    $args = array(
	  'post_type' => 'datask-log',
	  'posts_per_page' => -1,
	  'offset' => ( $page_number - 1 ) * $per_page,
	  'tax_query' => array(
		array(
		    'taxonomy' => 'wds_log_type',
		    'field' => 'slug',
		    'terms' => array( 'pending' ),
		    'operator' => 'AND',
		),
	  )
    );
    if ( !empty( $_REQUEST[ 'orderby' ] ) ) {
	$args[ 'order' ] = !empty( $_REQUEST[ 'order' ] ) ? ' ' . esc_sql( $_REQUEST[ 'order' ] ) : ' ASC';
	$args[ 'orderby' ] = esc_sql( $_REQUEST[ 'orderby' ] );
    }
    $query = new WP_Query( $args );
    $items = array();
    foreach ( $query->posts as $post ) {
	$task = get_post_meta( $post->ID, DT_TEXTDOMAIN . '_id', true );
	$mentors = dt_get_mentors( $task );
	$is_mentor = false;
	if ( !current_user_can( 'manage_options' ) ) {
	  if ( is_array( $mentors ) ) {
	    foreach ( $mentors as $key => $user ) {
		if ( $user === ( string ) get_current_user_id() ) {
		  $is_mentor = true;
		  break;
		}
	    }
	    if ( !$is_mentor ) {
		continue;
	    }
	  } else {
	    continue;
	  }
	}
	$items[ $post->ID ][ 'task' ] = $task;
	$items[ $post->ID ][ 'title' ] = $post->post_title;
	$items[ $post->ID ][ 'ID' ] = $post->ID;
	$items[ $post->ID ][ 'user' ] = $post->post_author;
    }
    return $items;
  }

  /**
   * Returns the count of records in the database.
   *
   * @return null|string
   */
  public function record_count() {
    return count( $this->get_tasks() );
  }

  /**
   * Print the string for no posts avalaible
   *
   * @return null|string
   */
  public function no_items() {
    _e( 'No approval pending avalaible.', DT_TEXTDOMAIN );
  }

  /**
   * Render a column when no column specific method exist.
   *
   * @param array  $item Column.
   * @param string $column_name Name.
   *
   * @return mixed
   */
  public function column_default( $item, $column_name ) {
    switch ( $column_name ) {
	case 'title':
	case 'user':
	  return $item[ $column_name ];
	default:
	  return print_r( $item, true ); //Show the whole array for troubleshooting purposes
    }
  }

  /**
   * Method for title column
   *
   * @param array $item an array of DB data
   * @return string
   */
  function column_title( $item ) {
    $title = '<strong>' . $item[ 'title' ] . '</strong>';
    $actions = [
	  'show' => '<a href="' . get_permalink( $item[ 'task' ] ) . '">' . __( 'Show' ) . '</a>'
    ];
    return $title . $this->row_actions( $actions );
  }

  /**
   * Method for user column
   *
   * @param array $item an array of DB data
   * @return string
   */
  function column_user( $item ) {
    $user = get_user_by( 'id', $item[ 'user' ] );
    $title = '<strong>' . $user->user_nicename . '</strong>';
    $actions = [
	  'show' => dt_profile_link( $user->user_login, __( 'Show' ) )
    ];
    return $title . $this->row_actions( $actions );
  }

  /**
   * Method for user column
   *
   * @param array $item an array of DB data
   * @return string
   */
  function column_approve_log( $item ) {
    $title = '<button class="button button-primary dt-approve-task" data-id="' . $item[ 'ID' ] . '">' . __( 'Approve', DT_TEXTDOMAIN ) . '</button><button class="button dt-remove-task" data-id="' . $item[ 'ID' ] . '">' . __( 'Remove', DT_TEXTDOMAIN ) . '</button>';
    return $title;
  }

  /**
   *  Associative array of columns
   *
   * @return array
   */
  function get_columns() {
    $columns = [
	  'title' => __( 'Title', DT_TEXTDOMAIN ),
	  'user' => __( 'User', DT_TEXTDOMAIN ),
	  'approve_log' => __( 'Approve', DT_TEXTDOMAIN ),
    ];
    return $columns;
  }

  /**
   * Columns to make sortable.
   *
   * @return array
   */
  public function get_sortable_columns() {
    $sortable_columns = array(
	  'title' => array( 'title', true ),
	  'user' => array( 'user', false )
    );
    return $sortable_columns;
  }

  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items() {
    $this->_column_headers = $this->get_column_info();
    //Read the screen option value
    $per_page = $this->get_items_per_page( 'tasks_per_page', 5 );
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();
    $this->set_pagination_args( [
	  'total_items' => $total_items,
	  'per_page' => $per_page
    ] );
    $this->items = self::get_tasks( $per_page, $current_page );
  }

  /**
   * Export the list table as CSV using the same settings of the view
   */
  public function maybe_download() {
    if ( empty( $_POST[ 'action' ] ) || 'export-approval-done' !== $_POST[ 'action' ] ) {
	return;
    }

    if ( !current_user_can( 'manage_options' ) ) {
	wp_die( '' );
    }
    check_admin_referer( DT_TEXTDOMAIN . '-export-approval', DT_TEXTDOMAIN . '_once' );

    $sitename = sanitize_key( get_bloginfo( 'name' ) );
    if ( !empty( $sitename ) ) {
	$sitename .= '.';
    }
    $filename = $sitename . '_datask-approvalpending_' . date( 'Y-m-d' ) . '.csv';

    header( 'Content-Description: File Transfer' );
    header( 'Content-Disposition: attachment; filename=' . $filename );
    header( 'Content-Type: application/csv; charset=' . get_option( 'blog_charset' ), true );

    //Load the tasks
    $array = $this->get_tasks( 50 );
    //Convert the array in csv using the php methods
    $temp_memory = fopen( 'php://memory', 'w' );
    //First row
    fputcsv( $temp_memory, array( __( 'Title' ), __( 'Done', DT_TEXTDOMAIN ) ), ',' );
    foreach ( $array as $line ) {
	//If there is no value add a defult value
	if ( empty( $line[ 'done' ] ) || !isset( $line[ 'done' ] ) ) {
	  $line[ 'done' ] = 0;
	}
	//The array_slice remove the first column that contain the ID of the task
	fputcsv( $temp_memory, array_slice( $line, 1 ), ',' );
    }
    fseek( $temp_memory, 0 );
    fpassthru( $temp_memory );
    die();
  }

}

new DT_ApprovalPending();
