<?php
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
?>

<div class="wrap">

    <h2>DaTask - <?php _e( 'Report', $this->plugin_slug ); ?></h2>

    <div id="tabs" class="settings-tab">
	<ul>
	    <li><a href="#tabs-1"><?php _e( 'Most Done', $this->plugin_slug ); ?></a></li>
	    <li><a href="#tabs-2"><?php _e( 'Extra', $this->plugin_slug ); ?></a></li>
	</ul>
	<div id="tabs-1" class="wrap">
	    <form id="export-log-form" method="post" action="" style="margin-top: -30px;position: absolute;">
                <input type="hidden" name="action" value="export-report-done" />
		<?php wp_nonce_field( $this->plugin_slug . '-export-report', $this->plugin_slug . '_once' ); ?>
		<?php submit_button( __( 'Download CSV', $this->plugin_slug ), 'button' ); ?>
	    </form>
	    <?php
	    $GLOBALS[ 'datask_report_done' ]->prepare_items();
	    $GLOBALS[ 'datask_report_done' ]->display();
	    ?>
	</div>
	<div id="tabs-2" class="wrap">
	    2
	</div>
    </div>

</div>
