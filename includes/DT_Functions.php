<?php
/**
 * DaTask Functions
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */

/**
 * Add the user id on the task post types and the task post types in the user meta
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @param     integer $task_id ID of task post type.
 * @return    bool true
 */
function dt_set_completed_task_for_user_id( $user_id, $task_id ) {
  $plugin = DaTask::get_instance();
  $counter = get_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), true );
  if ( empty( $counter ) ) {
    $counter = 1;
  } else {
    $counter = (( int ) $counter) + 1;
  }
  update_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), $counter );
  $tasks_later_of_user = get_tasks_later_by_user( $user_id );
  if ( isset( $tasks_later_of_user[ $task_id ] ) ) {
    unset( $tasks_later_of_user[ $task_id ] );
    update_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), serialize( $tasks_later_of_user ) );
  }

  if ( class_exists( 'BadgeOS' ) ) {
    do_action( 'datask_badgeos_trigger' );
  }

  /*
   * Fires before the end of function `dt_set_completed_task_for_user_id`
   *
   * @since 1.0.0
   */
  do_action( 'dt_set_completed_task', $user_id, $task_id );
  return true;
}

/**
 * Add in the profile the ids of the task for later
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @param     integer $task_id ID of task post type.
 * @return    bool true
 */
function dt_set_task_later_for_user_id( $user_id, $task_id ) {
  $plugin = DaTask::get_instance();
  $tasks_later_of_user = get_tasks_later_by_user( $user_id );
  if ( !isset( $tasks_later_of_user[ $task_id ] ) ) {
    $tasks_later_of_user[ $task_id ] = time();
    update_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), serialize( $tasks_later_of_user ) );
  }

  /*
   * Fires before the end of function `dt_set_task_later_for_user_id`
   *
   * @since 1.0.0
   */
  do_action( 'dt_set_task_later' );
  return true;
}

/**
 * Add the user id on the task post types and the task post types in the user meta
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @param     integer $task_id ID of task post type.
 * @return    bool true
 */
function dt_remove_complete_task_for_user_id( $user_id, $task_id ) {
  $old_task = get_tasks_by_user( $user_id );
  foreach ( $old_task as $task ) {
    if ( $task->task_ID === $task_id ) {
	wp_delete_post( $task->ID );
    }
  }
  $plugin = DaTask::get_instance();
  $counter = get_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), true );
  if ( empty( $counter ) ) {
    $counter = 1;
  } else {
    $counter = ( int ) $counter - 1;
  }
  update_post_meta( $task_id, $plugin->get_fields( 'tasks_counter' ), $counter );

  /*
   * Fires before the end of function `dt_set_completed_task_for_user_id`
   *
   * @since 1.0.0
   */
  do_action( 'dt_remove_complete_task' );
  return true;
}

/**
 * Get the task done from the user with html
 *
 * @since     1.0.0
 * @return    @string html
 */
function dt_get_tasks_completed() {
  $print = '';
  if ( is_author() ) {
    $user_id = get_user_by( 'id', get_user_of_profile( true ) );
  } else {
    $user_id = get_user_by( 'login', get_user_of_profile() );
  }
  if ( !empty( $user_id ) ) {
    $user_id = $user_id->data->ID;
    $tasks_user = get_tasks_by_user( $user_id );
    if ( !empty( $tasks_user ) ) {
	$tasks_user = array_reverse( $tasks_user, true );
	$print = '<h4 class="alert alert-success">' . sprintf( __( '%d Tasks Completed', DT_TEXTDOMAIN ), count( $tasks_user ) ) . '</h4>';
	$print .= '<ul>';
	foreach ( $tasks_user as $task ) {
	  $print .= '<li><a href="' . get_permalink( $task->task_ID ) . '">' . $task->post_title . '</a> - ' . date_i18n( get_option( 'date_format' ), strtotime( $task->post_date ) ) . '</li>';
	}
	$print .= '</ul>';
	wp_reset_postdata();

	/*
	 * Filter the box with task done
	 *
	 * @since 1.0.0
	 *
	 * @param string $html the html output
	 */
	$print = apply_filters( 'dt_get_completed_task', $print );
    } else {
	$print .= '<h5>' . __( 'Nothing task done :(', DT_TEXTDOMAIN ) . '</h5>';
    }
  } else {
    $print = __( 'This profile not exist!', DT_TEXTDOMAIN );
  }
  return $print;
}

