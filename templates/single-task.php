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

<div id="content-main" class="main" role="main">
    <div class="col-md-8">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		include(dt_get_template_part( 'content', 'single-task', false ));
		?>
	<?php endwhile; // End of the loop. ?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
