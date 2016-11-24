<?php

class DaTask_myCred extends myCRED_Hook {

  /**
   * Constructor
   *
   * @param array $hook_prefs configured preferences
   * @param string point type
   */
  function __construct( $hook_prefs, $type = 'mycred_default' ) {
    parent::__construct( array(
	  'id' => 'datask_task_done_mycred',
	  'defaults' => array( 'creds' => '25', 'log' => '%plural% for task done' )
		), $hook_prefs, $type );
  }

  /**
   * Hook into WordPress. Called when executing myCRED hooks
   */
  public function run() {
    add_action( 'dt_set_completed_task', array( $this, 'add_point' ), 99999, 2 );
  }

  public function add_point( $user_id, $task_id ) {
    $prefs = $this->prefs;
    $this->core->add_creds(
		'datask_task_done_mycred', $user_id, $prefs[ 'creds' ], $prefs[ 'log' ], $task_id
    );
  }

  /**
   * Prints preferences fields to config hook options
   */
  public function preferences() {
    $prefs = $this->prefs;
    ?>
    <label class="subheader"><?php echo $this->core->plural(); ?></label>
    <ol>
        <li>
    	  <div class="h2"><input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->format_number( $prefs[ 'creds' ] ); ?>" size="8" /></div>
        </li>
    </ol>
    <label class="subheader"><?php _e( 'Log template', 'mycred' ); ?></label>
    <ol>
        <li>
    	  <div class="h2"><input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" value="<?php echo $prefs[ 'log' ]; ?>" class="long" /></div>
        </li>
    </ol>
    <?php
  }

}
