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
get_header();
?>
<div class="container">
    <div class="row">
	  <div id="primary" class="col-md-8">
		<h2 class="alert alert-info">
		    <?php
		    $user = get_user_by( 'id', get_user_of_profile( true ) );
		    printf( __( "%s's Profile", DT_TEXTDOMAIN ), $user->display_name );
		    ?>
		</h2>
		<div class="row">
		    <div class="col-md-3">
			  <?php echo get_avatar( $user->user_email, 128 ); ?>
		    </div>
		    <div class="col-md-9">
			  <?php if ( is_user_logged_in() ) {
			    ?>
  			  <p><?php _e( 'Email', DT_TEXTDOMAIN ) ?>: <a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a></p>
			  <?php } ?>
			  <?php if ( !empty( $user->user_url ) ) { ?>
  			  <p><?php _e( 'Website', DT_TEXTDOMAIN ) ?>: <a href="<?php echo $user->user_url; ?>"><?php echo $user->user_url; ?></a></p>
			    <?php
			  }
			  if ( class_exists( 'myCRED_Core' ) ) {
			    ?>
  			  <p><?php printf( __( 'User points: %s', DT_TEXTDOMAIN ), do_shortcode( '[mycred_my_balance user_id="' . $user->ID . '" wrapper=0 title_el="" balance_el=""]' ) ); ?></p>
			    <?php
			  }
			  ?>
			  <p><a href="<?php echo get_bloginfo( 'url' ) . '/member-feed/' . $user->user_login; ?>"><?php _e( 'RSS Feed Activity', DT_TEXTDOMAIN ) ?></a></p>
			  <?php if ( !empty( $user->description ) ) { ?>
  			  <div class="description"><?php echo wpautop( the_author_meta( 'description', $user->ID ) ); ?></div>
			    <?php
			  }
			  $current_user = wp_get_current_user();
			  if ( get_user_of_profile() === $current_user->user_login ) {
			    ?>
  			  <p><a href="<?php echo home_url( '/profile/' ); ?>"><?php _e( 'Edit profile', DT_TEXTDOMAIN ); ?></a></p>
			  <?php } ?>
		    </div>
		</div>
		<h4 class="alert alert-warning"><?php _e( 'Dashboard', DT_TEXTDOMAIN ); ?></h4>
		<?php
		datask_badgeos_user_achievements( $user->ID );
		datask_course_user();
		dt_tasks_later();
		dt_tasks_completed();
		datask_user_form();
		?>
	  </div>
	  <?php get_sidebar( 'right' ); ?>
    </div>
</div>
<?php
get_footer();
