<?php
/**
 * The Template for displaying task content in the single.php template
 *
 * This template can be overridden by copying it to yourtheme/datask/log-in.php.
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
if ( !defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
	  <h5 class="entry-title display-4"><?php
		if ( get_post_status( get_the_ID() ) === 'archived' ) {
		  _e( 'Archived', DT_TEXTDOMAIN );
		}
		the_title();
		?></h5>
	  <h6><?php the_task_subtitle(); ?></h6>
    </header>
    <div class="entry-content">
	  <?php
	  do_action( 'dt_task_info' );
	  the_content();
	  do_shortcode( '[datask-badge]' );
	  if ( get_post_status( get_the_ID() ) === 'archive' ) {
	    _e( 'This task is archived, you can only read it.', DT_TEXTDOMAIN );
	  } else {
	    datask_buttons();
	  }
	  comments_template();
	  ?>
    </div>
</article>
