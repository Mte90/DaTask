<?php

/**
 * BadgeOS support
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_BadgeOS {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.1.0
	 */
	public function __construct() {
		add_filter( 'badgeos_activity_triggers', array( $this, 'add_trigger' ) );
		add_action( 'badgeos_steps_ui_html_after_trigger_type', array( $this, 'task_list' ), 30, 2 );
		add_filter( 'badgeos_get_step_requirements', array( $this, 'requirements' ), 10, 2 );
		add_action( 'datask_badgeos_trigger', array( $this, 'trigger_event' ), 10, 20 );
		add_filter( 'badgeos_save_step', array( $this, 'save_step' ), 10, 3 );
		add_action( 'admin_footer', array( $this, 'step_js' ) );
	}

	/**
	 * Add the trigger
	 *
	 * @since    1.1.0
	 * @param array $triggers The triggers.
	 * @return array $triggers The new triggers.
	 */
	public function add_trigger( $triggers ) {
		$plugin = DaTask::get_instance();
		$triggers[ 'datask_badgeos_trigger' ] = __( 'DaTask Done Task', $plugin->get_plugin_slug() );
		return $triggers;
	}

	/**
	 * Add the select to pick the task
	 *
	 * @since    1.1.0
	 * @param integer $step_id The step id.
	 * @param integer $post_id The post id.
	 */
	public function task_list( $step_id, $post_id ) {
		$plugin = DaTask::get_instance();
		$tasks = new WP_Query( array(
		    'post_type' => 'task',
		    'posts_per_page' => -1 ) );
		echo '<select name="datask_trigger" class="select-datask-trigger select-datask-trigger-' . $post_id . '" autocomplete="off">';
		echo '<option value="">' . __( 'Select the task', $plugin->get_plugin_slug() ) . '</option>';
		$current_selection = get_post_meta( $step_id, '_badgeos_datask_trigger', true );
		foreach ( $tasks->posts as $task ) {
			echo '<option' . selected( $current_selection, $task->ID, false ) . ' value="' . $task->ID . '">' . $task->post_title . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Add the requirements for the step
	 *
	 * @since    1.1.0
	 * @param array $requirements The requirements of the step.
	 * @return array $requirements The new requirements.
	 */
	public function requirements( $requirements, $step_id ) {
		$plugin = DaTask::get_instance();
		$requirements[ 'datask_done' ] = get_post_meta( $step_id, '_badgeos_datask_trigger', true ); 
		return $requirements;
	}

	/**
	 * Save the task associated
	 *
	 * @since    1.1.0
	 * @param array $title The title.
	 * @param array $step_id The step id.
	 * @param array $step_data The data sent.
	 */
	public function trigger_event() {
		global $blog_id, $wpdb;
		$plugin = DaTask::get_instance();
		// Setup args
		$args = func_get_args();

		$userID = get_current_user_id();

		if ( is_array( $args ) && isset( $args[ 0 ] ) && isset( $args[ 0 ][ 'created_by' ] ) ) {
			$userID = ( int ) $args[ 0 ][ 'created_by' ];
		}

		if ( empty( $userID ) ) {
			return;
		}

		$user_data = get_user_by( 'id', $userID );

		if ( empty( $user_data ) ) {
			return;
		}

		// Grab the current trigger
		$this_trigger = current_filter();

		// Update hook count for this user
		$new_count = badgeos_update_user_trigger_count( $userID, $this_trigger, $blog_id );

		// Mark the count in the log entry
		badgeos_post_log_entry( null, $userID, null, sprintf( __( '%1$s triggered %2$s (%3$dx)', 'badgeos' ), $user_data->user_login, __( 'Task Done', $plugin->get_plugin_slug() ), $new_count ) );

		// Now determine if any badges are earned based on this trigger event
		$triggered_achievements = $wpdb->get_results( $wpdb->prepare( "
		SELECT post_id
		FROM   $wpdb->postmeta
		WHERE  meta_key = '_badgeos_datask_trigger'
				AND meta_value = %s
		", $this_trigger ) );
		foreach ( $triggered_achievements as $achievement ) {
			badgeos_maybe_award_achievement_to_user( $achievement->post_id, $userID, $this_trigger, $blog_id, $args );
		}
	}

	/**
	 * Add the trigger
	 *
	 * @since    1.0.0
	 * @param array $triggers The triggers.
	 * @return array $triggers The new triggers.
	 */
	public function save_step( $title, $step_id, $step_data ) {
		if ( 'datask_badgeos_trigger' == $step_data[ 'trigger_type' ] ) {
			update_post_meta( $step_id, '_badgeos_datask_trigger', $step_data[ 'datask_badgeos_trigger' ] );
			update_post_meta( $step_data[ 'datask_badgeos_trigger' ], 'badgeos_datask', $step_data['datask_badgeos_task'] );
		}
	}

	/**
	 * Add the js code for the step
	 *
	 * @since    1.1.0
	 */
	public function step_js() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
			  // Inject our custom step details into the update step action
			  $(document).on('update_step_data', function (event, step_details, step) {
			    step_details.datask_badgeos_trigger = $('.select-datask-trigger', step).val();
			    step_details.datask_badgeos_task = $('#post_ID').val();
			  });
			});
		</script>
		<?php
	}

}

new DT_BadgeOS();
