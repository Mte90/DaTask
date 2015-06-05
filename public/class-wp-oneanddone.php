<?php

/**
 * WP-OneAndDone.
 *
 * @package   Wp_Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2014 GPL
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-wp-oneanddone-admin.php`
 *
 * @package Wp_Oneanddone
 * @author  Mte90 <mte90net@gmail.com>
 */
class Wp_Oneanddone {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * @TODO - Rename "wp-oneanddone" to the name of your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_slug = 'wp-oneanddone';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_name = 'WP-OneAndDone';

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
	protected $cpts = array( 'task', 'task-done' );

	/**
	 * Array of capabilities by roles
	 * 
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected static $plugin_roles = array(
		'editor' => array(
			'edit_tasks' => true,
			'edit_others_tasks' => true,
		),
		'author' => array(
			'edit_tasks' => true,
			'edit_others_tasks' => false,
		),
		'subscriber' => array(
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
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		register_via_cpt_core(
				array( __( 'Task', $this->get_plugin_slug() ), __( 'Tasks', $this->get_plugin_slug() ), 'task' ), array(
			'taxonomies' => array( 'task-projects' ),
			'capabilities' => array(
				'edit_post' => 'edit_tasks',
				'edit_others_posts' => 'edit_other_tasks',
			),
			'map_meta_cap' => true
				)
		);
		
		register_via_cpt_core(
				array( __( 'Task Done', $this->get_plugin_slug() ), __( 'Tasks Done', $this->get_plugin_slug() ), 'task-done' ), array(
			'taxonomies' => array( 'task-done-projects' ),
			'capabilities' => array(
				'edit_post' => 'edit_tasks',
				'edit_others_posts' => 'edit_other_tasks',
			),
			'map_meta_cap' => true
				)
		);

		add_filter( 'pre_get_posts', array( $this, 'filter_search' ) );

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
		
		register_via_taxonomy_core(
				array( __( 'Project Done', $this->get_plugin_slug() ), __( 'Projects Done', $this->get_plugin_slug() ), 'task-done-projects' ), array(
			'public' => true,
			'capabilities' => array(
				'assign_terms' => 'edit_posts',
			)
				), array( 'task-done' )
		);

		add_filter( 'body_class', array( $this, 'add_wo_class' ), 10, 3 );

		//Override the template hierarchy for load /templates/content-demo.php
		add_filter( 'template_include', array( $this, 'load_content_task' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/*
		 * Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );
		add_shortcode( 'oneanddone-todo', array( $this, 'oneanddone_todo' ) );
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
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
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
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
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
	 * @param    int    $blog_id    ID of the new blog.
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
	 * @param    object    $query   
	 */
	public function filter_search( $query ) {
		if ( $query->is_search ) {
			//Mantain support for post
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

		// get an array of blog ids
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
		//Requirements Detection System - read the doc/example in the library file
		require_once( plugin_dir_path( __FILE__ ) . 'includes/requirements.php' );
		new Plugin_Requirements( self::$plugin_name, self::$plugin_slug, array(
			'WP' => new WordPress_Requirement( '4.1.0' ),
			'Plugin' => new Plugin_Requirement( array(
				array( 'Theme My Login', 'theme-my-login/theme-my-login.php' ),
				array( 'Mozilla Persona (BrowserID)', 'browserid/browserid.php' ),
				array( 'Search & Filter via AJAX', 'q-ajax-filter/q-ajax-filter.php' )
					) )
				) );
		
		global $wp_roles;
		if ( !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles;
		}
		foreach ( $wp_roles->role_names as $role => $label ) {
			//if the role is a standard role, map the default caps, otherwise, map as a subscriber
			$caps = ( array_key_exists( $role, self::$plugin_roles ) ) ? self::$plugin_roles[ $role ] : self::$plugin_roles[ 'subscriber' ];
			//loop and assign
			foreach ( $caps as $cap => $grant ) {
				//check to see if the user already has this capability, if so, don't re-add as that would override grant
				if ( !isset( $wp_roles->roles[ $role ][ 'capabilities' ][ $cap ] ) ) {
					$wp_roles->add_cap( $role, $cap, $grant );
				}
			}
		}
		//Clear the permalinks
		flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		//Clear the permalinks
		flush_rewrite_rules();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->get_plugin_slug();
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
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
		wp_enqueue_script( $this->get_plugin_slug() . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * Add class in the body on the frontend
	 *
	 * @since    1.0.0
	 */
	public function add_wo_class( $classes ) {
		$classes[] = $this->get_plugin_slug();
		return $classes;
	}

	/**
	 * Example for override the template system on the frontend
	 *
	 * @since    1.0.0
	 */
	public function load_content_task( $original_template ) {
		if ( is_singular( 'task' ) && in_the_loop() ) {
			return wo_get_template_part( 'content', 'task', false );
		} else {
			return $original_template;
		}
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

	/**
	 * @since    1.0.0
	 */
	public function oneanddone_todo() {
		
	}

}