/**
 * Print the task done from the user with html
 *
 * @since     1.0.0
 */
function dt_tasks_completed() {
  echo dt_get_tasks_completed();
}

/**
 * Get the task later from the user with html
 *
 * @since     1.0.0
 * @param     string $user ID of the user.
 * @return    string html
 */
function dt_get_tasks_later( $user = null ) {
  $print = '';
  if ( $user === null ) {
    if ( is_author() ) {
	$user = get_user_by( 'id', get_user_of_profile( true ) );
    } else {
	$user = get_user_by( 'login', get_user_of_profile() );
    }
  }
  if ( !empty( $user ) ) {
    $current_user = wp_get_current_user();
    if ( $current_user->user_nicename === $user->user_nicename ) {
	$user_id = $user->data->ID;
	$tasks_later_user = get_tasks_later_by_user( $user_id );
	if ( is_array( $tasks_later_user ) ) {
	  $tasks_later_user = array_reverse( $tasks_later_user, true );
	  $print .= '<h4 class="alert alert-info">' . __( 'Tasks in progress', DT_TEXTDOMAIN ) . '</h4>';
	  if ( !empty( $tasks_later_user ) ) {
	    $tasks_later_user = array_reverse( $tasks_later_user, true );
	    $task_implode = array_keys( $tasks_later_user );
	    $tasks = new WP_Query( array(
		  'post_type' => 'task',
		  'post__in' => $task_implode,
		  'orderby' => 'post__in',
		  'posts_per_page' => -1 ) );
	    $print .= '<ul>';
	    foreach ( $tasks->posts as $task ) {
		$area = get_the_terms( $task->ID, 'task-area' );
		$minute = get_the_terms( $task->ID, 'task-minute' );
		$print .= '<li><a href="' . get_permalink( $task->ID ) . '">' . $task->post_title . '</a> - ' . $area[ 0 ]->name . ' - ' . $minute[ 0 ]->name . ' ' . __( 'minute estimated', DT_TEXTDOMAIN ) . '</li>';
	    }
	    $print .= '</ul>';
	    wp_reset_postdata();

	    /*
	     * Filter the box with task later
	     *
	     * @since 1.0.0
	     *
	     * @param string $html the html output
	     */
	    $print = apply_filters( 'dt_get_task_later', $print );
	  } else {
	    $print .= __( "You don't have any task to do! Pick one!", DT_TEXTDOMAIN );
	  }
	}
    }
  } else {
    $print = __( 'This profile not exist!', DT_TEXTDOMAIN );
  }
  return $print;
}

/**
 * Get the mentors of the task
 *
 * @since     1.0.0
 * @return    array html
 */
function dt_get_mentors( $id = '' ) {
  if ( empty( $id ) ) {
    $id = get_the_ID();
  }
  $plugin = DaTask::get_instance();
  $mentors = get_post_meta( $id, $plugin->get_fields( 'task_mentor' ), true );
  if ( empty( $mentors ) ) {
    return false;
  }
  $mentors = explode( ',', str_replace( ' ', '', $mentors ) );
  return $mentors;
}

/**
 * Print the task later from the user with html
 *
 * @since     1.0.0
 * @param     string $user ID of the user.
 */
function dt_tasks_later( $user = null ) {
  echo dt_get_tasks_later( $user );
}

/**
 * Print the task later from the user with html
 *
 * @since     1.0.0
 * @return    @string|null value Nick of the user
 */
