<?php

/**
 * This class generate the list for the statistics
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @since     1.1.0
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class DT_MostDone extends WP_List_Table {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.1.0
	 */
	public function __construct() {
		$plugin = DaTask::get_instance();
		parent::__construct( [
		    'singular' => __( 'Task', $plugin->get_plugin_slug() ),
		    'plural' => __( 'Tasks', $plugin->get_plugin_slug() ),
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
		$plugin = DaTask::get_instance();
		global $wpdb;
		$sql = "SELECT SQL_CALC_FOUND_ROWS " . $wpdb->posts . ".ID," . $wpdb->posts . ".post_title as title, done_task.meta_value as done";
		$sql .= " FROM wp_posts LEFT JOIN $wpdb->postmeta as done_task ON (" . $wpdb->posts . ".ID = done_task.post_id AND done_task.meta_key='_task_" . $plugin->get_plugin_slug() . "_counter') WHERE 1=1";
		$sql .= " AND " . $wpdb->posts . ".post_type = 'task' AND (" . $wpdb->posts . ".post_status = 'publish' OR " . $wpdb->posts . ".post_status = 'private')";
		if ( !empty( $_REQUEST[ 'orderby' ] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST[ 'orderby' ] );
			$sql .=!empty( $_REQUEST[ 'order' ] ) ? ' ' . esc_sql( $_REQUEST[ 'order' ] ) : ' ASC';
		}
		$sql .= ' LIMIT ' . $per_page . ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$results = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $results;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM $wpdb->posts WHERE 1=1 AND $wpdb->posts.post_type = 'task' AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private')";
		return $wpdb->get_var( $sql );
	}

	/**
	 * Print the string for no posts avalaible
	 *
	 * @return null|string
	 */
	public function no_items() {
		$plugin = DaTask::get_instance();
		_e( 'No tasks avalaible.', $plugin->get_plugin_slug() );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'done':
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
		    'edit' => '<a href="post.php?post=' . $item[ 'ID' ] . '&action=edit">' . __( 'Edit' ) . '</a>',
		    'show' => '<a href="' . get_permalink( $item[ 'ID' ] ) . '">' . __( 'Show' ) . '</a>'
		];
		return $title . $this->row_actions( $actions );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$plugin = DaTask::get_instance();
		$columns = [
		    'title' => __( 'Title', $plugin->get_plugin_slug() ),
		    'done' => __( 'Done', $plugin->get_plugin_slug() ),
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
		    'done' => array( 'done', false )
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
		if ( empty( $_POST[ 'action' ] ) || 'export-report-done' !== $_POST[ 'action' ] ) {
			return;
		}

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( '' );
		}
		$plugin = DaTask::get_instance();
		check_admin_referer( $plugin->get_plugin_slug() . '-export-report', $plugin->get_plugin_slug() . '_once' );

		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( !empty( $sitename ) ) {
			$sitename .= '.';
		}
		$filename = $sitename . '_datask-report_' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: application/csv; charset=' . get_option( 'blog_charset' ), true );

		//Load the tasks
		$array = $this->get_tasks( 50 );
		//Convert the array in csv using the php methods
		$temp_memory = fopen( 'php://memory', 'w' );
		//First row
		fputcsv( $temp_memory, array( __( 'Title' ), __( 'Done', $plugin->get_plugin_slug() ) ), ',' );
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

new DT_MostDone();
