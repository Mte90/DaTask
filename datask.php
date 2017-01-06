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

require_once( plugin_dir_path( __FILE__ ) . 'composer/autoload.php' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/DaTask.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_FakePage.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Log.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Sortable_Posts.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Uninstall.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_ActDeact.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_Functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/DT_myCred_Init.php' );

if ( is_admin() ) {
  require_once( plugin_dir_path( __FILE__ ) . 'admin/DaTask_Admin.php' );
}
