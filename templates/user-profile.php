<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$plugin = Wp_Oneanddone::get_instance();
get_header();
?>
<div id="content-main" class="main" role="main">
    <div class="col-md-8">
	<header class="entry-header jumbotron">
	    <h2><?php printf( __( "%s's Profile", $plugin->get_plugin_slug() ), get_user_of_profile() ); ?></h2>
	</header>
	<h2 class="alert alert-warning"><?php _e( 'Dashboard', $plugin->get_plugin_slug() ); ?></h2>
	<?php
	get_tasks_later();
	get_tasks_completed();
	?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