function get_user_of_profile( $id = false ) {
  global $wp_query;
  // Get nick from the url of the page
  if ( is_author() ) {
    $username = $wp_query->query_vars[ 'author_name' ];
    if ( $id ) {
	$user_id = $wp_query->query_vars[ 'author' ];
	$user = get_userdata( $user_id );
	if ( $user ) {
	  return $user_id;
	}
    }
    if ( username_exists( $username ) ) {
	return $username;
    }
  } elseif ( array_key_exists( 'member-feed', $wp_query->query_vars ) ) {
    $username = str_replace( '%20', ' ', $wp_query->query[ 'member-feed' ] );
    if ( username_exists( $username ) ) {
	return $username;
    }
    // If the url don't have the nick get the actual
  } else {
    return null;
  }
}

/**
 * Return the task ids of the user
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @return    array the ids
 */
function get_tasks_by_user( $user_id ) {
  if ( $user_id === 0 ) {
    return false;
  }
  $args = array(
	'post_type' => 'datask-log',
	'posts_per_page' => -1,
	'author' => $user_id,
	'tax_query' => array(
	    array(
		  'taxonomy' => 'wds_log_type',
		  'field' => 'slug',
		  'terms' => array( 'error', 'remove' ),
		  'operator' => 'NOT',
	    ),
	)
  );
  $query = new WP_Query( $args );
  if ( count( $query->posts ) > 0 ) {
    return $query->posts;
  }
  return false;
}

/**
 * Return the task later ids of the user
 *
 * @since     1.0.0
 * @param     integer $user_id ID of the user.
 * @return    array the ids
 */
function get_tasks_later_by_user( $user_id ) {
  $plugin = DaTask::get_instance();
  return unserialize( get_user_meta( $user_id, $plugin->get_fields( 'tasks_later_of_user' ), true ) );
}

/**
 * Return the user ids by task
 *
 * @since     1.0.0
 * @param     integer $task_id ID of the user.
 * @return    array the ids
 */
function get_users_by_task( $task_id ) {
  $args = array(
	'post_type' => 'datask-log',
	'posts_per_page' => -1,
	'meta_key' => DT_TEXTDOMAIN . '_id',
	'meta_value' => $task_id,
	'tax_query' => array(
	    array(
		  'taxonomy' => 'wds_log_type',
		  'field' => 'slug',
		  'terms' => array( 'error', 'remove' ),
		  'operator' => 'NOT',
	    ),
	)
  );
  $query = new WP_Query( $args );
  if ( count( $query->posts ) > 0 ) {
    return $query->posts;
  }
  return false;
}

/**
 * Check if the user have done the task
 *
 * @since     1.0.0
 * @param     integer $task_id ID of the task.
 * @param     integer $user_id ID of the user.
 *
 * @return    boolean
 */
function has_task( $task_id, $user_id = null ) {
  if ( $user_id === null ) {
    $user_id = get_current_user_id();
  }
  $tasks = get_tasks_by_user( $user_id );
  if ( !empty( $tasks ) ) {
    foreach ( $tasks as $task ) {
	if ( $task->task_ID === $task_id ) {
	  return true;
	}
    }
  }
  return false;
}

/**
 * Check if the user have the task later
 *
 * @since     1.0.0
 * @param     integer $task_id ID of the task.
 * @param     integer $user_id ID of the user.
 *
 * @return    boolean
 */
function has_later_task( $task_id, $user_id = null ) {
  if ( $user_id === null ) {
    $user_id = get_current_user_id();
  }
  $tasks = get_tasks_later_by_user( $user_id );
  if ( isset( $tasks[ $task_id ] ) ) {
    return true;
  } else {
    return false;
  }
}

/**
 * Get list of Badge of BadgeOS
 * Based on https://gist.github.com/tw2113/6c31366d094eee6d5151
 *
 * @since     1.1.0
 * @param     integer $user ID of the user.
 */
function datask_badgeos_user_achievements( $user ) {
  if ( class_exists( 'BadgeOS' ) ) {
    $output = '';
    $achievements = array_unique( badgeos_get_user_earned_achievement_ids( $user, '' ) );
    $output .= '<h4 class="alert alert-info">' . __( 'Badge Earned by the user', DT_TEXTDOMAIN ) . '</h4>';
    if ( !empty( $achievements ) ) {
	$output .= '<ul>';
	foreach ( $achievements as $achievement_id ) {
	  $output .= '<li><a href="' . get_permalink( $achievement_id ) . '">' . badgeos_get_achievement_post_thumbnail( $achievement_id ) . '</a></li>';
	}
	$output .= '</ul>';
    }
    echo $output;
  }
}

