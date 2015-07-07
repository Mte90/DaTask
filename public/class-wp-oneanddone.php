<?php
/**
 * WP-OneAndDone.
 *
 * @package   Wp_Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
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
		    'supports' => array( 'title', 'comments' ),
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

		add_filter( 'body_class', array( $this, 'add_wo_class' ), 10, 3 );

		//Function of plugin
		require_once( plugin_dir_path( __FILE__ ) . '/includes/functions.php' );
		require_once( plugin_dir_path( __FILE__ ) . '/includes/fake-page.php' );

		//Override the template hierarchy
		add_filter( 'template_include', array( $this, 'load_content_task' ) );
		//Member page
		new Fake_Page(
			array(
		    'slug' => 'member',
		    'post_title' => __( 'Your profile', $this->get_plugin_slug() ),
		    'post_content' => 'content'
			)
		);
		add_filter( 'query_vars', array( $this, 'add_member_permalink' ) );
		add_filter( 'init', array( $this, 'rewrite_rule' ) );
		add_action( 'template_redirect', array( $this, 'userprofile_template' ) );
		add_filter( 'wp_title', array( $this, 'member_wp_title' ), 10, 3 );
		add_filter( 'the_title', array( $this, 'member_title' ), 10, 2 );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_vars' ) );
		//Ajax frontend
		require_once( plugin_dir_path( __FILE__ ) . '/includes/WO_AJAX_Task.php' );
		//Search Shortcode
		require_once( plugin_dir_path( __FILE__ ) . '/includes/WO_AJAX_Filter.php' );
		//Frontend login system
		require_once( plugin_dir_path( __FILE__ ) . '/includes/WO_Frontend_Login.php' );

		/*
		 * Custom Action/Shortcode
		 */
		add_action( 'wo-task-info', array( $this, 'wo_task_info' ) );
		add_filter( 'the_content', array( $this, 'wo_task_content' ) );
		add_filter( 'the_excerpt', array( $this, 'wo_task_excerpt' ) );
		add_action( 'comment_form_logged_in_after', array( $this, 'task_comment_fields' ) );
		add_action( 'comment_form_after_fields', array( $this, 'task_comment_fields' ) );
		add_action( 'comment_post', array( $this, 'task_comment_save_data' ) );
		add_filter( 'comment_text', array( $this, 'task_comment_show_data_frontend' ), 99, 2 );
		add_action( 'add_meta_boxes_comment', array( $this, 'task_comment_show_metabox_data_backend' ) );
		add_shortcode( 'oneanddone-progress', array( $this, 'oneanddone_progress' ) );
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
	 * @return    array/string of fields
	 */
	public function get_fields( $value = '' ) {
		$fields = array();
		$prefix = '_task_';
		$fields[ 'users_of_task' ] = $prefix . $this->get_plugin_slug() . '_users';
		$fields[ 'tasks_done_of_user' ] = $prefix . $this->get_plugin_slug() . '_tasks_done';
		$fields[ 'tasks_later_of_user' ] = $prefix . $this->get_plugin_slug() . '_tasks_later';
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
			array( 'Mozilla Persona (BrowserID)', 'browserid/browserid.php' )
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
		global $post;
		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'oneanddone-search' ) ) {
			wp_enqueue_script( $this->get_plugin_slug() . '-filter-plugin-script', plugins_url( 'assets/js/ajax-filter.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		}
		if ( is_user_logged_in() && is_singular( 'task' ) ) {
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
		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'oneanddone-search' ) ) {
			wp_localize_script( $this->get_plugin_slug() . '-filter-plugin-script', 'wo_js_search_vars', array(
			    'ajaxurl' => admin_url( 'admin-ajax.php' ),
			    'on_load_text' => __( 'Search to see results', $this->get_plugin_slug() ),
			    'thisPage' => 1,
			    'nonce' => esc_js( wp_create_nonce( 'filternonce' ) )
				)
			);
		}
		if ( is_user_logged_in() && is_singular( 'task' ) ) {
			wp_localize_script( $this->get_plugin_slug() . '-plugin-script', 'wo_js_vars', array(
			    'ajaxurl' => admin_url( 'admin-ajax.php' )
				)
			);
		}
	}

	/**
	 * Add class in the body on the frontend
	 *
	 * @since    1.0.0
	 */
	public function add_wo_class( $classes ) {
		global $post;
		if ( is_singular( 'task' ) ) {
			$classes[] = $this->get_plugin_slug() . '-task';
		} elseif ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'oneanddone-search' ) ) {
			$classes[] = $this->get_plugin_slug() . '-search';
		}
		return $classes;
	}

	/**
	 * Example for override the template system on the frontend
	 *
	 * @since    1.0.0
	 */
	public function load_content_task( $original_template ) {
		if ( is_singular( 'task' ) ) {
			return wo_get_template_part( 'single', 'task', false );
		} else {
			return $original_template;
		}
	}

	/**
	 * Add the rewrite permalink for member
	 *
	 * @since    1.0.0
	 */
	public function add_member_permalink( $vars ) {
		$vars[] = 'member';
		return $vars;
	}

	/**
	 * Add the rewrite permalink for member
	 *
	 * @since    1.0.0
	 */
	public function rewrite_rule() {
		add_rewrite_tag( '%member%', '([^&]+)' );
		add_rewrite_rule(
			'^member/([^/]*)/?', 'index.php?member=$matches[1]', 'top'
		);
	}

	/**
	 * Include the template for the profile page
	 *
	 * @since    1.0.0
	 */
	public function userprofile_template() {
		global $wp_query;
		if ( array_key_exists( 'member', $wp_query->query_vars ) ) {
			if ( get_user_of_profile() !== NULL ) {
				wo_get_template_part( 'user', 'profile', true );
				exit;
			} else {
				$wp_query->set_404();
			}
		} elseif ( (isset( $wp_query->query[ 'name' ] ) && $wp_query->query[ 'name' ] === 'member') || (isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member') ) {
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				wp_redirect( home_url( '/member/' . $current_user->user_login ) );
				exit;
			} else {
				wp_redirect( home_url( '/login/' ) );
			}
		}
	}

	/**
	 * Add the head title for the member page
	 *
	 * @since    1.0.0
	 */
	public function member_wp_title( $title, $sep, $seplocation ) {
		global $wp_query;
		if ( array_key_exists( 'member', $wp_query->query_vars ) ) {
			if ( get_user_of_profile() !== NULL ) {
				$page = sprintf( __( "%s's Profile", $this->get_plugin_slug() ), get_user_of_profile() );

				return $page . ' ' . $sep . $title;
			}
		} elseif ( (isset( $wp_query->query[ 'name' ] ) && $wp_query->query[ 'name' ] === 'member') || (isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member') ) {
			return __( 'Your profile', $this->get_plugin_slug() ) . ' ' . $sep;
		} else {
			return $title;
		}
	}

	/**
	 * Add the title for the member page
	 *
	 * @since    1.0.0
	 */
	public function member_title( $title, $id ) {
		global $wp_query;
		if ( (isset( $wp_query->query[ 'name' ] ) && $wp_query->query[ 'name' ] === 'member') || (isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member') ) {
			return __( 'Your profile', $this->get_plugin_slug() );
		} else {
			return $title;
		}
	}

	/**
	 * Echo the data about the task
	 *
	 * @since    1.0.0
	 */
	public function wo_task_info() {
		echo '<div class="alert alert-warning">' . __( 'Last edit: ', $this->get_plugin_slug() ) . get_the_modified_date() . '</div>';
		echo '<ul class="list list-inset">';
		echo '<li><b>';
		_e( 'Team: ', $this->get_plugin_slug() );
		echo '</b>';
		$team = get_the_terms( get_the_ID(), 'task-team' );
		foreach ( $team as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-team' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li><li><b>';
		_e( 'Project: ', $this->get_plugin_slug() );
		echo '</b>';
		$project = get_the_terms( get_the_ID(), 'task-area' );
		foreach ( $project as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-area' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li><li><b>';
		_e( 'Estimated time: ', $this->get_plugin_slug() );
		echo '</b>';
		$minute = get_the_terms( get_the_ID(), 'task-minute' );
		foreach ( $minute as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-minute' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li>';
		echo '</ul>';
	}

	/**
	 * Echo the content of the task
	 *
	 * @since    1.0.0
	 */
	public function wo_task_content( $content ) {
		global $post;
		if ( get_post_type( $post->ID ) === 'task' ) {
			$content = the_task_subtitle( false );
		}
		if ( is_singular( 'task' ) ) {
			$prerequisites = get_post_meta( get_the_ID(), $this->get_fields( 'task_prerequisites' ), true );
			if ( !empty( $prerequisites ) ) {
				$content = '<h2 class="alert alert-success">' . __( 'Prerequisites', $this->get_plugin_slug() ) . '</h2>';
				$content .= $prerequisites;
			}
			$matters = get_post_meta( get_the_ID(), $this->get_fields( 'task_matters' ), true );
			if ( !empty( $matters ) ) {
				$content = '<h2 class="alert alert-success">' . __( 'Why this matters', $this->get_plugin_slug() ) . '</h2>';
				$content .= $matters;
			}
			$steps = get_post_meta( get_the_ID(), $this->get_fields( 'task_steps' ), true );
			if ( !empty( $steps ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Steps', $this->get_plugin_slug() ) . '</h2>';
				$content .= $steps;
			}
			$help = get_post_meta( get_the_ID(), $this->get_fields( 'task_help' ), true );
			if ( !empty( $help ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Need Help?', $this->get_plugin_slug() ) . '</h2>';
				$content .= $help;
				$content .= '<br><br>';
			}
			$completion = get_post_meta( get_the_ID(), $this->get_fields( 'task_completion' ), true );
			if ( !empty( $completion ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Completion', $this->get_plugin_slug() ) . '</h2>';
				$content .= $completion;
				$content .= '<br><br>';
			}
			$mentor = get_post_meta( get_the_ID(), $this->get_fields( 'task_mentor' ), true );
			if ( !empty( $mentor ) ) {
				$content .= '<div class="panel panel-warning">';
				$content .= '<div class="panel-heading">';
				$content .= __( 'Mentor(s): ', $this->get_plugin_slug() );
				$content .= '</div>';
				$content .= '<div class="panel-content">';
				$content .= $mentor;
				$content .= '</div>';
				$content .= '</div>';
			}
			$nexts = get_post_meta( get_the_ID(), $this->get_fields( 'task_next' ), true );
			if ( !empty( $nexts ) ) {
				$content .= '<div class="panel panel-danger">';
				$content .= '<div class="panel-heading">';
				$content .= __( 'Good next tasks: ', $this->get_plugin_slug() );
				$content .= '</div>';
				$content .= '<div class="panel-content">';
				$next_task = '';
				$nexts_split = explode( ',', str_replace( ' ', '', $nexts ) );
				$nexts_ids = new WP_Query( array(
				    'post_type' => 'task',
				    'post__in' => $nexts_split ) );
				foreach ( $nexts_ids->posts as $post ) {
					$next_task .= '<a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a>, ';
				}
				wp_reset_postdata();
				$content .= $next_task;
				$content .= '</div>';
				$content .= '</div>';
			}
			$users = unserialize( get_post_meta( get_the_ID(), $this->get_fields( 'users_of_task' ), true ) );
			if ( is_array( $users ) ) {
				$content .= '<h2>' . __( 'List of users who completed this task', $this->get_plugin_slug() ) . '</h2>';
				$content .= '<div class="panel panel-default">';
				$content .= '<div class="panel-content">';
				foreach ( $users as $user => $value ) {
					$content .= '<a href="' . get_home_url() . '/member/' . get_the_author_meta( 'user_login', $user ) . '">' . get_the_author_meta( 'display_name', $user ) . '</a>, ';
				}
				$content .= '</div>';
				$content .= '</div>';
			}
			$content .= '<br><br>';
		}
		return $content;
	}

	/**
	 * Echo the excerpt of the task
	 *
	 * @since    1.0.0
	 */
	public function wo_task_excerpt( $content ) {
		global $post;
		if ( get_post_type( $post->ID ) === 'task' ) {
			$content = the_task_subtitle( false );
		}
		return $content;
	}

	/**
	 * @since    1.0.0
	 */
	public function task_comment_fields() {
		global $post;
		if ( get_post_type( $post->ID ) === 'task' ) {
			?>
			<div class="form-group comment-form-tweet">
			    <label for="tweet_url"><?php _e( 'Insert URL of the Tweet', $this->get_plugin_slug() ); ?></label>
			    <input type="text" name="tweet_url" id="tweet_url" class="form-control" />
			    <a href="https://twitter.com/share" class="twitter-share-button" data-via="Mte90net" data-hashtags="oneanddone">Tweet</a>
			    <script>!function(d, s, id){var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location)?'http':'https'; if (!d.getElementById(id)){js = d.createElement(s); js.id = id; js.src = p + '://platform.twitter.com/widgets.js'; fjs.parentNode.insertBefore(js, fjs); }}(document, 'script', 'twitter-wjs');</script>
			</div>
			<?php
		}
	}

	/**
	 * @since    1.0.0
	 */
	public function task_comment_save_data( $comment_id ) {
		global $post;
		if ( get_post_type( $post->ID ) === 'task' ) {
			add_comment_meta( $comment_id, 'tweet_url', esc_html( $_POST[ 'tweet_url' ] ) );
		}
	}

	/**
	 * @since    1.0.0
	 */
	public function task_comment_show_data_frontend( $text, $comment ) {
		if ( get_post_type( $comment->comment_post_ID ) === 'task' ) {
			$tweet = get_comment_meta( $comment->comment_ID, 'tweet_url', true );
			if ( $tweet ) {
				$tweet = __( 'URL of the Tweet', $this->get_plugin_slug() ) . ': <a href="' . esc_attr( $tweet ) . '">' . esc_attr( $tweet ) . '</a>';
				$text = $tweet . $text;
			}
		}
		return $text;
	}

	/**
	 * @since    1.0.0
	 */
	public function task_comment_show_metabox_data_backend() {
		add_meta_box( 'task-comment', __( 'Task Feedback Data' ), array( $this, 'task_comment_show_field_data_backend' ), 'comment', 'normal', 'high' );
	}

	/**
	 * @since    1.0.0
	 */
	public function task_comment_show_field_data_backend( $comment ) {
		if ( get_post_type( $comment->comment_post_ID ) === 'task' ) {
			$tweet = get_comment_meta( $comment->comment_ID, 'tweet_url', true );
			wp_nonce_field( 'task_comment_nonce ', 'task_comment_nonce ', false );
			?>
			<p>
			    <label for="tweet_url"><?php _e( 'URL of the Tweet', $this->get_plugin_slug() ); ?></label>
			    <input type="text" name="tweet_url" value="<?php echo esc_attr( $tweet ); ?>" class="widefat" />
			</p>
			<?php
		}
	}

	/**
	 * @since    1.0.0
	 */
	public function oneanddone_progress() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			wo_tasks_later( $current_user->user_login );
		}
	}

}
