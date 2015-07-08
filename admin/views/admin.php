<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Wp-Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2014 GPL
 */
?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <div id="tabs" class="settings-tab">
	<ul>
	    <li><a href="#tabs-1"><?php _e( 'General', $this->plugin_slug ); ?></a></li>
	    <li><a href="#tabs-2"><?php _e( 'Extra', $this->plugin_slug ); ?></a></li>
	    <li><a href="#tabs-3"><?php _e( 'Import/Export', $this->plugin_slug ); ?></a></li>
	</ul>
	<div id="tabs-1" class="wrap">
	    <?php
	    $cmb = new_cmb2_box( array(
		'id' => $this->plugin_slug . '_options',
		'hookup' => false,
		'show_on' => array( 'key' => 'options-page', 'value' => array( $this->plugin_slug ), ),
		'show_names' => true,
		    ) );
	    $cmb->add_field( array(
		'name' => 'Frontend Login',
		'id' => $this->plugin_slug . '_enable_frontend',
		'desc' => __( 'Add register/login in the frontend', $this->plugin_slug ),
		'type' => 'checkbox'
	    ) );
	    cmb2_metabox_form( $this->plugin_slug . '_options', $this->plugin_slug . '-settings' );
	    ?>
	</div>
	<div id="tabs-2" class="wrap">
	    <?php
	    $cmb = new_cmb2_box( array(
		'id' => $this->plugin_slug . '_options-second',
		'hookup' => false,
		'show_on' => array( 'key' => 'options-page', 'value' => array( $this->plugin_slug ), ),
		'show_names' => true,
		    ) );
	    $cmb->add_field( array(
		'name' => 'Tweet Field in comments',
		'id' => $this->plugin_slug . '_tweet_comments',
		'type' => 'checkbox'
	    ) );
	    cmb2_metabox_form( $this->plugin_slug . '_options-extra', $this->plugin_slug . '-settings-extra' );
	    ?>

	    <!-- @TODO: Provide other markup for your options page here. -->
	</div>
	<div id="tabs-3" class="metabox-holder">
	    <div class="postbox">
		<h3 class="hndle"><span><?php _e( 'Export Settings', $this->plugin_slug ); ?></span></h3>
		<div class="inside">
		    <p><?php _e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', $this->plugin_slug ); ?></p>
		    <form method="post">
			<p><input type="hidden" name="wo_action" value="export_settings" /></p>
			<p>
			    <?php wp_nonce_field( 'wo_export_nonce', 'wo_export_nonce' ); ?>
			    <?php submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
			</p>
		    </form>
		</div>
	    </div>

	    <div class="postbox">
		<h3 class="hndle"><span><?php _e( 'Import Settings', $this->plugin_slug ); ?></span></h3>
		<div class="inside">
		    <p><?php _e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', $this->plugin_slug ); ?></p>
		    <form method="post" enctype="multipart/form-data">
			<p>
			    <input type="file" name="wo_import_file"/>
			</p>
			<p>
			    <input type="hidden" name="wo_action" value="import_settings" />
			    <?php wp_nonce_field( 'wo_import_nonce', 'wo_import_nonce' ); ?>
			    <?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
			</p>
		    </form>
		</div>
	    </div>
	</div>
    </div>

    <div class="right-column-settings-page postbox">
	<h3 class="hndle"><span><?php _e( 'WP-OneAndDone', $this->plugin_slug ); ?></span></h3>
	<div class="inside">
	    <a href="https://github.com/Mte90/WordPress-Plugin-Boilerplate-Powered"><img src="https://github.com/Mte90/WordPress-Plugin-Boilerplate-Powered/raw/master/wp-oneanddone/assets/icon-256x256.png" alt=""></a>
	</div>
    </div>
</div>
