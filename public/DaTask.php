<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package   DaTask
 * @author    Daniele Scasciafratte <mte90net@gmail.com>
 * @license   GPL 2.0+
 * @link      http://mte90.net
 * @copyright 2015-2016
 */
class DaTask {

  /**
   * Instance of this class.
   *
   * @var      object
   *
   * @since    1.0.0
   */
  private static $instance;

  /**
   * Array of cpts of the plugin
   *
   * @var      array
   *
   * @since    1.0.0
   */
  protected $cpts = array( 'task' );

  /**
   * Array of capabilities by roles
   *
   * @var array
   *
   * @since 1.0.0
   */
  protected static $plugin_roles = array(
	'administrator' => array(
	    'edit_demo' => true,
	    'edit_others_demo' => true,
	),
	'editor' => array(
	    'edit_demo' => true,
	    'edit_others_demo' => true,
	),
	'author' => array(
	    'edit_demo' => true,
	    'edit_others_demo' => false,
	),
	'subscriber' => array(
	    'edit_demo' => false,
	    'edit_others_demo' => false,
	),
  );

  /**
   * Initialize the plugin by setting localization and loading public scripts
   * and styles.
   *
   * @since     1.0.0
   */
  private function __construct() {
    // Load plugin text domain
    add_action( 'init', array( $this, 'load_cpt_taxonomy' ), 4 );
    add_filter( 'pre_get_posts', array( $this, 'filter_search' ) );
    $options = get_option( DT_TEXTDOMAIN . '-settings' );
    $options_extra = get_option( DT_TEXTDOMAIN . '-settings-extra' );
    // Load public-facing style sheet and JavaScript.
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_vars' ) );
    // Ajax frontend
    require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_AJAX_Task.php' );
    // Search Shortcode
    require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_AJAX_Filter.php' );
    if ( isset( $options[ 'enable_frontend' ] ) && $options[ 'enable_frontend' ] === 'on' ) {
	// Frontend login system
	require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Frontend_Login.php' );
    }
    // Frontend Profile page
    require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Frontend_Profile.php' );
    if ( isset( $options_extra[ 'tweet_comments' ] ) && $options_extra[ 'tweet_comments' ] === 'on' ) {
	// Comment support for task
	require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Comment.php' );
    }
    // Task integration for template ecc
    require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Task_Support.php' );
    // Support for API Rest
    require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_API.php' );
    // BadgeOS support
    if ( class_exists( 'BadgeOS' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_BadgeOS.php' );
    }
    if ( defined( 'ARCHIVED_POST_STATUS_PLUGIN' ) && isset( $options[ 'archived_frontend' ] ) && $options[ 'archived_frontend' ] === 'on' ) {
	require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Archived.php' );
    }
    require_once( plugin_dir_path( __FILE__ ) . 'widgets/recents-tasks.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'widgets/most-task-done.php' );
  }

  /**
   * Return the version
   *
   * @since    1.0.0
   *
   * @return    Version const.
   */
  public function get_plugin_roles() {
    return self::$plugin_roles;
  }

  /**
   * Return the cpts
   *
   * @since    1.0.0
   *
   * @return    Cpts array
   */
  public function get_cpts() {
    return $this->cpts;
  }

  /**
   * Return an instance of this class.
   *
   * @since     1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {
    // If the single instance hasn't been set, set it now.
    if ( null === self::$instance ) {
	self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Add support for custom CPT on the search box
   *
   * @since    1.0.0
   * @param object $query WP_Query object.
   * @return object WP_Query object with task post type.
   */
  public function filter_search( $query ) {
    if ( $query->is_search && !is_admin() ) {
	$post_types = $query->get( 'post_type' );
	if ( $post_types === 'post' ) {
	  $post_types = array();
	  $query->set( 'post_type', array_push( $post_types, $this->cpts ) );
	}
    }
    return $query;
  }

  /**
   * Return the fields
   *
   * @since    1.0.0
   *
   * @param array $value Key for get the real field key.
   *
   * @return    array String of fields.
   */
  public function get_fields( $value = '' ) {
    $fields = array();
    $prefix = '_task_';
    $fields[ 'users_of_task' ] = $prefix . DT_TEXTDOMAIN . '_users';
    $fields[ 'tasks_counter' ] = $prefix . DT_TEXTDOMAIN . '_counter';
    $fields[ 'tasks_done_of_user' ] = $prefix . DT_TEXTDOMAIN . '_tasks_done';
    $fields[ 'tasks_later_of_user' ] = $prefix . DT_TEXTDOMAIN . '_tasks_later';
    $fields[ 'task_before' ] = $prefix . DT_TEXTDOMAIN . '_before';
    $fields[ 'task_prerequisites' ] = $prefix . DT_TEXTDOMAIN . '_prerequisites';
    $fields[ 'task_matters' ] = $prefix . DT_TEXTDOMAIN . '_matters';
    $fields[ 'task_steps' ] = $prefix . DT_TEXTDOMAIN . '_steps';
    $fields[ 'task_help' ] = $prefix . DT_TEXTDOMAIN . '_help';
    $fields[ 'task_completion' ] = $prefix . DT_TEXTDOMAIN . '_completion';
    $fields[ 'task_mentor' ] = $prefix . DT_TEXTDOMAIN . '_mentor';
    $fields[ 'task_next' ] = $prefix . DT_TEXTDOMAIN . '_next';
    $fields[ 'task_subtitle' ] = $prefix . DT_TEXTDOMAIN . '_subtitle';
    $fields[ 'badgeos' ] = 'badgeos_datask';
    if ( array_key_exists( $value, $fields ) ) {
	return $fields[ $value ];
    } elseif ( empty( $value ) ) {
	return $fields;
    }
  }

  /**
   * Load the CPT and Taxonomy
   *
   * @since    1.0.0
   */
  public function load_cpt_taxonomy() {
    $options_extra = get_option( DT_TEXTDOMAIN . '-settings-extra' );
    $task_post_type = array(
	  'supports' => array( 'title', 'comments' ),
	  'capabilities' => array(
		'edit_post' => 'edit_tasks',
		'edit_others_posts' => 'edit_others_tasks',
	  ),
	  'map_meta_cap' => true,
	  'show_in_rest' => true,
	  'menu_icon' => 'dashicons-welcome-add-page',
    );
    if ( isset( $options_extra[ 'cpt_slug' ] ) && !empty( $options_extra[ 'cpt_slug' ] ) ) {
	$task_post_type[ 'rewrite' ][ 'slug' ] = $options_extra[ 'cpt_slug' ];
    }
    register_via_cpt_core( array( __( 'Task', DT_TEXTDOMAIN ), __( 'Tasks', DT_TEXTDOMAIN ), 'task' ), $task_post_type );
    $tax = array(
	  'public' => true,
	  'show_in_rest' => true,
	  'capabilities' => array(
		'assign_terms' => 'edit_posts',
	  ) );
    $tax_area = $tax;
    if ( isset( $options_extra[ 'tax_area' ] ) && !empty( $options_extra[ 'tax_area' ] ) ) {
	$tax_area[ 'rewrite' ][ 'slug' ] = $options_extra[ 'tax_area' ];
    }
    register_via_taxonomy_core( array( __( 'Area', DT_TEXTDOMAIN ), __( 'Areas', DT_TEXTDOMAIN ), 'task-area' ), $tax_area, array( 'task' ) );

    $tax_difficulty = $tax;
    if ( isset( $options_extra[ 'tax_difficulty' ] ) && !empty( $options_extra[ 'tax_difficulty' ] ) ) {
	$tax_difficulty[ 'rewrite' ][ 'slug' ] = $options_extra[ 'tax_difficulty' ];
    }
    register_via_taxonomy_core( array( __( 'Difficulty', DT_TEXTDOMAIN ), __( 'Difficulties', DT_TEXTDOMAIN ), 'task-difficulty' ), $tax_difficulty, array( 'task' ) );

    $task_team = $tax;
    if ( isset( $options_extra[ 'tax_team' ] ) && !empty( $options_extra[ 'tax_team' ] ) ) {
	$task_team[ 'rewrite' ][ 'slug' ] = $options_extra[ 'tax_team' ];
    }
    register_via_taxonomy_core( array( __( 'Team', DT_TEXTDOMAIN ), __( 'Teams', DT_TEXTDOMAIN ), 'task-team' ), $task_team, array( 'task' ) );

    $task_minute = $tax;
    if ( isset( $options_extra[ 'tax_minute' ] ) && !empty( $options_extra[ 'tax_minute' ] ) ) {
	$task_minute[ 'rewrite' ][ 'slug' ] = $options_extra[ 'tax_minute' ];
    }
    register_via_taxonomy_core( array( __( 'Estimated minute', DT_TEXTDOMAIN ), __( 'Estimated minutes', DT_TEXTDOMAIN ), 'task-minute' ), $task_minute, array( 'task' ) );
  }

  /**
   * Register and enqueue public-facing style sheet.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {
    wp_enqueue_style( DT_TEXTDOMAIN . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), DT_VERSION );
  }

  /**
   * Register and enqueues public-facing JavaScript files.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {
    wp_enqueue_script( DT_TEXTDOMAIN . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), DT_VERSION );
  }

  /**
   * Print the PHP var in the HTML of the frontend for access by JavaScript
   *
   * @since    1.0.0
   */
  public function enqueue_js_vars() {
    global $post;
    if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'datask-search' ) ) {
	wp_localize_script( DT_TEXTDOMAIN . '-plugin-script', 'dt_js_search_vars', array(
	    'ajaxurl' => admin_url( 'admin-ajax.php' ),
	    'on_load_text' => __( 'Search to see results', DT_TEXTDOMAIN ),
	    'thisPage' => 1,
	    'nonce' => esc_js( wp_create_nonce( 'filternonce' ) )
		  )
	);
    }
    if ( is_user_logged_in() && (is_singular( 'task' ) || get_user_of_profile()) ) {
	wp_localize_script( DT_TEXTDOMAIN . '-plugin-script', 'dt_js_vars', array(
	    'ajaxurl' => admin_url( 'admin-ajax.php' )
		  )
	);
    }
  }

}

/*
 * @TODO:
 *
 * - 9999 is used for load the plugin as last for resolve some
 *   problems when the plugin use API of other plugins, remove
 *   if you don' want this
 */

add_action( 'plugins_loaded', array( 'DaTask', 'get_instance' ), 9999 );
