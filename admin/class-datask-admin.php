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
		add_action( 'admin_print_footer_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_files' ) );
		
		require_once( plugin_dir_path( __FILE__ ) . '/includes/WP-Admin-Notice/WP_Admin_Notice.php' );
		// Reset User Task 
		require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_User_Backend.php' );

		// At Glance Dashboard widget for your cpts
		add_filter( 'dashboard_glance_items', array( $this, 'cpt_dashboard_support' ), 10, 1 );
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

		/*
		 * Add metabox
		 */

		add_action( 'cmb2_init', array( $this, 'cmb_task_metaboxes' ) );
		
		// Add the export settings method
		add_action( 'admin_init', array( $this, 'settings_export' ) );
		// Add the import settings method
		add_action( 'admin_init', array( $this, 'settings_import' ) );

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
		    'def' => "Not defined", // Default value in case post meta not found
		    'order' => "-1"
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
		if ( $this->plugin_screen_hook_suffix === $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), DaTask::VERSION );
		}
	}
	
	/**
	 * Register and enqueue admin-specific style sheet.
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();
		if ( $screen->post_type === 'task' ) {
			echo '<script type="text/javascript">
			jQuery(document).ready(function() { 
				jQuery("#publish").click(function (e) {
					var mandatory = jQuery("#task-area-all .selectit input:checked, #task-team-all .selectit input:checked");
					if (mandatory.length === 0) {
						e.preventDefault();
					}
				});
			});
			</script>';
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

		$this->plugin_screen_hook_suffix = add_menu_page( __( 'Page Title', $this->plugin_slug ), $this->plugin_name, 'manage_options', $this->plugin_slug, array( $this, 'display_plugin_admin_page' ), 'dashicons-hammer', 90 );
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
	public function cpt_dashboard_support( $items = array() ) {
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
		    'object_types' => array( 'task', ), // Post type
		    'context' => 'normal',
		    'priority' => 'high',
		    'show_names' => true, // Show field names on the left
			) );

		$cmb_task->add_field( array(
		    'name' => __( 'Subtitle', $this->plugin_slug ),
		    'desc' => __( 'Description in a row', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_subtitle',
		    'type' => 'text'
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
		    'type' => 'text'
		) );

		$cmb_task->add_field( array(
		    'name' => __( 'Good next tasks (IDs)', $this->plugin_slug ),
		    'id' => $prefix . $this->plugin_slug . '_next',
		    'type' => 'post_search_text',
		    'post_type' => 'task'
		) );

		$cmb_task->add_field( array(
		    'id' => $prefix . $this->plugin_slug . '_users',
		    'type' => 'hidden'
		) );

		$cmb_user_task = new_cmb2_box( array(
		    'id' => $prefix . 'user_metabox',
		    'title' => __( 'Task Completed', $this->plugin_slug ),
		    'object_types' => array( 'user' ), // Post type
		    'context' => 'normal',
		    'priority' => 'high',
		    'show_names' => true, // Show field names on the left
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
		// The number of user is the number of done
		$users_of_task = get_users_by_task( $task_id );
		return count( $users_of_task );
	}
	
	/**
	 * Process a settings export from config
	 * @since    1.0.0
	 */
	function settings_export() {
		if ( empty( $_POST[ 'dt_action' ] ) || 'export_settings' != $_POST[ 'dt_action' ] ) {
			return;
		}
		if ( !wp_verify_nonce( $_POST[ 'dt_export_nonce' ], 'dt_export_nonce' ) ) {
			return;
		}
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings[ 0 ] = get_option( $this->plugin_slug . '-settings' );
		$settings[ 1 ] = get_option( $this->plugin_slug . '-settings-extra' );
		ignore_user_abort( true );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=pn-settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
			echo json_encode( $settings, JSON_PRETTY_PRINT );
		} else {
			echo json_encode( $settings );
		}
		exit;
	}
	/**
	 * Process a settings import from a json file
	 * @since    1.0.0
	 */
	function settings_import() {
		if ( empty( $_POST[ 'dt_action' ] ) || 'import_settings' != $_POST[ 'dt_action' ] ) {
			return;
		}
		if ( !wp_verify_nonce( $_POST[ 'pn_import_nonce' ], 'dt_import_nonce' ) ) {
			return;
		}
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$extension = end( explode( '.', $_FILES[ 'dt_import_file' ][ 'name' ] ) );
		if ( $extension != 'json' ) {
			wp_die( __( 'Please upload a valid .json file', $this->plugin_slug ) );
		}
		$import_file = $_FILES[ 'pn_import_file' ][ 'tmp_name' ];
		if ( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import', $this->plugin_slug ) );
		}
		// Retrieve the settings from the file and convert the json object to an array.
		$settings = ( array ) json_decode( file_get_contents( $import_file ) );
		update_option( $this->plugin_slug . '-settings', get_object_vars( $settings[ 0 ] ) );
		update_option( $this->plugin_slug . '-settings-extra', get_object_vars( $settings[ 1 ] ) );
		wp_safe_redirect( admin_url( 'options-general.php?page=' . $this->plugin_slug ) );
		exit;
	}


}
