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
		require_once( plugin_dir_path( __FILE__ ) . 'includes/fake-page.php' );

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
		//Frontend login system
		/*
		 * Load Fake Page class
		 */
		new Fake_Page(
			array(
		    'slug' => 'login',
		    'post_title' => __( 'Login', $this->get_plugin_slug() ),
		    'post_content' => 'content'
			)
		);
		new Fake_Page(
			array(
		    'slug' => 'logout',
		    'post_title' => __( 'Logout', $this->get_plugin_slug() ),
		    'post_content' => 'content'
			)
		);
		add_action( 'login_init', array( $this, 'frontend_login' ) );
		add_action( 'template_redirect', array( $this, 'frontend_login_redirect' ) );
		add_action( 'admin_init', array( $this, 'prevent_access_backend' ) );
		add_filter( 'registration_errors', array( $this, 'registration_redirect' ), 10, 3 );
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
		add_action( 'lostpassword_post', array( $this, 'frontend_reset_password' ) );
		add_action( 'validate_password_reset', array( $this, 'frontend_validate_password_reset', 10, 2 ) );
		add_filter( 'the_content', array( $this, 'wo_login_page' ) );
		add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_vars' ) );
		//Ajax frontend
		require_once( plugin_dir_path( __FILE__ ) . '/includes/ajax.php' );

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

		require_once( plugin_dir_path( __FILE__ ) . '/includes/WO_AJAX_Filter.php' );
		$wo_ajax_filter = new WO_AJAX_Filter();
		add_action( 'wp_ajax_wpoad-ajax-search', array( $wo_ajax_filter, 'create_filtered_section' ) );
		add_action( 'wp_ajax_nopriv_wpoad-ajax-search', array( $wo_ajax_filter, 'create_filtered_section' ) );
		add_shortcode( 'ajaxFilter', array( $this, 'ajax_filter' ) );
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
		wp_enqueue_script( $this->get_plugin_slug() . '-filter-plugin-script', plugins_url( 'assets/js/ajax-filter.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		if ( is_singular( 'task' ) ) {
			wp_enqueue_script( $this->get_plugin_slug() . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		}
	}

	/**
	 * Print the PHP var in the HTML of the frontend for access by JavaScript
	 *
	 * @since    1.0.0
	 */
	public function enqueue_js_vars() {
		//if ( is_singular( 'task' ) ) {
		wp_localize_script( $this->get_plugin_slug() . '-filter-plugin-script', 'wo_js_vars', array(
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'site_name' => get_bloginfo( "sitename" ),
		    'search' => __( 'Search', $this->get_plugin_slug() ),
		    'search_results_for' => __( 'Search Results For', $this->get_plugin_slug() ),
		    'on_load_text' => __( 'Search & filter to see results', $this->get_plugin_slug() ),
		    'thisPage' => 1,
		    'nonce'=> esc_js( wp_create_nonce( 'filternonce' ) )
			)
		);
		//}
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
		} elseif ( isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member' ) {
			if ( is_user_logged_in() ) {
				wo_get_template_part( 'user', 'profile', true );
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
		} elseif ( isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member' ) {
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
		if ( isset( $wp_query->query[ 'pagename' ] ) && $wp_query->query[ 'pagename' ] === 'member' ) {
			return __( 'Your profile', $this->get_plugin_slug() );
		} else {
			return $title;
		}
	}

	/**
	 * Frontend login
	 *
	 * @since    1.0.0
	 */
	public function frontend_login() {
		$action = isset( $_REQUEST[ 'action' ] ) ? $_REQUEST[ 'action' ] : 'login';
		if ( isset( $_POST[ 'wp-submit' ] ) ) {
			$action = 'post-data';
		} else if ( isset( $_GET[ 'reauth' ] ) ) {
			$action = 'reauth';
		}
		// redirect to change password form
		if ( $action == 'rp' || $action == 'resetpass' ) {
			if ( isset( $_GET[ 'key' ] ) && isset( $_GET[ 'login' ] ) ) {
				$rp_path = wp_unslash( '/login/' );
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				$value = sprintf( '%s:%s', wp_unslash( $_GET[ 'login' ] ), wp_unslash( $_GET[ 'key' ] ) );
				setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
			}

			wp_redirect( home_url( '/login/?action=resetpass' ) );
			exit;
		}
		// redirect from wrong key when resetting password
		if ( $action == 'lostpassword' && isset( $_GET[ 'error' ] ) && ( $_GET[ 'error' ] == 'expiredkey' || $_GET[ 'error' ] == 'invalidkey' ) ) {
			wp_redirect( home_url( '/login/?action=forgot&failed=wrongkey' ) );
			exit;
		}
		if (
			$action == 'post-data' || // don't mess with POST requests
			$action == 'reauth' || // need to reauthorize
			$action == 'logout'      // user is logging out
		) {
			return;
		}
		wp_redirect( home_url( '/login/' ) );
		exit;
	}

	/**
	 * Frontend redirect when logged/not logged
	 *
	 * @since    1.0.0
	 */
	public function frontend_login_redirect() {
		if ( is_page( 'login' ) && is_user_logged_in() ) {
			wp_redirect( home_url( '/member/' ) );
			exit();
		} elseif ( is_page( 'logout' ) && is_user_logged_in() ) {
			wp_logout();
			wp_redirect( home_url() );
			exit();
		}
		global $wp_query;
		if ( array_key_exists( 'member', $wp_query->query_vars ) && !is_user_logged_in() ) {
			wp_redirect( home_url( '/login/' ) );
			exit();
		}
	}

	/**
	 *  Prevent access in administration for not admin user
	 *
	 * @since    1.0.0
	 */
	public function prevent_access_backend() {
		if ( current_user_can( 'subscriber' ) && !defined( 'DOING_AJAX' ) ) {
			wp_redirect( home_url( '/member/' ) );
			exit;
		}
	}

	/**
	 *  Redirect on registration
	 *
	 * @since    1.0.0
	 */
	public function registration_redirect( $errors, $sanitized_user_login, $user_email ) {
		// don't lose your time with spammers, redirect them to a success page
		if ( !isset( $_POST[ 'confirm_email' ] ) || $_POST[ 'confirm_email' ] !== '' ) {
			wp_redirect( home_url( '/login/' ) . '?action=register&success=1' );
			exit;
		}
		if ( !empty( $errors->errors ) ) {
			if ( isset( $errors->errors[ 'username_exists' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=username_exists' );
			} else if ( isset( $errors->errors[ 'email_exists' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=email_exists' );
			} else if ( isset( $errors->errors[ 'invalid_username' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=invalid_username' );
			} else if ( isset( $errors->errors[ 'invalid_email' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=invalid_email' );
			} else if ( isset( $errors->errors[ 'empty_username' ] ) || isset( $errors->errors[ 'empty_email' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=empty' );
			} else {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=generic' );
			}
			exit;
		}
		return $errors;
	}

	/**
	 * Redirect after login
	 *
	 * @since    1.0.0
	 */
	public function login_redirect( $redirect_to, $url, $user ) {
		if ( !isset( $user->errors ) ) {
			return $redirect_to;
		}
		wp_redirect( home_url( '/login/' ) . '?action=login&failed=1' );
		exit;
	}

	/**
	 * Reset password in the frontend
	 *
	 * @since    1.0.0
	 */
	public function frontend_reset_password() {
		$user_data = '';
		if ( !empty( $_POST[ 'user_login' ] ) ) {
			if ( strpos( $_POST[ 'user_login' ], '@' ) ) {
				$user_data = get_user_by( 'email', trim( $_POST[ 'user_login' ] ) );
			} else {
				$user_data = get_user_by( 'login', trim( $_POST[ 'user_login' ] ) );
			}
		}
		if ( empty( $user_data ) ) {
			wp_redirect( home_url( '/login/' ) . '?action=forgot&failed=1' );
			exit;
		}
	}

	/**
	 * Validate the password in frontend
	 *
	 * @since    1.0.0
	 */
	public function frontend_validate_password_reset( $errors, $user ) {
		// passwords don't match
		if ( $errors->get_error_code() ) {
			wp_redirect( home_url( '/login/?action=resetpass&failed=nomatch' ) );
			exit;
		}
		// wp-login already checked if the password is valid, so no further check is needed
		if ( !empty( $_POST[ 'pass1' ] ) ) {
			reset_password( $user, $_POST[ 'pass1' ] );
			wp_redirect( home_url( '/login/?action=resetpass&success=1' ) );
			exit;
		}
		// redirect to change password form
		wp_redirect( home_url( '/login/?action=resetpass' ) );
		exit;
	}

	/**
	 * Load login page
	 *
	 * @since    1.0.0
	 */
	public function wo_login_page( $content ) {
		if ( is_page( 'login' ) ) {
			wo_get_template_part( 'log', 'in', true );
		} else {
			return $content;
		}
	}

	/**
	 * hide the admin bar in frontend for not admin user
	 *
	 * @since    1.0.0
	 */
	public function remove_admin_bar() {
		if ( !current_user_can( 'administrator' ) && !is_admin() ) {
			show_admin_bar( false );
		}
	}

	/**
	 * Echo the data about the task
	 *
	 * @since    1.0.0
	 */
	public function wo_task_info() {
		echo '<ul class="list list-inset">';
		echo '<li><b>';
		_e( 'Team: ', 'oneanddone' );
		echo '</b>';
		$team = get_the_terms( get_the_ID(), 'task-team' );
		foreach ( $team as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-team' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li><li><b>';
		_e( 'Project: ', 'oneanddone' );
		echo '</b>';
		$project = get_the_terms( get_the_ID(), 'task-area' );
		foreach ( $project as $term ) {
			echo '<a href="' . get_term_link( $term->slug, 'task-area' ) . '">' . $term->name . '</a>, ';
		}
		echo '</li><li><b>';
		_e( 'Estimated time: ', 'oneanddone' );
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
			$prerequisites = get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_prerequisites', true );
			if ( !empty( $prerequisites ) ) {
				$content = '<h2 class="alert alert-success">' . __( 'Prerequisites', $this->get_plugin_slug() ) . '</h2>';
				$content .= $prerequisites;
			}
			$matters = get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_matters', true );
			if ( !empty( $matters ) ) {
				$content = '<h2 class="alert alert-success">' . __( 'Why this matters', $this->get_plugin_slug() ) . '</h2>';
				$content .= $matters;
			}
			$steps = get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_steps', true );
			if ( !empty( $steps ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Steps', $this->get_plugin_slug() ) . '</h2>';
				$content .= $steps;
			}
			$help = get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_help', true );
			if ( !empty( $help ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Need Help?', $this->get_plugin_slug() ) . '</h2>';
				$content .= $help;
				$content .= '<br><br>';
			}
			$completion = get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_completion', true );
			if ( !empty( $completion ) ) {
				$content .= '<h2 class="alert alert-success">' . __( 'Completion', $this->get_plugin_slug() ) . '</h2>';
				$content .= $completion;
				$content .= '<br><br>';
			}
			$mentor = get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_mentor', true );
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
			$nexts = get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_next', true );
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
			$users = unserialize( get_post_meta( get_the_ID(), '_task_' . $this->get_plugin_slug() . '_users', true ) );
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
				$tweet = __( 'URL of the Tweet', $this->get_plugin_slug() ) . '<a href="' . esc_attr( $title ) . '">' . esc_attr( $title ) . '</a>';
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
		$current_user = wp_get_current_user();
		get_tasks_later( $current_user->user_login );
	}

	/**
	 * The is the method that is used by the shortcode
	 * 
	 * @param       array   $atts
	 * @since       1.5
	 * @return      HTML
	 */
	function ajax_filter( $atts ) {
		$show_count = isset( $atts[ 'show_count' ] ) && $atts[ 'show_count' ] == 1 ? 1 : 0;
		$posts_per_page = isset( $atts[ 'posts_per_page' ] ) ? ( int ) $atts[ 'posts_per_page' ] : 10;
		$filter_type = isset( $atts[ 'filter_type' ] ) && !empty( $atts[ 'filter_type' ] ) ? $atts[ 'filter_type' ] : 'select';
		$wo_ajax_filter = new WO_AJAX_Filter();
		$wo_ajax_filter->create_filter_nav( $filter_type, $show_count );
		?>  
		<div id="ajax-content" class="r-content-wide">
		    <section id="ajax-filtered-section" data-postsperpage="<?php echo $posts_per_page ?>">
			<?php
			$wo_ajax_filter->create_filtered_section( $posts_per_page );
			?>
		    </section>
		</div>
		<?php
	}

}
