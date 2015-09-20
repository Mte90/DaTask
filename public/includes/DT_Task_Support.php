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
		// Override the template hierarchy
		add_filter( 'template_include', array( $this, 'load_content_task' ) );

		/*
		 * Custom Action/Shortcode
		 */

		add_action( 'dt-task-info', array( $this, 'dt_task_info' ) );
		add_filter( 'the_content', array( $this, 'dt_task_content' ) );
		add_filter( 'the_excerpt', array( $this, 'dt_task_excerpt' ) );
		add_shortcode( 'datask-progress', array( $this, 'datask_progress' ) );
	}

	/**
	 * Add class in the body on the frontend
	 *
	 * @since    1.0.0
	 * 
	 * @param array $classes Classes of the body.
	 * 
	 * @return array $classes Classes of the body
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
	 * 
	 * @param string $original_template The path of the template file.
	 * 
	 * @return string $original_template The path of the template file.
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
		echo '<div class="alert alert-warning"><b>' . __( 'Last edit: ', $plugin->get_plugin_slug() ) . '</b> ' . ucfirst( get_the_modified_date() ) . '</div>';
		echo '<ul class="list list-inset">';
		$team = get_the_terms( get_the_ID(), 'task-team' );
		if ( !empty( $team ) ) {
			echo '<li><b>';
			_e( 'Team', $plugin->get_plugin_slug() );
			echo ': </b>';
			foreach ( $team as $term ) {
				echo '<a href="' . get_term_link( $term->slug, 'task-team' ) . '">' . $term->name . '</a>, ';
			}
			echo '</li>';
		}
		$project = get_the_terms( get_the_ID(), 'task-area' );
		if ( !empty( $project ) ) {
			echo '<li><b>';
			_e( 'Project', $plugin->get_plugin_slug() );
			echo ': </b>';
			foreach ( $project as $term ) {
				echo '<a href="' . get_term_link( $term->slug, 'task-area' ) . '">' . $term->name . '</a>, ';
			}
			echo '</li>';
		}
		$difficulty = get_the_terms( get_the_ID(), 'task-difficulty' );
		if ( !empty( $difficulty ) ) {
			echo '<li><b>';
			_e( 'Difficulty', $plugin->get_plugin_slug() );
			echo ': </b>';
			foreach ( $difficulty as $term ) {
				echo '<a href="' . get_term_link( $term->slug, 'task-difficulty' ) . '">' . $term->name . '</a>, ';
			}
			echo '</li>';
		}
		$minute = get_the_terms( get_the_ID(), 'task-minute' );
		if ( !empty( $minute ) ) {
			echo '<li><b>';
			_e( 'Estimated time', $plugin->get_plugin_slug() );
			echo ': </b>';
			foreach ( $minute as $term ) {
				echo '<a href="' . get_term_link( $term->slug, 'task-minute' ) . '">' . $term->name . '</a>, ';
			}
			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Echo the content of the task
	 *
	 * @since    1.0.0
	 * 
	 * @param string $content HTML code of the task data.
	 * 
	 * @param string $content HTML code.
	 */
	public function dt_task_content( $content ) {
		global $post, $wp_embed;
		$plugin = DaTask::get_instance();
		if ( get_post_type( $post->ID ) === 'task' ) {
			$content = wpautop( the_task_subtitle( false ) );
		}
		if ( is_singular( 'task' ) ) {
			$befores = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_before' ), true );
			if ( !empty( $befores ) ) {
				$content .= '<div class="panel panel-danger">';
				$content .= '<div class="panel-heading">';
				$content .= __( 'Required or Suggested tasks: ', $plugin->get_plugin_slug() );
				$content .= '</div>';
				$content .= '<div class="panel-content">';
				$befores_task = '';
				$befores_split = explode( ',', str_replace( ' ', '', $befores ) );
				$befores_ids = new WP_Query( array(
				    'post_type' => 'task',
				    'post__in' => $befores_split ) );
				foreach ( $befores_ids->posts as $post ) {
					$befores_task .= '<a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a>, ';
				}
				wp_reset_postdata();
				$content .= $befores_task;
				$content .= '</div>';
				$content .= '</div>';
			}
			$prerequisites = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_prerequisites' ), true );
			if ( !empty( $prerequisites ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Prerequisites', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $prerequisites ) ) ) );
			}
			$matters = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_matters' ), true );
			if ( !empty( $matters ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Why this matters', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $matters ) ) ) );
			}
			$steps = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_steps' ), true );
			if ( !empty( $steps ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Steps', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $steps ) ) ) );
			}
			$help = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_help' ), true );
			if ( !empty( $help ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Need Help?', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $help ) ) ) );
				$content .= '<br><br>';
			}
			$completion = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_completion' ), true );
			if ( !empty( $completion ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Completion', $plugin->get_plugin_slug() ) . '</h2>';
				$content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $completion ) ) ) );
				$content .= '<br><br>';
			}
			$mentors = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_mentor' ), true );
			if ( !empty( $mentors ) ) {
				$content .= '<div class="panel panel-warning">';
				$content .= '<div class="panel-heading">';
				$content .= __( 'Mentor(s)', $plugin->get_plugin_slug() );
				$content .= ': </div>';
				$content .= '<div class="panel-content">';
				$mentors_split = explode( ',', str_replace( ' ', '', $mentors ) );
				foreach ( $mentors_split as $user ) {
					$user = get_user_by( 'id', $user );
					$name = trim( $user->display_name ) ? $user->display_name : $user->user_login;
					$content .= '<a href="' . home_url( '/member/' . $user->user_login ) . '">' . $name . '</a>, ';
				}
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
			if ( !empty( $users ) ) {
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
	 * 
	 * @param string $content The excerpt.
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
	public function datask_progress() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return dt_get_tasks_later( $current_user->user_login );
		}
	}

}

new DT_Task_Support();
