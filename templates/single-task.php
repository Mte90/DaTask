<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
?>

<div id="content-main" class="main" role="main">
	<?php while ( have_posts() ) : the_post(); ?>

		<?php include(wo_get_template_part( 'content', 'single-task', false )); ?>

	<?php endwhile; // end of the loop. ?>
</div>
<?php
get_footer();
