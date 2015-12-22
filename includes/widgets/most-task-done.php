<?php

/**
 * Create custom widget class extending WPH_Widget
 * 
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @copyright 2015
 */

class Most_Task_Done_Widget extends WPH_Widget {

	function __construct() {
		$plugin = DaTask::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		$args = array(
		    'label' => __( 'Most Tasks Done', $this->plugin_slug ),
		    'description' => __( 'Most Tasks Done', $this->plugin_slug ),
		);

		$args[ 'fields' ] = array(
		    array(
			'name' => __( 'Most Tasks Done', $this->plugin_slug ),
			'id' => 'title',
			'type' => 'text',
			'class' => 'widefat',
			'std' => __( 'Most Tasks Done', $this->plugin_slug ),
			'validate' => 'alpha_dash',
			'filter' => 'strip_tags|esc_attr'
		    ),
		    array(
			'name' => __( 'Task showed', $this->plugin_slug ),
			'id' => 'number',
			'type' => 'text',
			'class' => 'widefat',
			'std' => __( 5, $this->plugin_slug ),
			'validate' => 'numeric',
			'filter' => 'strip_tags|esc_attr'
		    )
		);

		$this->create_widget( $args );
	}

	function widget( $args, $instance ) {
		$plugin = DaTask::get_instance();
		$out = $args[ 'before_widget' ];
		$out .= $args[ 'before_title' ];
		$out .= $instance[ 'title' ];
		$out .= $args[ 'after_title' ];
		$tasks = new WP_Query( array( 'post_type' => 'task', 'showposts' => $instance[ 'number' ], 'meta_key' => $plugin->get_fields( 'tasks_counter' ), 'orderby' => 'meta_value_num', ) );
		if ( $tasks->have_posts() ) {
			$out .= '<ul class="widget-task-list">';
			while ( $tasks->have_posts() ) : $tasks->the_post();
				$out .= '<li><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></li>';
			endwhile;
			$out .= '</ul>';
			wp_reset_query();
		}
		$out .= $args[ 'after_widget' ];
		echo $out;
	}

}

// Register widget
if ( !function_exists( 'register_most_task_done_widget' ) ) {

	function register_most_task_done_widget() {
		register_widget( 'Most_Task_Done_Widget' );
	}

	add_action( 'widgets_init', 'register_most_task_done_widget', 1 );
}
