<?php

// Create custom widget class extending WPH_Widget
class Recent_Tasks_Widget extends WPH_Widget {

	function __construct() {
		$plugin = DaTask::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		$args = array(
		    'label' => __( 'Recents Tasks', $this->plugin_slug ),
		    'description' => __( 'Recent Tasks in the system', $this->plugin_slug ),
		);

		$args[ 'fields' ] = array(
		    array(
			'name' => __( 'Recents Tasks', $this->plugin_slug ),
			'id' => 'title',
			'type' => 'text',
			'class' => 'widefat',
			'std' => __( 'Recents Tasks', $this->plugin_slug ),
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
		$out = $args[ 'before_widget' ];
		$out .= $args[ 'before_title' ];
		$out .= $instance[ 'title' ];
		$out .= $args[ 'after_title' ];
		$tasks = new WP_Query( array( 'post_type' => 'task', 'showposts' => $instance[ 'number' ] ) );
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
if ( !function_exists( 'register_recent_task_widget' ) ) {
	function register_recent_task_widget() {
		register_widget( 'Recent_Tasks_Widget' );
	}

	add_action( 'widgets_init', 'register_recent_task_widget', 1 );
}
