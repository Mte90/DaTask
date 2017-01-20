<?php

/**
 * DT_API
 * Support for API rest
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @copyright 2015 GPL
 * @license   GPL-2.0+
 * @link      http://mte90.net
 */
class DT_API {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		if ( defined( 'REST_API_VERSION' ) ) {
			add_action( 'rest_api_init', array( $this, 'fields_to_apiv2' ) );
		}
	}

	/**
	 * Add fields to api v2 for tasks
	 *
	 * @since 1.0.0
	 */
	public function fields_to_apiv2() {
		$args = array(
			'get_callback' => array( $this, 'get_meta_data' ),
		);
		if ( !function_exists( 'register_rest_field' ) ) {
			return false;
		}
		register_rest_field( 'task', 'task_before', $args );
		register_rest_field( 'task', 'task_prerequisites', $args );
		register_rest_field( 'task', 'task_matters', $args );
		register_rest_field( 'task', 'task_steps', $args );
		register_rest_field( 'task', 'task_help', $args );
		register_rest_field( 'task', 'task_completion', $args );
		register_rest_field( 'task', 'task_mentor', $args );
		register_rest_field( 'task', 'task_next', $args );
	}

	/**
	 * Get the value of the "starship" field
	 *
	 * @param array           $object Details of current post.
	 * @param string          $field Name of field.
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return mixed
	 */
	public function get_meta_data( $object, $field, $request ) {
		$plugin = DaTask::get_instance();
		if ( 'task_before' === $field ) {
			$befores_task = '';
			$befores = get_post_meta( $object[ 'id' ], $plugin->get_fields( 'task_before' ), true );
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
			return array( 'ids' => $befores, 'rendered' => $befores_task );
		} elseif ( 'task_mentor' === $field ) {
			$mentors = get_post_meta( $object[ 'id' ], $plugin->get_fields( 'task_mentor' ), true );
			$mentors_task = '';
			if ( !empty( $mentors ) ) {
				$mentors_split = explode( ',', str_replace( ' ', '', $mentors ) );
				foreach ( $mentors_split as $user ) {
					$user = get_user_by( 'id', $user );
					$mentors_task .= dt_profile_link( $user->user_login, trim( $user->display_name ) ? $user->display_name : $user->user_login ) . ', ';
				}
			}
			return array( 'ids' => $mentors, 'rendered' => $mentors_task );
		} elseif ( 'task_next' === $field ) {
			$nexts = get_post_meta( $object[ 'id' ], $plugin->get_fields( 'task_next' ), true );
			$next_task = '';
			if ( !empty( $nexts ) ) {
				$nexts_split = explode( ',', str_replace( ' ', '', $nexts ) );
				$nexts_ids = new WP_Query( array(
					'post_type' => 'task',
					'post__in' => $nexts_split ) );
				foreach ( $nexts_ids->posts as $post ) {
					$next_task .= '<a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a>, ';
				}
				wp_reset_postdata();
			}
			return array( 'ids' => $nexts, 'rendered' => $next_task );
		}
		return get_post_meta( $object[ 'id' ], $plugin->get_fields( $field ), true );
	}

}

new DT_API();