/**
 * Echo the subtitle of the task
 *
 * @param    bool $echo Print or not to print.
 * @return   bool|string Echo or the value
 * @since    1.0.0
 */
function the_task_subtitle( $echo = true ) {
  $plugin = DaTask::get_instance();
  if ( $echo ) {
    echo get_post_meta( get_the_ID(), $plugin->get_fields( 'task_subtitle' ), true );
  } else {
    return get_post_meta( get_the_ID(), $plugin->get_fields( 'task_subtitle' ), true );
  }
}

/**
 * Print Task button
 *
 * @since    1.0.0
 */
function datask_buttons() {
  if ( is_user_logged_in() ) {
    ?>
    <div class="dt-buttons">
	  <?php
	  wp_nonce_field( 'dt-task-action', 'dt-task-nonce' );
	  if ( is_the_prev_task_done() ) {
	    ?>
	    <button type="submit" class="btn btn-primary complete <?php
	    if ( has_task( get_the_ID() ) && !has_later_task( get_the_ID() ) ) {
		echo 'disabled';
	    }
	    ?>" id="complete-task" data-complete="<?php the_ID(); ?>">
			<?php
			if ( has_later_task( get_the_ID() ) ) {
			  echo '<i class="fa fa-exclamation-circle"></i>';
			}
			if ( has_task( get_the_ID() ) && !has_later_task( get_the_ID() ) ) {
			  echo '<i class="fa fa-check"></i>';
			}
			?><?php _e( 'Complete this task', DT_TEXTDOMAIN ); ?></button>
	    <?php
	  }
	  ?>
        <button type="submit" class="btn btn-secondary save-later <?php
	  if ( has_later_task( get_the_ID() ) ) {
	    echo 'disabled';
	  }
	  ?>" id="save-for-later" data-save-later="<?php the_ID(); ?>"><i class="dt-refresh-hide fa fa-refresh"></i>
		    <?php
		    if ( has_later_task( get_the_ID() ) ) {
			echo '<i class="fa fa-check"></i>';
		    }
		    ?><?php _e( 'Save for later', DT_TEXTDOMAIN ); ?></button>
	  <?php
	  if ( is_the_prev_task_done() ) {
	    ?>
	    <button type="submit" class="btn btn-warning remove <?php
	    if ( has_task( get_the_ID() ) && has_later_task( get_the_ID() ) ) {
		echo 'disabled';
	    }
	    ?>" id="remove-task" data-remove="<?php the_ID(); ?>"><i class="dt-refresh-hide fa fa-refresh"></i><?php _e( 'Remove complete task', DT_TEXTDOMAIN ); ?></button>
		  <?php
		}
		?>
    </div>
    <?php
    $approval = datask_require_approval();
    if ( !empty( $approval ) && $approval !== 'none' ) {
	echo '<h4 class="alert alert-danger">';
	if ( $approval === 'comment' ) {
	  _e( 'This task require a comment for the final approval!', DT_TEXTDOMAIN );
	} else if ( $approval === 'email' ) {
	  _e( 'This task require to contact one of the mentors for the final approval!', DT_TEXTDOMAIN );
	}
	echo '</h4>';
    }
  } else {
    echo '<h4 class="alert alert-danger">';
    _e( 'Save your history of tasks done or in progress with a free account!', DT_TEXTDOMAIN );
    echo '</h4>';
  }
}

/**
 * User contact form
 *
 * @since    1.0.0
 */
