<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header jumbotron">
		<h2 class="entry-title"><?php the_title(); ?></h3>
		<h3><?php the_task_subtitle(); ?></h3>
	</header>
	<div class="entry-content">
		<?php	
		do_action('wo-task-info');
		the_content();	
		task_buttons();
		?>
	</div>
</article>