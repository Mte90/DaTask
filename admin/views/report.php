<?php

//$mostdone = add_query_arg( array( 'page' => 'datask-report', 'mostdone' => '1' ), admin_url( 'index.php' ) );
//$approvalpending = add_query_arg( array( 'page' => 'datask-report', 'approvalpending' => '1' ), admin_url( 'index.php' ) );
/**
 * Represents the report view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
function dt_table_tax( $slug, $name, $tax ) {
  ?>
  <table class="widefat fixed">
      <thead>
  	  <tr>
  		<td><b><?php _e( $name, $slug ); ?></b></td>
  	  </tr>
      </thead>
      <tbody>
  	  <tr>
  		<td><?php
			$terms = get_terms( $tax, array( 'orderby' => 'count', 'order' => 'DESC' ) );
			if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
			  echo '<ul class="list-grid">';
			  foreach ( $terms as $term ) {
			    echo '<li><a href="edit-tags.php?action=edit&taxonomy=' . $tax . '&tag_ID=' . $term->term_id . '&post_type=task">' . $term->name . '</a> (<b>' . $term->count . '</b> ' . __( 'Tasks', $slug ) . ')</li>';
			  }
			  echo '</ul>';
			}
			?></td>
  	  </tr>
      </tbody>
  </table>
  <?php
}
?>
<div id="tabs" class="wrap">

    <h2>DaTask - <?php _e( 'Report', DT_TEXTDOMAIN ); ?></h2>

    <div class="report-tab">
	  <ul class="menu">
		<li><a href="#tabs-most" data-link="<?php echo add_query_arg( array( 'page' => 'datask-report', 'mostdone' => '1' ), admin_url( 'index.php' ) ) ?>"><?php _e( 'Task Most Done', DT_TEXTDOMAIN ); ?></a></li>
		<li><a href="#tabs-approval" data-link="<?php echo add_query_arg( array( 'page' => 'datask-report', 'approvalpending' => '1' ), admin_url( 'index.php' ) ); ?>"><?php _e( 'Task Approval Pending', DT_TEXTDOMAIN ); ?></a></li>
		<li><a href="#tabs-extra"><?php _e( 'Extra', DT_TEXTDOMAIN ); ?></a></li>
	  </ul>
	  <?php if ( (isset( $_GET[ 'mostdone' ] ) && $_GET[ 'mostdone' ] === 1) || !isset( $_GET[ 'approvalpending' ] ) ) { ?>
  	  <div id="tabs-most" class="wrap">
  		<form id="export-log-form" method="post" action="" style="margin-top: -30px;position: absolute;">
  		    <input type="hidden" name="action" value="export-report-done" />
			<?php wp_nonce_field( DT_TEXTDOMAIN . '-export-report', DT_TEXTDOMAIN . '_once' ); ?>
			<?php submit_button( __( 'Download CSV', DT_TEXTDOMAIN ), 'button' ); ?>
  		</form>
		  <?php
		  $GLOBALS[ 'datask_report_done' ]->prepare_items();
		  $GLOBALS[ 'datask_report_done' ]->display();
		  ?>
  	  </div>
	  <?php } else { ?>
  	  <div id="tabs-approval" class="wrap">
  		<form id="export-log-form" method="post" action="" style="margin-top: -30px;position: absolute;">
                  <input type="hidden" name="action" value="export-approval-done" />
			<?php wp_nonce_field( DT_TEXTDOMAIN . '-export-approval', DT_TEXTDOMAIN . '_once' ); ?>
			<?php submit_button( __( 'Download CSV', DT_TEXTDOMAIN ), 'button' ); ?>
  		</form>
		<?php wp_nonce_field( 'dt-task-admin-action', 'dt-task-admin-nonce' ); ?>
		  <?php
		  $GLOBALS[ 'datask_approval_pending' ]->prepare_items();
		  $GLOBALS[ 'datask_approval_pending' ]->display();
		  ?>
  	  </div>
	  <?php } ?>
	  <div id="tabs-extra" class="wrap">
		<?php
		dt_table_tax( DT_TEXTDOMAIN, 'Estimated minutes', 'task-minute' );
		dt_table_tax( DT_TEXTDOMAIN, 'Difficulties', 'task-difficulty' );
		dt_table_tax( DT_TEXTDOMAIN, 'Teams', 'task-team' );
		dt_table_tax( DT_TEXTDOMAIN, 'Areas', 'task-area' );
		?>
	  </div>
    </div>

</div>