<?php

/**
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

    add_action( 'dt_task_info', array( $this, 'dt_task_info' ) );
    add_filter( 'the_content', array( $this, 'dt_task_content' ) );
    add_filter( 'the_excerpt', array( $this, 'dt_task_excerpt' ) );
    add_shortcode( 'datask-progress', array( $this, 'datask_progress' ) );
    add_shortcode( 'datask-badge', array( $this, 'datask_badgeos' ) );
  }

  /**
   * Add class in the body on the frontend
   *
   * @since    1.0.0
   * @param array $classes Classes of the body.
   * @return array $classes Classes of the body
   */
  public function add_dt_class( $classes ) {
    global $post;
    if ( is_singular( 'task' ) ) {
	$classes[] = DT_TEXTDOMAIN . '-task';
    } elseif ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'datask-search' ) ) {
	$classes[] = DT_TEXTDOMAIN . '-search';
    }
    return $classes;
  }

  /**
   * Override the template system on the frontend
   *
   * @since    1.0.0
   * @param string $original_template The path of the template file.
   * @return string $original_template The path of the template file.
   */
  public function load_content_task( $original_template ) {
    if ( is_singular( 'task' ) ) {
	return wpbp_get_template_part( DT_TEXTDOMAIN, 'single', 'task', false );
    }
    return $original_template;
  }

  public function taxonomy_as_list_comma( $taxonomy, $id = '' ) {
    $html = '';
    if ( empty( $id ) ) {
	$id = get_the_ID();
    }
    $terms = get_the_terms( $id, $taxonomy );
    if ( empty( $terms ) ) {
	return '';
    }
    foreach ( $terms as $term ) {
	$html .= '<a href="' . get_term_link( $term->slug, $taxonomy ) . '">' . $term->name . '</a>, ';
    }
    return rtrim( $html, ', ' );
  }

  public function task_as_list( $ids, $label ) {
    $content = '<h5 class="alert alert-danger">' . $label . ': </h5>';
    $content .= '<p class="lead">';
    $html = '';
    $ids = new WP_Query( array(
	  'post_type' => 'task',
	  'post__in' => $ids,
	  'posts_per_page' => -1 ) );
    if ( empty( $ids->posts ) ) {
	return '';
    }
    if ( is_user_logged_in() ) {
	$get_tasks_by_user = get_tasks_by_user( get_current_user_id() );
    }
    foreach ( $ids->posts as $post ) {
	$link = '<a href="' . get_permalink( $post->ID ) . '" target="_blank">' . $post->post_title . '</a>';
	$final_html = '';
	if ( is_user_logged_in() ) {
	  $final_html = '<i class="fa fa-exclamation"></i> <i>' . $link . '</i>';
	  foreach ( $get_tasks_by_user as $task ) {
	    if ( $task->task_ID === $post->ID ) {
		$final_html = $link . ' <i class="fa fa-check"></i>';
	    }
	  }
	}
	// Get last post
	if ( $ids->posts[ count( $ids->posts ) - 1 ]->ID !== $post->ID ) {
	  $final_html .= ', ';
	}
	$html .= $final_html;
    }
    wp_reset_postdata();
    $content .= $html;
    $content .= '</p>';
    return $content;
  }

  public function prev_next_task_as_list( $what, $label, $id = '' ) {
    if ( class_exists( 'SortablePosts' ) ) {
	if ( $what === 'prev' ) {
	  $what = '<';
	} elseif ( $what === 'next' ) {
	  $what = '>';
	}

	if ( empty( $id ) ) {
	  $id = get_the_ID();
	}

	$project = get_the_terms( $id, 'task-area' );
	$project = $project[ 0 ];
	$tasks = new WP_Query( array(
	    'post_status' => 'publish',
	    'post_type' => 'task',
	    'meta_key' => '_sortable_posts_order_task-area_' . $project->slug,
	    'orderby' => 'meta_value_num',
	    'order' => 'ASC',
	    'meta_query' => array(
		  'relation' => 'AND',
		  array(
			'key' => '_sortable_posts_order_task-area_' . $project->slug,
			'compare' => $what,
			'value' => get_post_meta( get_the_ID(), '_sortable_posts_order_task-area_' . $project->slug, true )
		  )
	    )
		  ) );
	if ( empty( $tasks->posts ) ) {
	  return '';
	}

	$content = '<h4 class="alert alert-danger">' . $label . ': </h4>';
	$content .= '<p class="lead">';
	$html = '';
	if ( is_user_logged_in() ) {
	  $get_tasks_by_user = get_tasks_by_user( get_current_user_id() );
	}
	foreach ( $tasks->posts as $post ) {
	  $link = '<a href="' . get_permalink( $post->ID ) . '" target="_blank">' . $post->post_title . '</a>';
	  $final_html = '';
	  if ( is_user_logged_in() ) {
	    $final_html = '<i class="fa fa-exclamation"></i> <i>' . $link . '</i>';
	    foreach ( $get_tasks_by_user as $task ) {
		if ( $task->task_ID === $post->ID ) {
		  $final_html = $link . ' <i class="fa fa-check"></i>';
		}
	    }
	  }
	  // Get last post
	  if ( $tasks->posts[ count( $tasks->posts ) - 1 ]->ID !== $post->ID ) {
	    $final_html .= ', ';
	  }
	  $html .= $final_html;
	}
	wp_reset_postdata();
	$content .= $html;
	$content .= '</p>';
    }
    return $content;
  }

  public function users_as_list( $ids, $label, $use_author = false ) {
    $content = '<h5 class="alert alert-info">' . $label . ': </h5>';
    $content .= '<p class="lead">';
    foreach ( $ids as $user_id ) {
	if ( $use_author ) {
	  $user_id = $user_id->post_author;
	}
	$user = get_user_by( 'id', $user_id );
	$content .= dt_profile_link( $user->user_login, trim( $user->display_name ) ? $user->display_name : $user->user_login );
	// Get last user
	if ( $ids[ count( $ids ) - 1 ] !== $user_id ) {
	  $content .= ', ';
	}
    }
    $content = rtrim( $content, ', ' );
    $content .= '</p>';
    return $content;
  }

  /**
   * Echo the data about the task
   *
   * @since    1.0.0
   */
  public function dt_task_info() {
    echo '<div class="alert alert-warning"><b>' . __( 'Last edit: ', DT_TEXTDOMAIN ) . '</b> ' . ucfirst( get_the_modified_date() ) . '</div>';
    echo '<ul class="list list-inset">';
    $taxonomies[ 'task-team' ] = __( 'Team', DT_TEXTDOMAIN );
    $taxonomies[ 'task-area' ] = __( 'Project', DT_TEXTDOMAIN );
    $taxonomies[ 'task-difficulty' ] = __( 'Difficulty', DT_TEXTDOMAIN );
    $taxonomies[ 'task-minute' ] = __( 'Estimated time', DT_TEXTDOMAIN );
    foreach ( $taxonomies as $tax => $label ) {
	$print = $this->taxonomy_as_list_comma( $tax );
	if ( !empty( $print ) ) {
	  echo '<li><b>' . $label . ': </b>';
	  echo $print;
	  echo '</li>';
	}
    }
    echo '</ul>';
    if ( is_user_logged_in() && !is_the_prev_task_done() ) {
	echo '<div class="alert alert-danger"><b>' . __( 'You need to do the previous task before this one', DT_TEXTDOMAIN ) . '</b> </div>';
    }
  }

  /**
   * Echo the content of the task
   *
   * @since    1.0.0
   * @param string $content HTML code of the task data.
   * @return string $content HTML code.
   */
  public function dt_task_content( $content ) {
    global $post, $wp_embed;
    $plugin = DaTask::get_instance();
    if ( get_post_type( $post->ID ) === 'task' ) {
	$content = wpautop( the_task_subtitle( false ) );
    }
    if ( is_singular( 'task' ) ) {
	//Required task
	$required = datask_task_before( get_the_ID() );
	if ( !empty( $required ) ) {
	  $content .= $this->task_as_list( $required, __( 'Required or Suggested tasks', DT_TEXTDOMAIN ) );
	}
	//Prerequisites field
	$prerequisites = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_prerequisites' ), true );
	if ( !empty( $prerequisites ) ) {
	  $content .= '<h4 class="alert alert-success">' . __( 'Prerequisites', DT_TEXTDOMAIN ) . '</h4>';
	  $content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $prerequisites ) ) ) );
	}
	//Previous task
	$content .= $this->prev_next_task_as_list( "prev", __( 'Previous', DT_TEXTDOMAIN ) );
	//Matters field
	$matters = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_matters' ), true );
	if ( !empty( $matters ) ) {
	  $content .= '<h4 class="alert alert-success">' . __( 'Why this matters', DT_TEXTDOMAIN ) . '</h4>';
	  $content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $matters ) ) ) );
	}
	//Steps field
	$steps = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_steps' ), true );
	if ( !empty( $steps ) ) {
	  $content .= '<h4 class="alert alert-success">' . __( 'Steps', DT_TEXTDOMAIN ) . '</h4>';
	  $content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $steps ) ) ) );
	}
	//Help field
	$help = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_help' ), true );
	if ( !empty( $help ) ) {
	  $content .= '<h4 class="alert alert-success">' . __( 'Need Help?', DT_TEXTDOMAIN ) . '</h4>';
	  $content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $help ) ) ) );
	}
	//Completion field
	$completion = get_post_meta( get_the_ID(), $plugin->get_fields( 'task_completion' ), true );
	if ( !empty( $completion ) ) {
	  $content .= '<h4 class="alert alert-success">' . __( 'Completion', DT_TEXTDOMAIN ) . '</h4>';
	  $content .= wpautop( do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $completion ) ) ) );
	}
	//Next task
	$next = datask_task_before( get_the_ID() );
	if ( !empty( $next ) ) {
	  $content .= $this->task_as_list( $next, __( 'Good next tasks', DT_TEXTDOMAIN ) );
	}
	//Next task
	$content .= $this->prev_next_task_as_list( "next", __( 'Next', DT_TEXTDOMAIN ) );
	//Mentors
	$mentors = dt_get_mentors( get_the_ID() );
	if ( is_array( $mentors ) ) {
	  $content .= $this->users_as_list( $mentors, __( 'Mentor(s)', DT_TEXTDOMAIN ) );
	}
	//Log of users
	$logs = get_users_by_task( get_the_ID() );
	if ( is_array( $logs ) ) {
	  $content .= $this->users_as_list( $logs, __( 'List of users who completed this task', DT_TEXTDOMAIN ), true );
	}
    }
    return $content;
  }

  /**
   * Echo the excerpt of the task
   *
   * @since    1.0.0
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
   * @return string The HTML of the Box of task in progress
   */
  public function datask_progress() {
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
  public function datask_badgeos() {
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

}

new DT_Task_Support();
