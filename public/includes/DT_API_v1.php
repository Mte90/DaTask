<?php

/**
 * DT_APIv1
 * Support for API rest
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_APIv1 {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		add_filter( 'json_prepare_post', array( $this, 'add_fields_to_api' ), 99, 3 );
	}

	/**
	 * Add fields to api for tasks
	 * 
	 * @since    1.0.0
	 */
	public function add_fields_to_api( $_post, $post, $context ) {
		if ( $_post[ 'type' ] === 'task' ) {
			$plugin = DaTask::get_instance();
			$_post[ 'content' ] = array();
			$befores = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_before' ), true );
			$befores_task = '';
			if ( !empty( $befores ) ) {
				$befores_split = explode( ',', str_replace( ' ', '', $befores ) );
				$befores_ids = new WP_Query( array(
				    'post_type' => 'task',
				    'post__in' => $befores_split ) );
				foreach ( $befores_ids->posts as $post ) {
					$befores_task .= '<a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a>, ';
				}
				wp_reset_postdata();
			}
			$_post[ 'content' ][ 'before' ] = $befores_task;
			$_post[ 'content' ][ 'prerequisites' ] = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_prerequisites' ), true );
			$_post[ 'content' ][ 'matters' ] = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_matters' ), true );
			$_post[ 'content' ][ 'steps' ] = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_steps' ), true );
			$_post[ 'content' ][ 'help' ] = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_help' ), true );
			$_post[ 'content' ][ 'completion' ] = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_completion' ), true );
			$mentors = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_mentor' ), true );
			$mentors_task = '';
			if ( !empty( $mentors ) ) {
				$mentors_split = explode( ',', str_replace( ' ', '', $mentors ) );
				foreach ( $mentors_split as $user ) {
					$user = get_user_by( 'id', $user );
					$name = trim( $user->display_name ) ? $user->display_name : $user->user_login;
					$mentors_task .= '<a href="' . home_url( '/member/' . $user->user_login ) . '">' . $name . '</a>, ';
				}
			}
			$_post[ 'content' ][ 'mentors' ] = $mentors_task;
			$nexts = get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'task_next' ), true );
			$next_task = '';
			if ( !empty( $nexts ) ) {
				$nexts = '';
				$nexts_split = explode( ',', str_replace( ' ', '', $nexts ) );
				$nexts_ids = new WP_Query( array(
				    'post_type' => 'task',
				    'post__in' => $nexts_split ) );
				foreach ( $nexts_ids->posts as $post ) {
					$next_task .= '<a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a>, ';
				}
				wp_reset_postdata();
			}
			$_post[ 'content' ][ 'next' ] = $next_task;
			$users = unserialize( get_post_meta( $_post[ 'ID' ], $plugin->get_fields( 'users_of_task' ), true ) );
			$next_user = '';
			if ( !empty( $users ) ) {
				foreach ( $users as $user => $value ) {
					$next_user .= '<a href="' . get_home_url() . '/member/' . get_the_author_meta( 'user_login', $user ) . '">' . get_the_author_meta( 'display_name', $user ) . '</a>, ';
				}
			}
			$_post[ 'content' ][ 'users' ] = $next_user;
		}
		return $_post;
	}

}

new DT_APIv1();
