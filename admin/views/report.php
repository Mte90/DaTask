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
					echo '<li><a href="edit-tags.php?action=edit&taxonomy=$tax&tag_ID=' . $term->term_id . '&post_type=task">' . $term->name . '</a> (<b>' . $term->count . '</b> ' . __( 'Tasks', $slug ) . ')</li>';
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
<div class="wrap">

    <h2>DaTask - <?php _e( 'Report', DT_TEXTDOMAIN ); ?></h2>

    <div id="tabs" class="settings-tab">
	<ul>
	    <li><a href="#tabs-1"><?php _e( 'Most Done', DT_TEXTDOMAIN ); ?></a></li>
	    <li><a href="#tabs-2"><?php _e( 'Extra', DT_TEXTDOMAIN ); ?></a></li>
	</ul>
	<div id="tabs-1" class="wrap">
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
	<div id="tabs-2" class="wrap">
	    <?php
	    dt_table_tax( DT_TEXTDOMAIN, 'Estimated minutes', 'task-minute' );
	    dt_table_tax( DT_TEXTDOMAIN, 'Difficulties', 'task-difficulty' );
	    dt_table_tax( DT_TEXTDOMAIN, 'Teams', 'task-team' );
	    dt_table_tax( DT_TEXTDOMAIN, 'Areas', 'task-area' );
	    ?>
	</div>
    </div>

</div>
