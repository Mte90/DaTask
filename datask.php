<?php

/**
 * @package   DaTask
 * @author    Daniele Scasciafratte <mte90net@gmail.com>
 * @license   GPL 2.0+
 * @link      http://mte90.net
 * @copyright 2015-2016
 *
 * Plugin Name:       DaTask
 * Plugin URI:        https://github.com/Mte90/DaTask
 * Description:       Task Management system inspired to Mozilla OneAndDone project
 * Version:           2.0.0
 * Author:            Mte90
 * Author URI:        http://mte90.net
 * Text Domain:       datask
 * License:           GPL 2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WordPress-Plugin-Boilerplate-Powered: v2.0.0
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
  die;
}

define( 'DT_VERSION', '2.0.0' );
define( 'DT_TEXTDOMAIN', 'datask' );
define( 'DT_NAME', 'DaTask' );

/**
 * Load the language files for the plugin
 */
function dt_load_plugin_textdomain() {
  load_plugin_textdomain( DT_TEXTDOMAIN, false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'dt_load_plugin_textdomain', 1 );

require_once( plugin_dir_path( __FILE__ ) . 'composer/autoload.php' );

require_once( plugin_dir_path( __FILE__ ) . 'public/DaTask.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_FakePage.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Log.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Uninstall.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_ActDeact.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Functions.php' );

if ( is_admin() && (!defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
  require_once( plugin_dir_path( __FILE__ ) . 'admin/DaTask_Admin.php' );
}
