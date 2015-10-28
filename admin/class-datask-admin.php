<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-wp-datask.php`
 *
 * @package   DaTask_Admin
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2014 GPL
 */
class DaTask_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = DaTask::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->plugin_name = $plugin->get_plugin_name();
		$this->version = $plugin->get_plugin_version();
		$this->cpts = $plugin->get_cpts();

		// Load admin JavaScript after jQuery loading
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_files' ) );

		require_once( plugin_dir_path( __FILE__ ) . '/includes/WP-Admin-Notice/WP_Admin_Notice.php' );
		// Reset User Task 
		require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_User_Backend.php' );

		// At Glance Dashboard widget for your cpts
		add_filter( 'dashboard_glance_items', array( $this, 'cpt_glance_dashboard_support' ), 10, 1 );
		// Activity Dashboard widget for your cpts
		add_filter( 'dashboard_recent_posts_query_args', array( $this, 'cpt_activity_dashboard_support' ), 10, 1 );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * CMB 2 for metabox and many other cool things!
		 * https://github.com/WebDevStudios/CMB2
		 */

		require_once( plugin_dir_path( __FILE__ ) . '/includes/CMB2/init.php' );
		require_once( plugin_dir_path( __FILE__ ) . '/includes/cmb2_post_search_field.php' );
		require_once( plugin_dir_path( __FILE__ ) . '/includes/cmb2_user_search_field.php' );

		/*
		 * Add metabox
		 */

		add_action( 'cmb2_init', array( $this, 'cmb_task_metaboxes' ) );

		require_once( plugin_dir_path( __FILE__ ) . '/includes/impexp.php' );

		/*
		 * Load CPT_Columns
		 * 
		 * Check the file for example
		 */

		require_once( plugin_dir_path( __FILE__ ) . 'includes/CPT_Columns.php' );
		$post_columns = new CPT_columns( 'task' );
		$post_columns->add_column( 'Done', array(
		    'label' => __( 'Done', $this->plugin_slug ),
		    'type' => 'custom_value',
		    'callback' => array( $this, 'number_of_done' ),
		    'sortable' => true,
		    'prefix' => "<b>",
		    'suffix' => "</b>",
		    'def' => "0",
		    'order' => "-1",
		    'meta_key' => '_task_' . $this->plugin_slug . '_counter'
			)
		);
		$post_columns->add_column( 'Author', array(
		    'label' => __( 'Author', $this->plugin_slug ),
		    'type' => 'custom_value',
		    'callback' => array( $this, 'author_of_task' ),
		    'sortable' => true,
		    'prefix' => "<b>",
		    'suffix' => "</b>",
		    'order' => "-1",
		    'meta_key' => 'post_author'
			)
		);
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
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    void    Return early if no settings page is registered.
	 */
	public function enqueue_admin_files() {
		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id || strpos( $_SERVER[ 'REQUEST_URI' ], 'index.php' ) || strpos( $_SERVER[ 'REQUEST_URI' ], get_bloginfo( 'wpurl' ) . '/wp-admin/' ) ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array( 'dashicons' ), DaTask::VERSION );
		}
		if ( 'task' === $screen->id && ($screen->action === 'add' || $_GET[ 'action' ] === 'edit' ) ) {
			wp_enqueue_script( $this->plugin_slug . '-task-admin-script', plugins_url( 'assets/js/task.js', __FILE__ ), array( 'jquery' ), DaTask::VERSION );
			wp_localize_script( $this->plugin_slug . '-task-admin-script', 'dt_js_admin_vars', array(
			    'alert' => __( 'You have not selected a taxonomy!', $this->plugin_slug ),
				)
			);
		}
		if ( $this->plugin_screen_hook_suffix === $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), DaTask::VERSION );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Settings page in the menu
		 */

		$this->plugin_screen_hook_suffix = add_menu_page( $this->plugin_name, $this->plugin_name, 'manage_options', $this->plugin_slug, array( $this, 'display_plugin_admin_page' ), 'dashicons-hammer', 90 );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 * 
	 * @param array $links The links of the menu.
	 * 
	 * @return array The links of the menu
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
		    'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings' ) . '</a>',
		    'donate' => '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=danielemte90@alice.it&item_name=Donation">' . __( 'Donate', $this->plugin_slug ) . '</a>'
			), $links
		);
	}

	/**
	 * Add the counter of your CPTs in At Glance widget in the dashboard<br>
	 * NOTE: add in $post_types your cpts, remember to edit the css style (admin/assets/css/admin.css) for change the dashicon<br>
	 *
	 *        Reference:  http://wpsnipp.com/index.php/functions-php/wordpress-post-types-dashboard-at-glance-widget/
	 *
	 * @since    1.0.0
	 * 
	 * @param array $items HTML code for your CPTs.
	 * 
	 * @return array HTML
	 */
	public function cpt_glance_dashboard_support( $items = array() ) {
		$post_types = $this->cpts;
		foreach ( $post_types as $type ) {
			if ( !post_type_exists( $type ) ) {
				continue;
			}
			$num_posts = wp_count_posts( $type );
			if ( $num_posts ) {
				$published = intval( $num_posts->publish );
				$post_type = get_post_type_object( $type );
				$text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, $this->plugin_slug );
				$text = sprintf( $text, number_format_i18n( $published ) );
				if ( current_user_can( $post_type->cap->edit_posts ) ) {
					$items[] = '<a class="' . $post_type->name . '-count" href="edit.php?post_type=' . $post_type->name . '">' . sprintf( '%2$s', $type, $text ) . "</a>\n";
				} else {
					$items[] = sprintf( '%2$s', $type, $text ) . "\n";
				}
			}
		}
		return $items;
	}

	/**
	 * Add the recents post type in the activity widget<br>
	 * NOTE: add in $post_types your cpts
	 *
	 * @since    1.0.0
	 */
	function cpt_activity_dashboard_support( $query_args ) {
		if ( !is_array( $query_args[ 'post_type' ] ) ) {
			//Set default post type
			$query_args[ 'post_type' ] = array( 'page' );
		}
		$query_args[ 'post_type' ] = array_merge( $query_args[ 'post_type' ], $this->cpts );
		return $query_args;
	}

	/**
	 * The metabox of task post type
	 *
	 * @since    1.0.0
	 */
	public function cmb_task_metaboxes() {
		// Start with an underscore to hide fields from custom fields list
		$prefix = '_task_';

		$cmb_task = new_cmb2_box( array(
		    'id' => $prefix . 'metabox',
		    'title' => __( 'Task Info', $this->plugin_slug ),
		    'object_types' => array( 'task', ),
		    'context' => 'normal',
		    'priority' => 'high',
		    'show_names' => true,
			) );

		$cmb_task->add_field( array(
		    'name' => __( 'Subtitle', $this->plugin_slug ),
		    'desc' => __( 'Description in a row', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_subtitle',
		    'type' => 'text'
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Required or Suggested Tasks', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_before',
		    'type' => 'post_search_text',
		    'post_type' => 'task'
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Prerequisites', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_prerequisites',
		    'type' => 'wysiwyg',
		    'options' => array( 'textarea_rows' => '5' )
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Why this matters', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_matters',
		    'type' => 'wysiwyg',
		    'options' => array( 'textarea_rows' => '5' )
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Steps', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_steps',
		    'type' => 'wysiwyg',
		    'options' => array( 'textarea_rows' => '10' )
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Need Help?', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_help',
		    'type' => 'wysiwyg',
		    'options' => array( 'textarea_rows' => '5' )
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Completion', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_completion',
		    'type' => 'wysiwyg',
		    'options' => array( 'textarea_rows' => '5' )
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Mentor(s)', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_mentor',
		    'type' => 'user_search_text',
		    'roles' => array( 'administrator', 'author', 'editor' )
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Good next tasks', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_next',
		    'type' => 'post_search_text',
		    'post_type' => 'task'
		) );

		$cmb_task->add_field( array(
		    'id' => $prefix . $this->plugin_slug . '_users',
		    'type' => 'hidden'
		) );

		$cmb_task->add_field( array(
		    'id' => $prefix . $this->plugin_slug . '_counter',
		    'type' => 'hidden',
		    'default' => '0'
		) );

		$cmb_user_task = new_cmb2_box( array(
		    'id' => $prefix . 'user_metabox',
		    'title' => __( 'Task Completed', $this->plugin_slug ),
		    'object_types' => array( 'user' ),
		    'context' => 'normal',
		    'priority' => 'high',
		    'show_names' => true,
			) );

		$cmb_user_task->add_field( array(
		    'id' => $prefix . $this->plugin_slug . '_tasks',
		    'type' => 'hidden'
		) );

		$cmb_user_task->add_field( array(
		    'id' => $prefix . $this->plugin_slug . '_tasks_done',
		    'type' => 'hidden'
		) );
	}

	/**
	 * Return the total of done of the task
	 *
	 * @since    1.0.0
	 * 
	 * @param integer $task_id ID of the task.
	 */
	public function number_of_done( $task_id ) {
		$counter = get_post_field( '_task_' . $this->plugin_slug . '_counter', $task_id );
		if ( empty( $counter ) ) {
			return 0;
		} else {
			return $counter;
		}
	}

	/**
	 * Return the author of the task
	 *
	 * @since    1.0.0
	 * 
	 * @param integer $task_id ID of the task.
	 */
	public function author_of_task( $task_id ) {
		$author_id = get_post_field( 'post_author', $task_id );
		return '<a href="' . admin_url() . 'edit.php?post_type=task&author=' . $author_id . '">' . get_the_author_meta( 'user_nicename', $author_id ) . '</a>';
	}

}
