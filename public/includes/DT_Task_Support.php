<?php

/**
 * DT_Task_Support
 * Task integration for template ecc
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_Task_Support {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {	
		add_filter( 'body_class', array( $this, 'add_dt_class' ), 10, 3 );
		//Override the template hierarchy
		add_filter( 'template_include', array( $this, 'load_content_task' ) );
		/*
		 * Custom Action/Shortcode
		 */
		add_action( 'dt-task-info', array( $this, 'dt_task_info' ) );
		add_filter( 'the_content', array( $this, 'dt_task_content' ) );
		add_filter( 'the_excerpt', array( $this, 'dt_task_excerpt' ) );
		add_shortcode( 'datask-progress', array( $this, 'oneanddone_progress' ) );
	
	}
	
	/**
	 * Add class in the body on the frontend
	 *
	 * @since    1.0.0
	 */
	public function add_dt_class( $classes ) {
		$plugin = DaTask::get_instance();
		global $post;
		if ( is_singular( 'task' ) ) {
			$classes[] = $plugin->get_plugin_slug() . '-task';
		} elseif ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'datask-search' ) ) {
			$classes[] = $plugin->get_plugin_slug() . '-search';
		}
		return $classes;
	}
	
	/**
	 * Example for override the template system on the frontend
	 *
	 * @since    1.0.0
	 */
	public function load_content_task( $original_template ) {
		if ( is_singular( 'task' ) ) {
			return dt_get_template_part( 'single', 'task', false );
		} else {
			return $original_template;
		}
	}
	
	/**
	 * Echo the data about the task
	 *
	 * @since    1.0.0
	 */
	public function dt_task_info() {
		$plugin = DaTask::get_instance();
		echo '<div class="alert alert-warning">' . __( 'Last edit: ', $plugin->get_plugin_slug() ) . get_the_modified_date() . '</div>';
		echo '<ul class="list list-inset">';
		echo '<li><b>';
		_e( 'Team: ', $plugin->get_plugin_slug() );
		echo '</b>';
		$team = get_the_terms( get_the_ID(), 'task-team' );
		foreach ( $team as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-team' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li><li><b>';
		_e( 'Project: ', $plugin->get_plugin_slug() );
		echo '</b>';
		$project = get_the_terms( get_the_ID(), 'task-area' );
		foreach ( $project as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-area' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li><li><b>';
		_e( 'Estimated time: ', $plugin->get_plugin_slug() );
		echo '</b>';
		$minute = get_the_terms( get_the_ID(), 'task-minute' );
		foreach ( $minute as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-minute' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li>';
		echo '</ul>';
	}

	/**
	 * Echo the content of the task
	 *
	 * @since    1.0.0
	 */
	public function dt_task_content( $content ) {
		global $post;
		$plugin = DaTask::get_instance();
		if ( get_post_type( $post->ID ) === 'task' ) {
			$content = the_task_subtitle( false );
		}
		if ( is_singular( 'task' ) ) {
			$prerequisites = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_prerequisites' ), true );
			if ( !empty( $prerequisites ) ) {
				$content = '<h2 class="alert alert-success">' . __( 'Prerequisites', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= $prerequisites;
			}
			$matters = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_matters' ), true );
			if ( !empty( $matters ) ) {
				$content = '<h2 class="alert alert-success">' . __( 'Why this matters', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= $matters;
			}
			$steps = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_steps' ), true );
			if ( !empty( $steps ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Steps', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= $steps;
			}
			$help = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_help' ), true );
			if ( !empty( $help ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Need Help?', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= $help;
				$content .= '<br><br>';
			}
			$completion = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_completion' ), true );
			if ( !empty( $completion ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Completion', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= $completion;
				$content .= '<br><br>';
			}
			$mentor = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_mentor' ), true );
			if ( !empty( $mentor ) ) {
				$content .= '<div class="panel panel-warning">';
				$content .= '<div class="panel-heading">';
				$content .= __( 'Mentor(s): ', $plugin->get_plugin_slug() );
				$content .= '</div>';
				$content .= '<div class="panel-content">';
				$content .= $mentor;
				$content .= '</div>';
				$content .= '</div>';
			}
			$nexts = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_next' ), true );
			if ( !empty( $nexts ) ) {
				$content .= '<div class="panel panel-danger">';
				$content .= '<div class="panel-heading">';
				$content .= __( 'Good next tasks: ', $plugin->get_plugin_slug() );
				$content .= '</div>';
				$content .= '<div class="panel-content">';
				$next_task = '';
				$nexts_split = explode( ',', str_replace( ' ', '', $nexts ) );
				$nexts_ids = new WP_Query( array(
				    'post_type' => 'task',
				    'post__in' => $nexts_split ) );
				foreach ( $nexts_ids->posts as $post ) {
					$next_task .= '<a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a>, ';
				}
				wp_reset_postdata();
				$content .= $next_task;
				$content .= '</div>';
				$content .= '</div>';
			}
			$users = unserialize( get_post_meta( get_the_ID(), $plugin->get_fields( 'users_of_task' ), true ) );
			if ( is_array( $users ) ) {
				$content .= '<h2>' . __( 'List of users who completed this task', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= '<div class="panel panel-default">';
				$content .= '<div class="panel-content">';
				foreach ( $users as $user => $value ) {
					$content .= '<a href="' . get_home_url() . '/member/' . get_the_author_meta( 'user_login', $user ) . '">' . get_the_author_meta( 'display_name', $user ) . '</a>, ';
				}
				$content .= '</div>';
				$content .= '</div>';
			}
			$content .= '<br><br>';
		}
		return $content;
	}

	/**
	 * Echo the excerpt of the task
	 *
	 * @since    1.0.0
	 */
	public function dt_task_excerpt( $content ) {
		global $post;
		if ( get_post_type( $post->ID ) === 'task' ) {
			$content = the_task_subtitle( false );
		}
		return $content;
	}

	/**
	 * 
	 * The shortcode show the task in progress
	 * 
	 * @since    1.0.0
	 */
	public function oneanddone_progress() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			dt_tasks_later( $current_user->user_login );
		}
	}
}

new DT_Task_Support();