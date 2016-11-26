<?php

/**
 * Frontend Profile page
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_Frontend_Profile {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    // Create Fake Page for members feed
    new Fake_Page(
		array(
	  'slug' => 'member-feed',
	  'post_title' => __( 'Member Feed', DT_TEXTDOMAIN ),
	  'post_content' => 'content'
		)
    );
    add_filter( 'query_vars', array( $this, 'add_member_feed_permalink' ) );
    add_filter( 'init', array( $this, 'rewrite_rule' ) );
    add_filter( 'template_include', array( $this, 'userprofile_template' ) );
  }

  /**
   * Add the rewrite permalink for member feed
   *
   * @since    1.0.0
   * @param string $vars The permalinks.
   * @return array $vars The permalinks.
   */
  public function add_member_feed_permalink( $vars ) {
    $vars[] = 'member-feed';
    return $vars;
  }

  /**
   * Add the rewrite permalink for member
   *
   * @since    1.0.0
   */
  public function rewrite_rule() {
    add_rewrite_tag( '%member-feed%', '([^&]+)' );
    add_rewrite_rule(
		'^member-feed/([^/]*)/?', 'index.php?member-feed=$matches[1]', 'top'
    );
  }

  /**
   * Include the template for the profile page
   *
   * @since    1.0.0
   */
  public function userprofile_template( $original_template ) {
    global $wp_query;
    if ( is_author() ) {
	return wpbp_get_template_part( DT_TEXTDOMAIN, 'user', 'profile' );
    } elseif ( array_key_exists( 'member-feed', $wp_query->query_vars ) ) {
	if ( get_user_of_profile() !== null ) {
	  return wpbp_get_template_part( DT_TEXTDOMAIN, 'user', 'feed' );
	}
    }
    return $original_template;
  }

}

new DT_Frontend_Profile();
