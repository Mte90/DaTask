<?php

/**
 * Support for Sortable Posts
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_Sortable_Posts {

  protected $log = '';

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_filter( 'sortable_post_types', array( $this, 'add_sortable_to_task' ) );
    add_filter( 'sortable_taxonomies', array( $this, 'add_sortable_to_area' ) );
    add_filter( 'sortable_post_inside_tax', array( $this, 'add_sortable_by_meta' ) );
  }

  public function add_sortable_to_task( $types ) {
    $types = array_merge( $types, array( 'task' ) );
    return $types;
  }

  public function add_sortable_to_area( $taxes ) {
    $taxes = array_merge( $taxes, array( 'task-area' ) );
    return $taxes;
  }

  public function add_sortable_by_meta( $array ) {
    $array[] = array( 'post_type' => 'task', 'taxonomy' => 'task-area' );
    return $array;
  }

}

new DT_Sortable_Posts();
