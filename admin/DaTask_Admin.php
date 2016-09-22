<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package   DaTask
 * @author    Daniele Scasciafratte <mte90net@gmail.com>
 * @license   GPL 2.0+
 * @link      http://mte90.net
 * @copyright 2015-2016
 */
class DaTask_Admin {

  /**
   * Instance of this class.
   *
   * @var      object
   *
   * @since    1.0.0
   */
  protected static $instance = null;

  /**
   * Slug of the plugin screen.
   *
   * @var      string
   *
   * @since    1.0.0
   */
  protected $admin_view_page = null;

  /**
   * Initialize the plugin by loading admin scripts & styles and adding a
   * settings page and menu.
   *
   * @since     1.0.0
   */
  private function __construct() {

    /*
     * @TODO :
     *
     * - Uncomment following lines if the admin class should only be available for super admins
      if( ! is_super_admin() ) {
      return;
      }
     */

    $plugin = DaTask::get_instance();
    $this->cpts = $plugin->get_cpts();
    // Load admin JavaScript after jQuery loading
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_files' ) );
    // Reset User Task 
    require_once( plugin_dir_path( __FILE__ ) . '/includes/DT_User_Backend.php' );
    /*
     * Load CMB
     */
    require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_CMB.php' );
    /*
     * Import Export settings
     */
    require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_ImpExp.php' );
    /*
     * Dismissible notice
     */
    //dnh_register_notice( 'my_demo_notice', 'updated', __( 'This is my dismissible notice', DT_TEXTDOMAIN ) );
    /*
     * Review Me notice
     */
    new WP_Review_Me( array(
	  'days_after' => 15,
	  'type' => 'plugin',
	  'slug' => DT_TEXTDOMAIN,
	  'rating' => 5,
	  'message' => __( 'Do you like DaTask? Review me or ignore that message!', DT_TEXTDOMAIN ),
	  'link_label' => __( 'Click here!', DT_TEXTDOMAIN )
		) );
    //Add the option for the report page, TODO load only on that page
    add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );
    // Add the options page and menu item.
    add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
    /*
     * Load CPT_Columns
     * 
     * Check the file for example
     */
    $post_columns = new CPT_columns( 'task' );
    $post_columns->add_column( 'Done', array(
	  'label' => __( 'Done', DT_TEXTDOMAIN ),
	  'type' => 'custom_value',
	  'callback' => array( $this, 'number_of_done' ),
	  'sortable' => true,
	  'prefix' => '<b>',
	  'suffix' => '</b>',
	  'def' => '0',
	  'order' => '-1',
	  'meta_key' => '_' . DT_TEXTDOMAIN . '_counter'
		)
    );
    $post_columns->add_column( 'Author', array(
	  'label' => __( 'Author', DT_TEXTDOMAIN ),
	  'type' => 'custom_value',
	  'sortable' => true,
	  'order' => '-1',
	  'meta_key' => 'post_author'
		)
    );
    /*
     * All the extras functions
     */
    require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Extras.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/includes/cmb2_post_search_field.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/includes/cmb2_user_search_field.php' );
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
   * @return    null    Return early if no settings page is registered.
   */
  public function enqueue_admin_files() {
    $screen = get_current_screen();
    if ( strpos( $screen->base, DT_TEXTDOMAIN ) !== false || strpos( $_SERVER[ 'REQUEST_URI' ], 'index.php' ) || strpos( $_SERVER[ 'REQUEST_URI' ], get_bloginfo( 'wpurl' ) . '/wp-admin/' ) ) {
	wp_enqueue_style( DT_TEXTDOMAIN . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array( 'dashicons' ), DT_VERSION );
    }
    if ( strpos( $screen->base, DT_TEXTDOMAIN ) !== false && ($screen->action === 'add' || (isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'edit' )) ) {
	wp_enqueue_script( DT_TEXTDOMAIN . '-task-admin-script', plugins_url( 'assets/js/task.js', __FILE__ ), array( 'jquery' ), DT_VERSION );
	wp_localize_script( DT_TEXTDOMAIN . '-task-admin-script', 'dt_js_admin_vars', array(
	    'alert' => __( 'You have not selected a taxonomy!', DT_TEXTDOMAIN ),
		  )
	);
    }
    if ( strpos( $screen->base, DT_TEXTDOMAIN ) !== false ) {
	wp_enqueue_script( DT_TEXTDOMAIN . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), DT_VERSION );
    }
  }

  /**
   * Register the administration menu for this plugin into the WordPress Dashboard menu.
   *
   * @since    1.0.0
   */
  public function add_plugin_admin_menu() {
    $this->plugin_screen_hook_suffix = add_menu_page( DT_TEXTDOMAIN, DT_NAME, 'manage_options', DT_TEXTDOMAIN . '-settings', array( $this, 'display_plugin_admin_page' ), 'dashicons-yes', 99 );
    $hook = add_dashboard_page( __( 'DT Report', DT_TEXTDOMAIN ), __( 'DT Report', DT_TEXTDOMAIN ), 'edit_posts', DT_TEXTDOMAIN . '-report', array( $this, 'display_plugin_report_page' ) );
    if ( !empty( $hook ) ) {
	//Call the report screen option only on the correct page
	add_action( 'load-' . $hook, array( $this, 'report_screen_option' ) );
    }
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
   * Render the report page 
   *
   * @since    1.1.0
   */
  public function display_plugin_report_page() {
    include_once( 'views/report.php' );
  }

  /**
   * Add the screen option for the report page
   *
   * @since    1.1.0
   */
  public function report_screen_option() {
    $option = 'per_page';
    $args = [
	  'label' => 'Task',
	  'default' => 5,
	  'option' => 'tasks_per_page'
    ];
    add_screen_option( $option, $args );
    require_once(plugin_dir_path( __FILE__ ) . '/includes/DT_MostDone_report.php');
    $GLOBALS[ 'datask_report_done' ] = new DT_MostDone();
  }

  /**
   * Return the value of the screen option
   *
   * @since    1.1.0
   */
  public function set_screen( $status, $option, $value ) {
    return $value;
  }

  /**
   * Return the total of done of the task
   *
   * @since    1.0.0
   * @param    integer $task_id ID of the task.
   * @return   integer $counter Number of counter done of the task
   */
  public function number_of_done( $task_id ) {
    $counter = get_post_field( '_' . DT_TEXTDOMAIN . '_counter', $task_id );
    if ( empty( $counter ) ) {
	return 0;
    }
    return $counter;
  }

}

add_action( 'plugins_loaded', array( 'DaTask_Admin', 'get_instance' ) );
