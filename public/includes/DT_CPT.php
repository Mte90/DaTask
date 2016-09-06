<?php

/**
 * Post types and taxonomy for DaTask
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_CPT {

  /**
   * Options extra
   *
   * @var array
   */
  protected $options_extra = null;

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    $this->options_extra = get_option( DT_TEXTDOMAIN . '-settings-extra' );
    add_action( 'init', array( $this, 'load_cpt' ), 4 );
    add_action( 'init', array( $this, 'load_taxonomy' ), 4 );
  }

  /**
   * Load the CPT
   *
   * @since    1.0.0
   */
  public function load_cpt() {
    $task_post_type = array(
	  'supports' => array( 'title', 'comments', 'post-expiration' ),
	  'capabilities' => array(
		'edit_post' => 'edit_tasks',
		'edit_others_posts' => 'edit_others_tasks',
	  ),
	  'map_meta_cap' => true,
	  'show_in_rest' => true,
	  'menu_icon' => 'dashicons-welcome-add-page',
    );
    if ( isset( $this->options_extra[ 'cpt_slug' ] ) && !empty( $this->options_extra[ 'cpt_slug' ] ) ) {
	$task_post_type[ 'rewrite' ][ 'slug' ] = $this->options_extra[ 'cpt_slug' ];
    }
    register_via_cpt_core( array( __( 'Task', DT_TEXTDOMAIN ), __( 'Tasks', DT_TEXTDOMAIN ), 'task' ), $task_post_type );
  }

  /**
   * Load the Taxonomy
   *
   * @since    1.0.0
   */
  public function load_taxonomy() {
    $tax = array(
	  'public' => true,
	  'show_in_rest' => true,
	  'capabilities' => array(
		'assign_terms' => 'edit_posts',
	  ) );
    $tax_area = $tax;
    if ( isset( $this->options_extra[ 'tax_area' ] ) && !empty( $this->options_extra[ 'tax_area' ] ) ) {
	$tax_area[ 'rewrite' ][ 'slug' ] = $this->options_extra[ 'tax_area' ];
    }
    register_via_taxonomy_core( array( __( 'Area', DT_TEXTDOMAIN ), __( 'Areas', DT_TEXTDOMAIN ), 'task-area' ), $tax_area, array( 'task' ) );

    $tax_difficulty = $tax;
    if ( isset( $this->options_extra[ 'tax_difficulty' ] ) && !empty( $this->options_extra[ 'tax_difficulty' ] ) ) {
	$tax_difficulty[ 'rewrite' ][ 'slug' ] = $this->options_extra[ 'tax_difficulty' ];
    }
    register_via_taxonomy_core( array( __( 'Difficulty', DT_TEXTDOMAIN ), __( 'Difficulties', DT_TEXTDOMAIN ), 'task-difficulty' ), $tax_difficulty, array( 'task' ) );

    $task_team = $tax;
    if ( isset( $this->options_extra[ 'tax_team' ] ) && !empty( $this->options_extra[ 'tax_team' ] ) ) {
	$task_team[ 'rewrite' ][ 'slug' ] = $this->options_extra[ 'tax_team' ];
    }
    register_via_taxonomy_core( array( __( 'Team', DT_TEXTDOMAIN ), __( 'Teams', DT_TEXTDOMAIN ), 'task-team' ), $task_team, array( 'task' ) );

    $task_minute = $tax;
    if ( isset( $this->options_extra[ 'tax_minute' ] ) && !empty( $this->options_extra[ 'tax_minute' ] ) ) {
	$task_minute[ 'rewrite' ][ 'slug' ] = $this->options_extra[ 'tax_minute' ];
    }
    register_via_taxonomy_core( array( __( 'Estimated minute', DT_TEXTDOMAIN ), __( 'Estimated minutes', DT_TEXTDOMAIN ), 'task-minute' ), $task_minute, array( 'task' ) );
  }

}

new DT_CPT();
