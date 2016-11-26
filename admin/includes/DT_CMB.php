<?php

/**
 * All the CMB related code.
 *
 * @package   DaTask
 * @author    Daniele Scasciafratte <mte90net@gmail.com>
 * @license   GPL 2.0+
 * @link      http://mte90.net
 * @copyright 2015-2016
 */
class DT_CMB {

  /**
   * Initialize CMB2.
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_action( 'cmb2_init', array( $this, 'fields' ) );
  }

  /**
   * CMB fields
   *
   * @since    1.0.0
   * 
   * @return void
   */
  public function fields() {
    // Start with an underscore to hide fields from custom fields list
    $prefix = '_';

    $cmb_task = new_cmb2_box( array(
	  'id' => $prefix . DT_TEXTDOMAIN . 'metabox',
	  'title' => __( 'Task Info', DT_TEXTDOMAIN ),
	  'object_types' => array( 'task', ),
	  'context' => 'normal',
	  'priority' => 'high',
	  'show_names' => true,
		) );

    $cmb_task->add_field( array(
	  'name' => __( 'Manual Approval', DT_TEXTDOMAIN ),
	  'desc' => __( 'This task require a manual approval from the mentor of the task if checked. That approval can asked by a comment in the page or with a personal message via email.', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_approval',
	  'type' => 'radio',
	  'default' => 'none',
	  'options' => array(
		'comment' => __( 'Ask to post a comment in the task', DT_TEXTDOMAIN ),
		'email' => __( 'Invite the user to sent an email to the mentor', DT_TEXTDOMAIN ),
		'none' => __( 'Disable Manual approval', DT_TEXTDOMAIN ),
	  )
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Subtitle', DT_TEXTDOMAIN ),
	  'desc' => __( 'Description in a row', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_subtitle',
	  'type' => 'text',
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Mentor(s)', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_mentor',
	  'type' => 'user_search_text',
	  'roles' => array( 'administrator', 'author', 'editor' )
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Good next tasks', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_next',
	  'type' => 'post_search_text',
	  'post_type' => 'task'
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Required or Suggested Tasks', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_before',
	  'type' => 'post_search_text',
	  'post_type' => 'task'
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Prerequisites', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_prerequisites',
	  'type' => 'wysiwyg',
	  'options' => array( 'textarea_rows' => '5' )
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Why this matters', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_matters',
	  'type' => 'wysiwyg',
	  'options' => array( 'textarea_rows' => '5' )
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Steps', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_steps',
	  'type' => 'wysiwyg',
	  'options' => array( 'textarea_rows' => '10' )
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Need Help?', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_help',
	  'type' => 'wysiwyg',
	  'options' => array( 'textarea_rows' => '5' )
    ) );

    $cmb_task->add_field( array(
	  'name' => __( 'Completion', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_completion',
	  'type' => 'wysiwyg',
	  'options' => array( 'textarea_rows' => '5' )
    ) );

    $cmb_task->add_field( array(
	  'id' => $prefix . DT_TEXTDOMAIN . '_counter',
	  'type' => 'hidden',
	  'default' => '0'
    ) );

    $cmb_user_task = new_cmb2_box( array(
	  'id' => $prefix . 'user_metabox',
	  'title' => __( 'Task Later', DT_TEXTDOMAIN ),
	  'object_types' => array( 'user' ),
	  'context' => 'normal',
	  'priority' => 'high',
	  'show_names' => true,
		) );

    $cmb_user_task->add_field( array(
	  'id' => $prefix . DT_TEXTDOMAIN . '_tasks_later',
	  'type' => 'hidden'
    ) );

    $cmb_term = new_cmb2_box( array(
	  'id' => $prefix . DT_TEXTDOMAIN . 'area',
	  'title' => __( 'Featured Image', DT_TEXTDOMAIN ),
	  'object_types' => array( 'term' ),
	  'taxonomies' => array( 'task-team' ),
	  'new_term_section' => true,
		) );

    $cmb_term->add_field( array(
	  'name' => __( 'Featured Image', DT_TEXTDOMAIN ),
	  'id' => $prefix . DT_TEXTDOMAIN . '_featured',
	  'type' => 'file',
    ) );
  }

}

new DT_CMB();
