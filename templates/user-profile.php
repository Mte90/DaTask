<?php
/**
 * The Template for displaying the user profile page
 *
 * This template can be overridden by copying it to yourtheme/datask/user-profile.php.
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
$plugin = DaTask::get_instance();
get_header();
?>
<div id="content-main" class="main" role="main">
    <div class="col-md-8">
	<h2>
	    <?php
	    $user = get_user_by( 'login', get_user_of_profile() );
	    printf( __( "%s's Profile", $plugin->get_plugin_slug() ), $user->display_name );
	    ?>
	</h2>
	<div class="row">
	    <div class="col-md-3">
		<?php echo get_avatar( $user->user_email, 128 ); ?>
	    </div>
	    <div class="col-md-9">
		<?php if ( is_user_logged_in() ) {
			?>
			<p><?php _e( 'Email', $plugin->get_plugin_slug() ) ?>: <a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a></p>
		<?php } ?>
		<?php if ( !empty( $user->user_url ) ) { ?>
			<p><?php _e( 'Website', $plugin->get_plugin_slug() ) ?>: <a href="<?php echo $user->user_url; ?>"><?php echo $user->user_url; ?></a></p>
		<?php } ?>
		<p><a href="<?php echo get_bloginfo( 'url' ) . '/member-feed/' . $user->user_login; ?>"><?php _e( 'RSS Feed Activity', $plugin->get_plugin_slug() ) ?></a></p>
		<?php if ( !empty( $user->description ) ) { ?>
			<div class="description"><?php echo wpautop( the_author_meta( 'description', $user->ID ) ); ?></div>
		<?php } ?>
		<?php
		$current_user = wp_get_current_user();
		if ( get_user_of_profile() === $current_user->user_login ) {
			?>
			<p><a href="<?php echo home_url( '/profile/' ); ?>"><?php _e( 'Edit profile', $plugin->get_plugin_slug() ); ?></a></p>
		<?php } ?>
	    </div>
	</div>
	<h2 class="alert alert-warning"><?php _e( 'Dashboard', $plugin->get_plugin_slug() ); ?></h2>
	<?php
	dt_tasks_later();
	dt_tasks_completed();
	datask_user_form();
	?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
