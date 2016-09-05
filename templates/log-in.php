<?php
/**
 * The Template for displaying the login page
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

$action = !empty( $_GET[ 'action' ] ) && ($_GET[ 'action' ] === 'register' || $_GET[ 'action' ] === 'forgot' || $_GET[ 'action' ] === 'resetpass') ? $_GET[ 'action' ] : 'login';
$success = !empty( $_GET[ 'success' ] );
$failed = !empty( $_GET[ 'failed' ] ) ? $_GET[ 'failed' ] : false;
?>

<div id="content-main" class="main" role="main">
    <article id="page-<?php the_ID(); ?>" class="row hentry">
	  <?php if ( $action === 'register' && $success ): ?>
  	  <div class="panel card panel-primary card-primary">
  		<div class="panel-heading card-header">
  		    <h1><?php _e( 'Success!', DT_TEXTDOMAIN ); ?></h1>
  		</div>
  		<div class="panel-body card-block">
			<?php _e( 'Check your email for the password and then return to log in.', DT_TEXTDOMAIN ); ?>
  		</div>
  	  </div>
	  <?php elseif ( $action === 'forgot' && $success ): ?>
  	  <div class="panel card panel-primary card-primary">
  		<div class="panel-heading card-header">
  		    <h1><?php _e( 'Password recovery', DT_TEXTDOMAIN ); ?></h1>
  		</div>
  		<div class="panel-body card-block">
			<?php _e( 'Check your email for the instructions to get a new password.', DT_TEXTDOMAIN ); ?>
  		</div>
  	  </div>
	  <?php elseif ( $action === 'resetpass' && $success ): ?>
  	  <div class="panel card panel-success card-success">
  		<div class="panel-heading card-header">
  		    <h1><?php _e( 'Password reset', DT_TEXTDOMAIN ); ?></h1>
  		</div>
  		<div class="panel-body card-block">
			<?php _e( 'Your password has been updated. <a href="/login/">Proceed to login</a>.', DT_TEXTDOMAIN ); ?>
  		</div>
  	  </div>
	  <?php else: ?>
  	  <div class="panel-login col-lg-6" <?php
	    if ( $action === 'resetpass' ) {
		echo 'style="display:none;"';
	    }
	    ?>>
			 <?php if ( $action === 'login' && $failed ): ?>
    		<div class="panel card panel-danger card-danger">
    		    <div class="panel-heading card-header">
				<?php
				if ( $failed ) {
				  _e( 'Invalid username or password. Please try again.', DT_TEXTDOMAIN );
				}
				?>
    		    </div>
    		</div>
		  <?php endif; ?>
  		<div class="panel panel-info card card-info">
  		    <div class="panel-heading card-header">
			    <?php _e( 'Login', DT_TEXTDOMAIN ); ?>
  		    </div>
  		    <div class="panel-body card-block">
  			  <form name="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
  				<div class="form-group">
  				    <label for="user_login"><?php _e( 'Username' ) ?></label>
  				    <input type="text" name="log" id="user_login" class="form-control user_login" value="" size="20" />
  				</div>
  				<div class="form-group">
  				    <label for="user_pass"><?php _e( 'Password' ) ?></label>
  				    <input type="password" name="pwd" id="user_pass" class="form-control user_pass" value="" size="20" />
  				</div>
  				<div class="form-group">
  				    <input type="submit" name="wp-submit" id="wp-submit" class="button btn btn-primary" value="<?php _e( 'Login' ); ?>" />
  				</div>
				  <?php
				  do_action( 'login_form' );
				  ?>
  			  </form>
  		    </div>
  		</div>
  	  </div>
	    <?php if ( get_option( 'users_can_register' ) ) { ?>
    	  <div class="panel-register col-lg-6" <?php
		if ( $action === 'resetpass' ) {
		  echo 'style="display:none;"';
		}
		?>>
			   <?php if ( $action === 'register' && $failed ): ?>
			<div class="panel panel-danger card card-danger">
			    <div class="panel-heading card-header">
				  <?php
				  if ( $failed === 'invalid_character' ) {
				    _e( 'Username can only contain alphanumerical characters, "_" and "-". Please choose another username.', DT_TEXTDOMAIN );
				  } elseif ( $failed === 'username_exists' ) {
				    _e( 'Username already in use.', DT_TEXTDOMAIN );
				  } elseif ( $failed === 'email_exists' ) {
				    _e( 'E-mail already in use. Maybe you are already registered?', DT_TEXTDOMAIN );
				  } elseif ( $failed === 'empty' ) {
				    _e( 'All fields are required.', DT_TEXTDOMAIN );
				  } else {
				    _e( 'An error occurred while registering the new user. Please try again.', DT_TEXTDOMAIN );
				  }
				  ?>
			    </div>
			</div>
		    <?php endif; ?>
    		<div class="panel panel-info card card-info">
    		    <div class="panel-heading card-header">
				<?php _e( 'Register', DT_TEXTDOMAIN ); ?>
    		    </div>
    		    <div class="panel-body card-block">
    			  <form action="<?php echo site_url( 'wp-login.php?action=register', 'login_post' ) ?>" id="registerform" method="post">
    				<div class="form-group">
    				    <label for="user_login"><?php _e( 'Username', DT_TEXTDOMAIN ); ?></label>
    				    <input type="text" name="user_login" id="user_login" class="form-control user_login" value="">
    				</div>
    				<div class="form-group">
    				    <label for="user_email"><?php _e( 'E-mail', DT_TEXTDOMAIN ); ?></label>
    				    <input type="text" name="user_email" id="user_email" class="form-control user_email" value="">
    				</div>
    				<div class="form-group">
    				    <label for="confirm_email"><?php _e( 'Confirm E-mail', DT_TEXTDOMAIN ); ?></label>
    				    <input type="text" name="confirm_email" class="form-control" id="reg_passmail" value="">
    				    <p class="help-block"><?php _e( 'A password will be e-mailed to you.', DT_TEXTDOMAIN ); ?></p>
    				</div>
    				<div class="form-group">
    				    <input type="hidden" name="redirect_to" value="/login/?action=register&amp;success=1" />
    				    <input type="submit" name="wp-submit" id="wp-submit" class="button btn btn-primary" value="<?php _e( 'Register', DT_TEXTDOMAIN ); ?>" />
    				</div>
				    <?php do_action( 'register_form' ); ?>
    			  </form>
    		    </div>
    		</div>
    	  </div>
	    <?php } ?>
  	  <div class="panel-forgot col-lg-12">
		  <?php if ( $action === 'forgot' && $failed ): ?>
    		<div class="panel panel-danger card card-danger">
    		    <div class="panel-heading card-header">
				<?php
				if ( $failed === 'wrongkey' ) {
				  _e( 'The reset key is wrong or expired. Please check that you used the right reset link or request a new one.', DT_TEXTDOMAIN );
				} else {
				  _e( 'Sorry, we couldn\'t find any user with that username or email.', DT_TEXTDOMAIN );
				}
				?>
    		    </div>
    		</div>
		  <?php endif; ?>
  		<div class="panel panel-warning card card-warning" <?php
		  if ( $action === 'resetpass' ) {
		    echo 'style="display:none;"';
		  }
		  ?>>
  		    <div class="panel-heading card-header">
			    <?php _e( 'Password recovery', DT_TEXTDOMAIN ); ?>
  		    </div>
  		    <div class="panel-body card-block">
  			  <p class="help-block"><?php _e( 'Please enter your username or email address. You will receive a link to create a new password.', DT_TEXTDOMAIN ); ?></p>
  			  <form action="<?php echo site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ?>" method="post">
  				<div class="form-group">
  				    <label for="user_login"><?php _e( 'Username or E-mail:', DT_TEXTDOMAIN ); ?></label>
  				    <input type="text" name="user_login" class="form-control" value="">
  				</div>
  				<input type="hidden" name="redirect_to" value="/login/?action=forgot&amp;success=1">
  				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button btn btn-primary" value="<?php _e( 'Get New Password', DT_TEXTDOMAIN ); ?>" /></p>
  			  </form>
  		    </div>
  		</div>
		  <?php if ( $action === 'resetpass' ): ?>
		    <?php if ( $failed ): ?>
			<div class="panel panel-warning card card-warning">
			    <div class="panel-heading card-header">
				  <?php _e( 'The passwords don\'t match. Please try again.', DT_TEXTDOMAIN ); ?>
			    </div>
			</div>
		    <?php endif; ?>
    		<div class="panel panel-warning card card-warning">
    		    <div class="panel-heading card-header">
				<?php _e( 'Reset password', DT_TEXTDOMAIN ); ?>
    		    </div>
    		    <div class="panel-body card-block">
    			  <p><?php _e( 'Create a new password for your account.', DT_TEXTDOMAIN ); ?></p>
    			  <form action="<?php echo site_url( 'wp-login.php?action=resetpass', 'login_post' ) ?>" method="post">
    				<div class="form-group">
    				    <label for="pass1"><?php _e( 'New Password', DT_TEXTDOMAIN ); ?></label>
    				    <input class="form-control" name="pass1" type="password">
    				</div>
    				<div class="form-group">
    				    <label for="pass2"><?php _e( 'Confirm Password', DT_TEXTDOMAIN ); ?></label>
    				    <input class="form-control" name="pass2" type="password">
    				</div>
    				<input type="hidden" name="redirect_to" value="/login/?action=resetpass&amp;success=1">
				    <?php
				    $rp_key = '';
				    $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				    if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
					list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
				    }
				    ?>
    				<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>">
    				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button btn btn-warning" value="<?php _e( 'Get New Password', DT_TEXTDOMAIN ); ?>" /></p>
    			  </form>
    		    </div>
			<?php endif; ?>
		    <?php endif; ?>
		    </article>
		</div>
