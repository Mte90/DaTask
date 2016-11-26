<?php

/**
 * Shortcode
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_Shortcode {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_shortcode( 'datask-progress', array( $this, 'progress' ) );
    add_shortcode( 'datask-badge', array( $this, 'badgeos' ) );
    add_shortcode( 'datask-dots', array( $this, 'dots' ) );
  }

  /**
   * 
   * The shortcode show the task in progress
   * 
   * @since    1.0.0
   * @return string The HTML of the Box of task in progress
   */
  public function progress() {
    if ( is_user_logged_in() ) {
	$current_user = wp_get_current_user();
	return dt_get_tasks_later( $current_user->user_login );
    }
  }

  /**
   * 
   * The shortcode show the badge associated of the task
   * 
   * @since    1.1.0
   * @return string The HTML from BadgeOS
   */
  public function badgeos() {
    if ( class_exists( 'BadgeOS' ) ) {
	$plugin = DaTask::get_instance();
	global $post;
	if ( get_post_type( $post->ID ) === 'task' ) {
	  $badge = get_post_meta( get_the_ID(), $plugin->get_fields( 'badgeos' ), true );
	  if ( $badge ) {
	    $html = badgeos_achievement_shortcode( array( 'id' => $badge ) );
	    echo $html;
	  }
	}
    }
  }

  public function dots( $atts ) {
    extract( shortcode_atts( array(
	  'type' => 'archive',
			  ), $atts ) );
    $get_tasks_by_user = get_tasks_by_user( get_current_user_id() );
    $terms = array();
    if ( $type === 'archive' ) {
	$terms = get_terms( 'task-area', array( 'hide_empty' => false ) );
    } elseif ( $type === 'user' ) {
	if ( !empty( $get_tasks_by_user ) ) {
	  foreach ( $get_tasks_by_user as $task_user ) {
	    $find_term = wp_get_post_terms( $task_user->task_ID, 'task-area' );
	    $terms[ $find_term[ 0 ]->term_id ] = $find_term[ 0 ];
	  }
	}
    }
    if ( empty( $terms ) ) {
	return false;
    }
    $html = '<ul class="datask-dots">';
    foreach ( $terms as $term ) {
	$i = 0;
	$html .= '<li>';
	$html .= '<a href="' . get_term_link( $term->term_id, 'task-area' ) . '">';
	$image = get_term_meta( $term->term_id, '_' . DT_TEXTDOMAIN . '_featured', true );
	$done = new WP_Query( array(
	    'post_type' => 'task',
	    'tax_query' => array(
		  array(
			'taxonomy' => 'task-area',
			'terms' => $term->slug,
			'field' => 'slug',
		  ),
	    ),
	    'order' => 'ASC'
		  ) );
	if ( !empty( $get_tasks_by_user ) ) {
	  foreach ( $done->posts as $task ) {
	    foreach ( $get_tasks_by_user as $task_user ) {
		if ( $task_user->task_ID === $task->ID ) {
		  $i++;
		}
	    }
	  }
	}
	$class = '';
	$percentage = 0;
	if ( $i !== 0 ) {
	  $percentage = ($i / count( $done->posts )) * 100;
	}
	if ( $percentage === 100 ) {
	  $class = ' class="datask-image-done"';
	}
	if ( !empty( $image ) ) {
	  $attachment = wp_get_attachment_image_src( datask_get_id_image_term( $image ), 'thumbnail' );
	  $html .= '<img src="' . $attachment[ 0 ] . '"' . $class . '>';
	} else {
	  $html .= $term->name;
	}
	$html .= '<progress class="progress" value="' . $percentage . '" max="100"></progress>';
	$html .= '</a>';
	$html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
  }

}

new DT_Shortcode();
