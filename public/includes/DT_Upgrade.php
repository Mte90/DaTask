<?php

/**
 * Upgrade from 1.0.0
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_Upgrade {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    $this->upgrade_100();
  }

  public function upgrade_100() {
    $tasks = get_posts( array( 'numberposts' => -1, 'post_status' => 'any', 'post_type' => 'task' ) );

    foreach ( $tasks as $task ) : setup_postdata( $task );
	$fields = array(
	    '_task_datask_next' => '_datask_next',
	    '_task_datask_mentor' => '_datask_mentor',
	    '_task_datask_before' => '_datask_before',
	    '_task_datask_users' => '_datask_users',
	    '_task_datask_counter' => '_datask_counter',
	    '_task_datask_tasks_done' => '_datask_tasks_done',
	    '_task_datask_tasks_later' => '_datask_tasks_later',
	    '_task_datask_counter' => '_datask_prerequisites',
	    '_task_datask_matters' => '_datask_matters',
	    '_task_datask_steps' => '_datask_steps',
	    '_task_datask_help' => '_datask_help',
	    '_task_datask_completion' => '_datask_completion',
	    '_task_datask_subtitle' => '_datask_subtitle'
	);

	foreach ( $fields as $key => $newkey ) {
	  $text = get_post_meta( $task->ID, $key, true );
	  if ( !empty( $text ) ) {
	    update_post_meta( $task->ID, $newkey, $text );
	    delete_post_meta( $task->ID, $key );
	  }
	}
    endforeach;
  }

}

new DT_Upgrade();
