<?php

class DT_myCred_Init {

  /**
   * Initialize the class 
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_filter( 'mycred_setup_hooks', array( $this, 'register_hook' ) );
    add_filter( 'mycred_init', array( $this, 'load_hooks' ) );
  }

  public function register_hook( $installed ) {
    $installed[ 'task_done' ] = array(
	  'title' => __( 'Task done', DT_TEXTDOMAIN ),
	  'description' => __( 'Add points on every task done', DT_TEXTDOMAIN ),
	  'callback' => array( 'DaTask_myCred' )
    );
    return $installed;
  }

  public function load_hooks() {
    require_once( plugin_dir_path( __FILE__ ) . 'DT_myCred.php' );
  }

}

new DT_myCred_Init();
