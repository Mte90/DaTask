<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-wp-datask-admin.php`
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DaTask {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_slug = 'datask';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_name = 'DaTask';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Array of cpts of the plugin
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected $cpts = array( 'task' );

	/**
	 * Array of capabilities by roles
	 * 
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected static $plugin_roles = array(
	    'administrator' => array(
		'edit_tasks' => true,
		'edit_others_tasks' => true,
	    ), 'editor' => array(
		'edit_tasks' => true,
		'edit_others_tasks' => true,
	    ), 'author' => array(
		'edit_tasks' => true,
		'edit_others_tasks' => false,
	    ), 'subscriber' => array(
		'edit_tasks' => false,
		'edit_others_tasks' => false,
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

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		add_filter( 'pre_get_posts', array( $this, 'filter_search' ) );

		$options = get_option( $this->get_plugin_slug() . '-settings' );
		$options_extra = get_option( $this->get_plugin_slug() . '-settings-extra' );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_vars' ) );
		// Ajax frontend
		require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_AJAX_Task.php' );
		// Search Shortcode
		require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_AJAX_Filter.php' );
		if ( isset( $options[ $this->get_plugin_slug() . '_enable_frontend' ] ) && $options[ $this->get_plugin_slug() . '_enable_frontend' ] === 'on' ) {
			// Frontend login system
			require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Frontend_Login.php' );
		}
		// Frontend Profile page
		require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Frontend_Profile.php' );
		if ( isset( $options_extra[ $this->get_plugin_slug() . '_tweet_comments' ] ) && $options_extra[ $this->get_plugin_slug() . '_tweet_comments' ] === 'on' ) {
			// Comment support for task
			require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Comment.php' );
		}
		// Task integration for template ecc
		require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_Task_Support.php' );
		// Support for API Rest v1
		require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_API_v1.php' );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return self::$plugin_slug;
	}

	/**
	 * Return the plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin name variable.
	 */
	public function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Return the version
	 *
	 * @since    1.0.0
	 *
	 * @return    Version const.
	 */
	public function get_plugin_version() {
		return self::VERSION;
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
		$fields[ 'users_of_task' ] = $prefix . $this->get_plugin_slug() . '_users';
		$fields[ 'tasks_done_of_user' ] = $prefix . $this->get_plugin_slug() . '_tasks_done';
		$fields[ 'tasks_later_of_user' ] = $prefix . $this->get_plugin_slug() . '_tasks_later';
		$fields[ 'task_before' ] = $prefix . $this->get_plugin_slug() . '_before';
		$fields[ 'task_prerequisites' ] = $prefix . $this->get_plugin_slug() . '_prerequisites';
		$fields[ 'task_matters' ] = $prefix . $this->get_plugin_slug() . '_matters';
		$fields[ 'task_steps' ] = $prefix . $this->get_plugin_slug() . '_steps';
		$fields[ 'task_help' ] = $prefix . $this->get_plugin_slug() . '_help';
		$fields[ 'task_completion' ] = $prefix . $this->get_plugin_slug() . '_completion';
		$fields[ 'task_mentor' ] = $prefix . $this->get_plugin_slug() . '_mentor';
		$fields[ 'task_next' ] = $prefix . $this->get_plugin_slug() . '_next';
		$fields[ 'task_subtitle' ] = $prefix . $this->get_plugin_slug() . '_subtitle';
		if ( array_key_exists( $value, $fields ) ) {
			return $fields[ $value ];
		} elseif ( empty( $value ) ) {
			return $fields;
		}
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
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();
				}
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    integer $blog_id ID of the new blog.
	 * 
	 * @return void
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Add support for custom CPT on the search box
	 *
	 * @since    1.0.0
	 *
	 * @param    object $query WP_Query object.
	 * 
	 * @return object WP_Query object with task post type  
	 */
	public function filter_search( $query ) {
		if ( $query->is_search ) {
			// Mantain support for post
			$this->cpts[] = 'task';
			$query->set( 'post_type', $this->cpts );
		}
		return $query;
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		global $wpdb;

		// Get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		global $wp_roles;
		if ( !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles;
		}
		foreach ( $wp_roles->role_names as $role => $label ) {
			// If the role is a standard role, map the default caps, otherwise, map as a subscriber
			$caps = ( array_key_exists( $role, self::$plugin_roles ) ) ? self::$plugin_roles[ $role ] : self::$plugin_roles[ 'subscriber' ];
			// Loop and assign
			foreach ( $caps as $cap => $grant ) {
				// Check to see if the user already has this capability, if so, don't re-add as that would override grant
				if ( !isset( $wp_roles->roles[ $role ][ 'capabilities' ][ $cap ] ) ) {
					$wp_roles->add_cap( $role, $cap, $grant );
				}
			}
		}
		// Clear the permalinks
		flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// Clear the permalinks
		flush_rewrite_rules();
	}

	/**
	 * Load the CPT and Taxonomy
	 *
	 * @since    1.0.0
	 */
	public function load_cpt_taxonomy() {
		$options_extra = get_option( $this->get_plugin_slug() . '-settings-extra' );
		$task_post_type = array(
		    'supports' => array( 'title', 'comments' ),
		    'capabilities' => array(
			'edit_post' => 'edit_tasks',
			'edit_others_posts' => 'edit_others_tasks',
		    ),
		    'map_meta_cap' => true,
		    'menu_icon' => 'dashicons-welcome-add-page',
		);
		if ( isset( $options_extra[ $this->get_plugin_slug() . '_cpt_slug' ] ) && !empty( $options_extra[ $this->get_plugin_slug() . '_cpt_slug' ] ) ) {
			$task_post_type[ 'rewrite' ][ 'slug' ] = $options_extra[ $this->get_plugin_slug() . '_cpt_slug' ];
		}
		register_via_cpt_core(
			array( __( 'Task', $this->get_plugin_slug() ), __( 'Tasks', $this->get_plugin_slug() ), 'task' ), $task_post_type );
		register_via_taxonomy_core(
			array( __( 'Area', $this->get_plugin_slug() ), __( 'Areas', $this->get_plugin_slug() ), 'task-area' ), array(
		    'public' => true,
		    'capabilities' => array(
			'assign_terms' => 'edit_posts',
		    )
			), array( 'task' )
		);

		register_via_taxonomy_core(
			array( __( 'Difficulty', $this->get_plugin_slug() ), __( 'Difficulties', $this->get_plugin_slug() ), 'task-difficulty' ), array(
		    'public' => true,
		    'capabilities' => array(
			'assign_terms' => 'edit_posts',
		    )
			), array( 'task' )
		);

		register_via_taxonomy_core(
			array( __( 'Team', $this->get_plugin_slug() ), __( 'Teams', $this->get_plugin_slug() ), 'task-team' ), array(
		    'public' => true,
		    'capabilities' => array(
			'assign_terms' => 'edit_posts',
		    )
			), array( 'task' )
		);

		register_via_taxonomy_core(
			array( __( 'Estimated minute', $this->get_plugin_slug() ), __( 'Estimated minutes', $this->get_plugin_slug() ), 'task-minute' ), array(
		    'public' => true,
		    'capabilities' => array(
			'assign_terms' => 'edit_posts',
		    )
			), array( 'task' )
		);
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->get_plugin_slug() . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $post;
		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'datask-search' ) ) {
			wp_enqueue_script( $this->get_plugin_slug() . '-filter-plugin-script', plugins_url( 'assets/js/ajax-filter.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		}
		if ( is_user_logged_in() && (is_singular( 'task' ) || get_user_of_profile()) ) {
			wp_enqueue_script( $this->get_plugin_slug() . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		}
	}

	/**
	 * Print the PHP var in the HTML of the frontend for access by JavaScript
	 *
	 * @since    1.0.0
	 */
	public function enqueue_js_vars() {
		global $post;
		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'datask-search' ) ) {
			wp_localize_script( $this->get_plugin_slug() . '-filter-plugin-script', 'dt_js_search_vars', array(
			    'ajaxurl' => admin_url( 'admin-ajax.php' ),
			    'on_load_text' => __( 'Search to see results', $this->get_plugin_slug() ),
			    'thisPage' => 1,
			    'nonce' => esc_js( wp_create_nonce( 'filternonce' ) )
				)
			);
		}
		if ( is_user_logged_in() && (is_singular( 'task' ) || get_user_of_profile()) ) {
			wp_localize_script( $this->get_plugin_slug() . '-plugin-script', 'dt_js_vars', array(
			    'ajaxurl' => admin_url( 'admin-ajax.php' )
				)
			);
		}
	}

}
