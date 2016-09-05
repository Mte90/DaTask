<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   DaTask
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2016 Your Name or Company Name
 */
?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <div id="tabs" class="settings-tab">
	  <ul>
		<li><a href="#tabs-1"><?php _e( 'Settings' ); ?></a></li>
		<li><a href="#tabs-2"><?php _e( 'Settings 2', DT_TEXTDOMAIN ); ?></a></li>
		<?php
				?>
		<li><a href="#tabs-3"><?php _e( 'Import/Export', DT_TEXTDOMAIN ); ?></a></li>
		<?php
				?>
	  </ul>
	  <div id="tabs-1" class="wrap">
		<?php
				$cmb = new_cmb2_box( array(
		    'id' => DT_TEXTDOMAIN . '_options',
		    'hookup' => false,
		    'show_on' => array( 'key' => 'options-page', 'value' => array( DT_TEXTDOMAIN ), ),
		    'show_names' => true,
			  ) );
		$cmb->add_field( array(
		    'name' => __( 'Text', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_text',
		    'type' => 'text',
		    'default' => 'Default Text',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Color Picker', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_colorpicker',
		    'type' => 'colorpicker',
		    'default' => '#bada55',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Text Medium', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_textmedium',
		    'type' => 'text_medium',
			  // 'repeatable' => true,
		) );
		$cmb->add_field( array(
		    'name' => __( 'Website URL', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_url',
		    'type' => 'text_url',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Text Email', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_email',
		    'type' => 'text_email',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Time', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_time',
		    'type' => 'text_time',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Time zone', DT_TEXTDOMAIN ),
		    'desc' => __( 'Time zone', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_timezone',
		    'type' => 'select_timezone',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Date Picker', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_textdate',
		    'type' => 'text_date',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Date Picker (UNIX timestamp)', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_textdate_timestamp',
		    'type' => 'text_date_timestamp',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Date/Time Picker Combo (UNIX timestamp)', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_datetime_timestamp',
		    'type' => 'text_datetime_timestamp',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Money', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_textmoney',
		    'type' => 'text_money',
		    'before_field' => '€', // Override '$' symbol if needed
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Text Area', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_textarea',
		    'type' => 'textarea',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Text Area Small', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_textareasmall',
		    'type' => 'textarea_small',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Text Area for Code', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_textarea_code',
		    'type' => 'textarea_code',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Title Weeeee', DT_TEXTDOMAIN ),
		    'desc' => __( 'This is a title description', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_title',
		    'type' => 'title',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Select', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_select',
		    'type' => 'select',
		    'show_option_none' => true,
		    'options' => array(
			  'standard' => __( 'Option One', DT_TEXTDOMAIN ),
			  'custom' => __( 'Option Two', DT_TEXTDOMAIN ),
			  'none' => __( 'Option Three', DT_TEXTDOMAIN ),
		    ),
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Radio inline', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_radio_inline',
		    'type' => 'radio_inline',
		    'show_option_none' => 'No Selection',
		    'options' => array(
			  'standard' => __( 'Option One', DT_TEXTDOMAIN ),
			  'custom' => __( 'Option Two', DT_TEXTDOMAIN ),
			  'none' => __( 'Option Three', DT_TEXTDOMAIN ),
		    ),
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Radio', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_radio',
		    'type' => 'radio',
		    'options' => array(
			  'option1' => __( 'Option One', DT_TEXTDOMAIN ),
			  'option2' => __( 'Option Two', DT_TEXTDOMAIN ),
			  'option3' => __( 'Option Three', DT_TEXTDOMAIN ),
		    ),
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Taxonomy Radio', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_text_taxonomy_radio',
		    'type' => 'taxonomy_radio',
		    'taxonomy' => 'category', // Taxonomy Slug
			  // 'inline'  => true, // Toggles display to inline
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Taxonomy Select', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_taxonomy_select',
		    'type' => 'taxonomy_select',
		    'taxonomy' => 'category', // Taxonomy Slug
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Taxonomy Multi Checkbox', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_multitaxonomy',
		    'type' => 'taxonomy_multicheck',
		    'taxonomy' => 'category', // Taxonomy Slug
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Checkbox', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_checkbox',
		    'type' => 'checkbox',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Multi Checkbox', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_multicheckbox',
		    'type' => 'multicheck',
		    'options' => array(
			  'check1' => __( 'Check One', DT_TEXTDOMAIN ),
			  'check2' => __( 'Check Two', DT_TEXTDOMAIN ),
			  'check3' => __( 'Check Three', DT_TEXTDOMAIN ),
		    ),
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test wysiwyg', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_wysiwyg',
		    'type' => 'wysiwyg',
		    'options' => array( 'textarea_rows' => 5, ),
		) );
		$cmb->add_field( array(
		    'name' => __( 'Test Image', DT_TEXTDOMAIN ),
		    'desc' => __( 'Upload an image or enter a URL.', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_image',
		    'type' => 'file',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Multiple Files', DT_TEXTDOMAIN ),
		    'desc' => __( 'Upload or add multiple images/attachments.', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_file_list',
		    'type' => 'file_list',
		    'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
		) );
		$cmb->add_field( array(
		    'name' => __( 'oEmbed', DT_TEXTDOMAIN ),
		    'desc' => __( 'Enter a youtube, twitter, or instagram URL. Supports services listed at <a href="http://codex.wordpress.org/Embeds">http://codex.wordpress.org/Embeds</a>.', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_embed',
		    'type' => 'oembed',
		) );
		$cmb->add_field( array(
		    'name' => 'Testing Field Parameters',
		    'id' => DT_TEXTDOMAIN . '_parameters',
		    'type' => 'text',
		    'before_row' => '<p>before_row_if_2</p>', // Callback
		    'before' => '<p>Testing <b>"before"</b> parameter</p>',
		    'before_field' => '<p>Testing <b>"before_field"</b> parameter</p>',
		    'after_field' => '<p>Testing <b>"after_field"</b> parameter</p>',
		    'after' => '<p>Testing <b>"after"</b> parameter</p>',
		    'after_row' => '<p>Testing <b>"after_row"</b> parameter</p>',
		) );


		cmb2_metabox_form( DT_TEXTDOMAIN . '_options', DT_TEXTDOMAIN . '-settings' );
				?>

		<!-- @TODO: Provide other markup for your options page here. -->
	  </div>
	  <div id="tabs-2" class="wrap">
		<?php
				$cmb = new_cmb2_box( array(
		    'id' => DT_TEXTDOMAIN . '_options-second',
		    'hookup' => false,
		    'show_on' => array( 'key' => 'options-page', 'value' => array( DT_TEXTDOMAIN ), ),
		    'show_names' => true,
			  ) );
		$cmb->add_field( array(
		    'name' => __( 'Text', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_text-second',
		    'type' => 'text',
		    'default' => 'Default Text',
		) );
		$cmb->add_field( array(
		    'name' => __( 'Color Picker', DT_TEXTDOMAIN ),
		    'desc' => __( 'field description (optional)', DT_TEXTDOMAIN ),
		    'id' => DT_TEXTDOMAIN . '_colorpicker-second',
		    'type' => 'colorpicker',
		    'default' => '#bada55',
		) );

		cmb2_metabox_form( DT_TEXTDOMAIN . '_options-second', DT_TEXTDOMAIN . '-settings-second' );
				?>

		<!-- @TODO: Provide other markup for your options page here. -->
	  </div>
	  <?php
	  	  ?>
	  <div id="tabs-3" class="metabox-holder">
		<div class="postbox">
		    <h3 class="hndle"><span><?php _e( 'Export Settings', DT_TEXTDOMAIN ); ?></span></h3>
		    <div class="inside">
			  <p><?php _e( 'Export the plugin\'s settings for this site as a .json file. This will allows you to easily import the configuration to another installation.', DT_TEXTDOMAIN ); ?></p>
			  <form method="post">
				<p><input type="hidden" name="d_action" value="export_settings" /></p>
				<p>
				    <?php wp_nonce_field( 'd_export_nonce', 'd_export_nonce' ); ?>
				    <?php submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
				</p>
			  </form>
		    </div>
		</div>

		<div class="postbox">
		    <h3 class="hndle"><span><?php _e( 'Import Settings', DT_TEXTDOMAIN ); ?></span></h3>
		    <div class="inside">
			  <p><?php _e( 'Import the plugin\'s settings from a .json file. This file can be retrieved by exporting the settings from another installation.', DT_TEXTDOMAIN ); ?></p>
			  <form method="post" enctype="multipart/form-data">
				<p>
				    <input type="file" name="d_import_file"/>
				</p>
				<p>
				    <input type="hidden" name="d_action" value="import_settings" />
				    <?php wp_nonce_field( 'd_import_nonce', 'd_import_nonce' ); ?>
				    <?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
				</p>
			  </form>
		    </div>
		</div>
	  </div>
	  <?php
	  	  ?>
    </div>

    <div class="right-column-settings-page metabox-holder">
	  <div class="postbox">
		<h3 class="hndle"><span><?php _e( 'Plugin Name.', DT_TEXTDOMAIN ); ?></span></h3>
		<div class="inside">
		    <a href="https://github.com/Mte90/WordPress-Plugin-Boilerplate-Powered"><img src="https://github.com/Mte90/WordPress-Plugin-Boilerplate-Powered/raw/master/datask/assets/icon-256x256.png" alt=""></a>
		</div>
	  </div>
    </div>
</div>
