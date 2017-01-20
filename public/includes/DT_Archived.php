<?php

/**
 * Support for Archived Post Status
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @copyright 2015 GPL
 * @license   GPL-2.0+
 * @link      http://mte90.net
 */
class DT_Archived {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_filter( 'aps_status_arg_public', '__return_true' );
    add_filter( 'aps_status_arg_private', '__return_false' );
    add_filter( 'aps_status_arg_exclude_from_search', '__return_false' );
  }

}

new DT_Archived();
