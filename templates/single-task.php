<?php
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

	<?php endwhile; // end of the loop.  ?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
