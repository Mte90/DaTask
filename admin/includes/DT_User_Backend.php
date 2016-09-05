<?php

/**
 * This class contain the reset link for counter on user backend
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @since     1.0.0
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_User_Backend {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		add_filter( 'user_row_actions', array( $this, 'filter_user_row_actions' ), 10, 2 );
		add_filter( 'ms_user_row_actions', array( $this, 'filter_user_row_actions' ), 10, 2 );
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'action_init' ) );
		}
	}

	/**
	 * Adds a 'Switch To' link to each list of user actions on the Users screen.
	 * 
	 * @since     1.0.0
	 *
	 * @param  array   $actions The actions to display for this user row.
	 * @param  WP_User $user    The user object displayed in this row.
	 * @return array The actions to display for this user row.
	 */
	public function filter_user_row_actions( array $actions, WP_User $user ) {
		$plugin = DaTask::get_instance();
		if ( is_admin() ) {
			$link = wp_nonce_url( add_query_arg( array(
			    'action' => 'reset_task_later_user',
			    'user_id' => $user->ID,
					), 'users.php' ), 'reset_task_later_user_' . $user->ID );
			$actions[ 'reset_task_later_user' ] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Reset Task in Progress', DT_TEXTDOMAIN ) . '</a>';
		}
		$actions[ 'member_profile' ] = '<a href="' . home_url( '/member/' . $user->user_login ) . '">' . esc_html__( 'View Public Profile', DT_TEXTDOMAIN ) . '</a>';

		return $actions;
	}

	/**
	 * Load localisation files and route actions depending on the 'action' query var.
	 * 
	 * @since     1.0.0
	 */
	public function action_init() {
		if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'reset_task_later_user' ) {
			$user_id = absint( $_REQUEST[ 'user_id' ] );
			check_admin_referer( 'reset_task_later_user_' . $user_id );
			$plugin = DaTask::get_instance();
			update_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), serialize( '' ) );
			$user = get_user_by( 'id', $user_id );
			New WP_Admin_Notice( sprintf( __( 'Task in progress reset for <b>%s</b> done!', DT_TEXTDOMAIN ), $user->data->user_login ), 'updated' );
		}
	}

}

new DT_User_Backend();
