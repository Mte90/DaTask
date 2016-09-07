<?php

/**
 * DT_Comment
 * Comment supports for task post type
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_Comment {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_action( 'comment_form_logged_in_after', array( $this, 'task_comment_fields' ) );
    add_action( 'comment_form_after_fields', array( $this, 'task_comment_fields' ) );
    add_action( 'comment_post', array( $this, 'task_comment_save_data' ) );
    add_filter( 'comment_text', array( $this, 'task_comment_show_data_frontend' ), 99, 2 );
    add_action( 'add_meta_boxes_comment', array( $this, 'task_comment_show_metabox_data_backend' ) );
  }

  /**
   * 
   * Print Tweet fields in comments
   * 
   * @since    1.0.0
   */
  public function task_comment_fields() {
    global $post;
    if ( get_post_type( $post->ID ) === 'task' ) {
	?>
	<div class="form-group comment-form-tweet">
	    <label for="tweet_url"><?php _e( 'Insert URL of the Tweet', DT_TEXTDOMAIN ); ?></label>
	    <input type="text" name="tweet_url" id="tweet_url" class="form-control" />
	    <a href="https://twitter.com/share" class="twitter-share-button" data-via="Mte90net" data-hashtags="datask">Tweet</a>
	    <script>!function(d, s, id){var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location)?'http':'https'; if (!d.getElementById(id)){js = d.createElement(s); js.id = id; js.src = p + '://platform.twitter.com/widgets.js'; fjs.parentNode.insertBefore(js, fjs); }}(document, 'script', 'twitter-wjs');</script>
	</div>
	<?php
    }
  }

  /**
   * 
   * Save Tweet field in comments
   * 
   * @since 1.0.0
   * @param integer $comment_id The ID of the comment.
   */
  public function task_comment_save_data( $comment_id ) {
    global $post;
    if ( get_post_type( $post->ID ) === 'task' ) {
	add_comment_meta( $comment_id, 'tweet_url', esc_html( $_POST[ 'tweet_url' ] ) );
    }
  }

  /**
   * 
   * Add in frontend the tweet in comment
   * 
   * @since    1.0.0
   * @param string $text HTML code.
   * @param string $comment The comment.
   * @return string $text URL of the tweet
   */
  public function task_comment_show_data_frontend( $text, $comment ) {
    if ( get_post_type( $comment->comment_post_ID ) === 'task' ) {
	$tweet = get_comment_meta( $comment->comment_ID, 'tweet_url', true );
	if ( $tweet ) {
	  $tweet = __( 'URL of the Tweet', DT_TEXTDOMAIN ) . ': <a href="' . esc_attr( $tweet ) . '">' . esc_attr( $tweet ) . '</a>';
	  $text = $tweet . $text;
	}
    }
    return $text;
  }

  /**
   * 
   * Add metabox in comments
   * 
   * @since 1.0.0
   */
  public function task_comment_show_metabox_data_backend() {
    add_meta_box( 'task-comment', __( 'Task Feedback Data', DT_TEXTDOMAIN ), array( $this, 'task_comment_show_field_data_backend' ), 'comment', 'normal', 'high' );
  }

  /**
   * 
   * Show tweet url in backend comment
   * 
   * @since 1.0.0
   * @param string $comment The comment.
   */
  public function task_comment_show_field_data_backend( $comment ) {
    if ( get_post_type( $comment->comment_post_ID ) === 'task' ) {
	$tweet = get_comment_meta( $comment->comment_ID, 'tweet_url', true );
	wp_nonce_field( 'task_comment_nonce ', 'task_comment_nonce ', false );
	?>
	<p>
	    <label for="tweet_url"><?php _e( 'URL of the Tweet', DT_TEXTDOMAIN ); ?></label>
	    <input type="text" name="tweet_url" value="<?php echo esc_attr( $tweet ); ?>" class="widefat" />
	</p>
	<?php
    }
  }

}

new DT_Comment();
