<?php
/**
 * The Template for displaying all single tasks
 *
 * This template can be overridden by copying it to yourtheme/datask/single-task.php.
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

get_header();
?>

<div class="container">
    <div class="row">
	  <div id="primary" class="col-md-9">
		<main id="main" class="site-main" role="main">
		    <?php while ( have_posts() ) : the_post(); ?>
			<?php
			wpbp_get_template_part( DT_TEXTDOMAIN, 'content', 'single-task' );
			?>
		    <?php endwhile; // End of the loop. ?>
	  </div>
	  <?php get_sidebar(); ?>
    </div>
    <?php
    get_footer();
    