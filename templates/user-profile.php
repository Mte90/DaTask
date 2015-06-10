<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$plugin = Wp_Oneanddone::get_instance();
get_header();
?>
<div id="content-main" class="main" role="main">
    <h1><?php printf( __( "%s's Profile", $plugin->get_plugin_slug() ), get_user_of_profile() ); ?></h1>
    <h2><?php _e( 'Dashboard', $plugin->get_plugin_slug() ); ?></h2>
    <?php
    get_tasks_completed();
    ?>
</div>
<?php
get_footer();