function datask_user_form() {
  if ( is_user_logged_in() ) {
    $user = get_user_by( 'id', get_user_of_profile( true ) );
    $current_user = wp_get_current_user();
    if ( $user->roles[ 0 ] != 'subscriber' && $current_user->user_login !== $user->user_login ) {
	$content .= '<h4 class="alert alert-warning">' . __( 'Contact', DT_TEXTDOMAIN ) . ' ' . $user->display_name . '</h4>';
	$content .= '<h5>' . __( 'If you are contacting him for a task don\'t forget to mention it!', DT_TEXTDOMAIN ) . '</h5>';
	$content .= '<div class="form-group"><textarea class="form-control" name="datask-email-subject" cols="45" rows="8" aria-required="true" autocomplete="off"></textarea></div>';
	$content .= wp_nonce_field( 'dt_contact_user', 'dt_user_nonce', true, false );
	$content .= '<button type="submit" data-user="' . get_user_of_profile() . '" class="button btn btn-warning"><i class="dashicons-email-alt"></i>' . __( 'Sent', DT_TEXTDOMAIN ) . '</button>';
	echo $content;
    }
  }
}

/**
 * Check if the task require a manual approval
 * 
 * @param integer $task_id The Task ID.
 * @return string
 */
function datask_require_approval( $task_id = '' ) {
  if ( empty( $task_id ) ) {
    $task_id = get_the_ID();
  }
  return get_post_meta( $task_id, '_datask_approval', true );
}

/**
 * Return the task suggested before the task itself
 * 
 * @param integer $task_id The Task ID.
 * @return string
 */
function datask_task_before( $task_id = '' ) {
  $plugin = DaTask::get_instance();
  if ( empty( $task_id ) ) {
    $task_id = get_the_ID();
  }
  $befores = get_post_meta( $task_id, $plugin->get_fields( 'task_before' ), true );
  if ( empty( $befores ) ) {
    return false;
  }
  $befores = explode( ',', str_replace( ' ', '', $befores ) );
  return $befores;
}

/**
 * Return the task suggested next the task itself
 * 
 * @param integer $task_id The Task ID.
 * @return string
 */
function datask_task_next( $task_id = '' ) {
  $plugin = DaTask::get_instance();
  if ( empty( $task_id ) ) {
    $task_id = get_the_ID();
  }
  $next = get_post_meta( $task_id, $plugin->get_fields( 'task_next' ), true );
  if ( empty( $next ) ) {
    return false;
  }
  $next = explode( ',', str_replace( ' ', '', $next ) );
  return $next;
}

/**
 * Output an anchor to the profile user
 * 
 * @param string $username
 * @param string $text
 * @return string
 */
function dt_profile_link( $username, $text ) {
  return '<a href="' . home_url( '/author/' . $username ) . '" target="_blank">' . $text . '</a>';
}

function is_the_prev_task_done( $id = '' ) {
  if ( !is_user_logged_in() ) {
    return true;
  }

  if ( empty( $id ) ) {
    $id = get_the_ID();
  }
  $terms = get_the_terms( $id, 'task-team' );
  $project = $terms[ 0 ];
  $prev = new WP_Query( array(
	'post_type' => 'task',
	'meta_key' => '_sortable_posts_order_task-team_' . $project->slug,
	'orderby' => 'meta_value_num',
	'order' => 'ASC',
	'meta_query' => array(
	    'relation' => 'AND',
	    array(
		  'key' => '_sortable_posts_order_task-team_' . $project->slug,
		  'compare' => '<',
		  'value' => get_post_meta( get_the_ID(), '_sortable_posts_order_task-team_' . $project->slug, true )
	    )
	)
	    ) );
  if ( empty( $prev->posts ) ) {
    return true;
  }
  $prev = $prev->posts[ count( $prev->posts ) - 1 ];
  $get_tasks_by_user = get_tasks_by_user( get_current_user_id() );
  if ( !empty( $get_tasks_by_user ) ) {
    foreach ( $get_tasks_by_user as $task ) {
	if ( $task->task_ID === $prev->ID ) {
	  return true;
	}
    }
  }
  return false;
}

function datask_get_id_image_term( $image_url ) {
  global $wpdb;
  $attachment = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . " WHERE guid='%s';", esc_sql( $image_url ) ) );
  return $attachment[ 0 ];
}

function datask_course_user() {
  echo do_shortcode( '[datask-dots type="user"]' );
}
