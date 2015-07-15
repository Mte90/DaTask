<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$plugin = DaTask::get_instance();
get_header();
?>
<div id="content-main" class="main" role="main">
    <div class="col-md-8">
	<header class="entry-header jumbotron">
	    <h2><?php $user = get_user_by( 'login', get_user_of_profile() );printf( __( "%s's Profile", $plugin->get_plugin_slug() ), $user->display_name ); ?></h2>
	</header>
	<h2 class="alert alert-warning"><?php _e( 'Dashboard', $plugin->get_plugin_slug() ); ?></h2>
	<?php
	dt_tasks_later();
	dt_tasks_completed();
	?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
