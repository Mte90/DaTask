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
	<header class="entry-header jumbotron">
		<h2 class="entry-title"><?php the_title(); ?></h2>
		<h3><?php the_task_subtitle(); ?></h3>
	</header>
	<div class="entry-content">
		<?php	
		do_action('dt-task-info');
		the_content();	
		datask_buttons();
		comments_template();
		?>
	</div>
</article>