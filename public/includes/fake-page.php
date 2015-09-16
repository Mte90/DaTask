<?php

//Based on https://coderwall.com/p/fwea7g
//Update to work on last Wordpress versione

if ( !class_exists( 'Fake_Page' ) ) {

	/**
	 * Simple class that generate a fake page on the fly
	 *
	 * @package   Plugin_Name
	 * @author    Ohad Raz & Mte90 <mte90net@gmail.com>
	 * @license   GPL-2.0+
	 * @copyright 2014 
	 */
	class Fake_Page {

		public $slug = '';
		public $args = array();

		/**
		 * __construct<br>
		 * initialize the Fake Page
		 * @param array $args
		 * @author Ohad Raz 
		 * 
		 */
		function __construct( $args ) {
			add_filter( 'the_posts', array( $this, 'fake_page_filter' ) );
			$this->args = $args;
			$this->slug = $args[ 'slug' ];
			add_filter( 'nav_menu_items_page', array( $this, 'add_nav_menu' ), 9999, 3 );
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'add_nav_menu_description' ), 9999 );
		}

		/**
		 * fake_page_filter<br>
		 * Catches the request and returns the page as if it was retrieved from the database
		 * @param  array $posts 
		 * @return array 
		 * @author Ohad Raz & Mte90
		 */
		public function fake_page_filter( $posts ) {
			global $wp, $wp_query;
			$page_slug = $this->slug;

			//check if user is requesting our fake page
			if (
				count( $posts ) === 0 &&
				(basename( $_SERVER[ "SCRIPT_FILENAME" ], '.php' ) === 'nav-menus' ||
				strtolower( $wp->request ) === $page_slug ||
				isset( $wp->query_vars[ 'page_id' ] ) && $wp->query_vars[ 'page_id' ] === $page_slug ||
				( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'add-menu-item' ))
			) {
				//create a fake post
				$post = new stdClass;
				$post->ID = '1' . rand( 1, 99999999999 );
				$post->post_author = 1;
				//dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
				$post->post_date = current_time( 'mysql' );
				$post->post_date_gmt = current_time( 'mysql', 1 );
				$post->post_title = $this->args[ 'post_title' ];
				$post->post_content = $this->args[ 'post_content' ];
				$post->comment_status = 'closed';
				$post->ping_status = 'closed';
				$post->post_parent = 0;
				$post->menu_item_parent = 0;
				$post->post_password = '';
				$post->post_name = $page_slug;
				$post->to_ping = '';
				$post->pinged = '';
				$post->modified = $post->post_date;
				$post->modified_gmt = $post->post_date_gmt;
				$post->guid = get_bloginfo( 'wpurl' . '/' . $page_slug );
				$post->menu_order = 0;
				$post->post_type = 'page';
				$post->post_status = 'publish';
				$post->post_mime_type = '';
				$post->comment_count = 0;
				$post->description = '';
				$post->ancestors = array();

				$post = ( object ) array_merge( ( array ) $post, ( array ) $this->args );

				$post = new WP_Post( $post );
				$GLOBALS[ 'post' ] = $post;
				$posts = array( $post );

				$wp_query->is_page = true;
				$wp_query->is_singular = true;
				$wp_query->is_home = false;
				$wp_query->is_archive = false;
				$wp_query->is_category = false;
				$wp_query->is_404 = false;
				unset( $wp_query->query[ "error" ] );
				$wp_query->query_vars[ "error" ] = "";
				$wp_query->found_posts = 1;
				$wp_query->post_count = 1;
				$wp_query->comment_count = 0;
				$wp_query->current_comment = null;
				$wp_query->queried_object = $post;
				$wp_query->queried_object_id = $post->ID;
				$wp_query->current_post = $post->ID;
			}

			return $posts;
		}

		public function add_nav_menu( $posts, $args, $post_type ) {
			if ( $post_type[ 'id' ] === 'add-page' && $post_type[ 'args' ]->name === 'page' ) {
				$post = $this->fake_page_filter( array() );
				$posts[] = $post[ 0 ];
			}
			return $posts;
		}

		public function add_nav_menu_description( $item ) {
			if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'add-menu-item' ) {
				if ( !isset( $item->description ) ) {
					$item = $this->fake_page_filter( array() );
					$item = $item[ 0 ];
				}
			}

			return $item;
		}

	}

}