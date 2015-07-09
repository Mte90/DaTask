<?php

/**
 * WP-OneAndDone.
 *
 * @package   Wp_Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */

/**
 * This class contain the link on user backend
 *
 * @package Wp_Oneanddone
 * @author  Mte90 <mte90net@gmail.com>
 */
class WO_User_Backend {

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
	 * @param  array   $actions The actions to display for this user row.
	 * @param  WP_User $user    The user object displayed in this row.
	 * @return array The actions to display for this user row.
	 */
	public function filter_user_row_actions( array $actions, WP_User $user ) {
		$plugin = Wp_Oneanddone::get_instance();
		if ( is_admin() ) {
			$link = wp_nonce_url( add_query_arg( array(
			    'action' => 'reset_task_later_user',
			    'user_id' => $user->ID,
					), 'users.php' ), 'reset_task_later_user_' . $user->ID );
			$actions[ 'reset_task_later_user' ] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Reset Task in Progress', $plugin->get_plugin_slug() ) . '</a>';
		}
		$actions[ 'member_profile' ] = '<a href="' . home_url( '/member/' . $user->user_login ) . '">' . esc_html__( 'View Public Profile', $plugin->get_plugin_slug() ) . '</a>';

		return $actions;
	}

	/**
	 * Load localisation files and route actions depending on the 'action' query var.
	 */
	public function action_init() {
		if ( !isset( $_REQUEST[ 'action' ] ) ) {
			return;
		}

		if ( $_GET[ 'action' ] === 'reset_task_later_user' ) {
			$user_id = absint( $_REQUEST[ 'user_id' ] );
			check_admin_referer( 'reset_task_later_user_' . $user_id );
			$plugin = Wp_Oneanddone::get_instance();
			update_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), serialize( '' ) );
			$user = get_user_by( 'id', $user_id );
			New WP_Admin_Notice( sprintf( __( 'Task in progress reset for <b>%s</b> done!', $plugin->get_plugin_slug() ), $user->data->user_login ), 'updated' );
		}
	}

}

new WO_User_Backend();
