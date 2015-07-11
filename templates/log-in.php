<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$action = !empty( $_GET[ 'action' ] ) && ($_GET[ 'action' ] === 'register' || $_GET[ 'action' ] === 'forgot' || $_GET[ 'action' ] === 'resetpass') ? $_GET[ 'action' ] : 'login';
$success = !empty( $_GET[ 'success' ] );
$failed = !empty( $_GET[ 'failed' ] ) ? $_GET[ 'failed' ] : false;
$plugin = DaTask::get_instance();
?>

<div id="content-main" class="main" role="main">
    <article id="page-<?php the_ID(); ?>" class="hentry">
	<?php if ( $action === 'register' && $success ): ?>
		<div class="panel panel-primary">
		    <div class="panel-heading">
			<h1><?php _e( 'Success!', $plugin->get_plugin_slug() ); ?></h1>
		    </div>
		    <div class="panel-body">
			<?php _e( 'Check your email for the password and then return to log in.', $plugin->get_plugin_slug() ); ?>
		    </div>
		</div>
	<?php elseif ( $action === 'forgot' && $success ): ?>
		<div class="panel panel-primary">
		    <div class="panel-heading">
			<h1><?php _e( 'Password recovery', $plugin->get_plugin_slug() ); ?></h1>
		    </div>
		    <div class="panel-body">
			<?php _e( 'Check your email for the instructions to get a new password.', $plugin->get_plugin_slug() ); ?>
		    </div>
		</div>
	<?php elseif ( $action === 'resetpass' && $success ): ?>
		<div class="panel panel-success">
		    <div class="panel-heading">
			<h1><?php _e( 'Password reset', $plugin->get_plugin_slug() ); ?></h1>
		    </div>
		    <div class="panel-body">
			<?php _e( 'Your password has been updated. <a href="/login/">Proceed to login</a>.', $plugin->get_plugin_slug() ); ?>
		    </div>
		</div>
	<?php else: ?>
		<div class="panel-login col-lg-6" <?php
		if ( $action === 'resetpass' ) {
			echo 'style="display:none;"';
		}
		?>>
			 <?php if ( $action === 'login' && $failed ): ?>
			    <div class="panel panel-danger">
				<div class="panel-heading">
				    <?php
				    if ( $failed ) {
					    _e( 'Invalid username or password. Please try again.', $plugin->get_plugin_slug() );
				    }
				    ?>
				</div>
			    </div>
		    <?php endif; ?>
		    <div class="panel panel-info">
			<div class="panel-heading">
			    <?php _e( 'Login', $plugin->get_plugin_slug() ); ?>
			</div>
			<div class="panel-body">
			    <form name="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
				<div class="form-group">
				    <label for="user_login"><?php _e( 'Username' ) ?></label>
				    <input type="text" name="log" class="form-control" value="" size="20" />
				</div>
				<div class="form-group">
				    <label for="user_pass"><?php _e( 'Password' ) ?></label>
				    <input type="password" name="pwd" class="form-control" value="" size="20" />
				</div>
				<div class="form-group">
				    <input type="submit" name="wp-submit" class="button btn btn-primary" value="<?php _e( 'Login' ); ?>" />
				</div>
				<?php
				do_action( 'login_form' );
				?>
			    </form>
			</div>
		    </div>
		</div>
		<div class="panel-register col-lg-6" <?php
		if ( $action === 'resetpass' ) {
			echo 'style="display:none;"';
		}
		?>>
			 <?php if ( $action === 'register' && $failed ): ?>
			    <div class="panel panel-danger">
				<div class="panel-heading">
				    <?php
				    if ( $failed === 'invalid_character' ) {
					    _e( 'Username can only contain alphanumerical characters, "_" and "-". Please choose another username.', $plugin->get_plugin_slug() );
				    } elseif ( $failed === 'username_exists' ) {
					    _e( 'Username already in use.', $plugin->get_plugin_slug() );
				    } elseif ( $failed === 'email_exists' ) {
					    _e( 'E-mail already in use. Maybe you are already registered?', $plugin->get_plugin_slug() );
				    } elseif ( $failed === 'empty' ) {
					    _e( 'All fields are required.', $plugin->get_plugin_slug() );
				    } else {
					    _e( 'An error occurred while registering the new user. Please try again.', $plugin->get_plugin_slug() );
				    }
				    ?>
				</div>
			    </div>
		    <?php endif; ?>
		    <div class="panel panel-info">
			<div class="panel-heading">
			    <?php _e( 'Register', $plugin->get_plugin_slug() ); ?>
			</div>
			<div class="panel-body">
			    <form action="<?php echo site_url( 'wp-login.php?action=register', 'login_post' ) ?>" method="post">
				<div class="form-group">
				    <label for="user_login"><?php _e( 'Username', $plugin->get_plugin_slug() ); ?></label>
				    <input type="text" name="user_login" class="form-control" value="">
				</div>
				<div class="form-group">
				    <label for="user_email"><?php _e( 'E-mail', $plugin->get_plugin_slug() ); ?></label>
				    <input type="text" name="user_email" class="form-control" value="">
				</div>
				<div class="form-group">
				    <label for="confirm_email"><?php _e( 'Confirm E-mail', $plugin->get_plugin_slug() ); ?></label>
				    <input type="text" name="confirm_email" class="form-control" value="">
				    <p class="help-block"><?php _e( 'A password will be e-mailed to you.', $plugin->get_plugin_slug() ); ?></p>
				</div>
				<div class="form-group">
				    <input type="hidden" name="redirect_to" value="/login/?action=register&amp;success=1" />
				    <input type="submit" name="wp-submit" id="wp-submit" class="button btn btn-primary" value="<?php _e( 'Register', $plugin->get_plugin_slug() ); ?>" />
				</div>
			    </form>
			</div>
		    </div>
		</div>
		<div class="panel-forgot col-lg-12">
		    <?php if ( $action === 'forgot' && $failed ): ?>
			    <div class="panel panel-danger">
				<div class="panel-heading">
				    <?php
				    if ( $failed === 'wrongkey' ) {
					    _e( 'The reset key is wrong or expired. Please check that you used the right reset link or request a new one.', $plugin->get_plugin_slug() );
				    } else {
					    _e( 'Sorry, we couldn\'t find any user with that username or email.', $plugin->get_plugin_slug() );
				    }
				    ?>
				</div>
			    </div>
		    <?php endif; ?>
		    <div class="panel panel-warning" <?php
		    if ( $action === 'resetpass' ) {
			    echo 'style="display:none;"';
		    }
		    ?>>
			<div class="panel-heading">
			    <?php _e( 'Password recovery', $plugin->get_plugin_slug() ); ?>
			</div>
			<div class="panel-body">
			    <p class="help-block"><?php _e( 'Please enter your username or email address. You will receive a link to create a new password.', $plugin->get_plugin_slug() ); ?></p>
			    <form action="<?php echo site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ?>" method="post">
				<div class="form-group">
				    <label for="user_login"><?php _e( 'Username or E-mail:', $plugin->get_plugin_slug() ); ?></label>
				    <input type="text" name="user_login" class="form-control" value="">
				</div>
				<input type="hidden" name="redirect_to" value="/login/?action=forgot&amp;success=1">
				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button btn btn-primary" value="<?php _e( 'Get New Password', $plugin->get_plugin_slug() ); ?>" /></p>
			    </form>
			</div>
		    </div>
		    <?php if ( $action === 'resetpass' ): ?>
			    <?php if ( $failed ): ?>
				    <div class="panel panel-warning">
					<div class="panel-heading">
					    <?php _e( 'The passwords don\'t match. Please try again.', $plugin->get_plugin_slug() ); ?>
					</div>
				    </div>
			    <?php endif; ?>
			    <div class="panel panel-warning">
				<div class="panel-heading">
				    <?php _e( 'Reset password', $plugin->get_plugin_slug() ); ?>
				</div>
				<div class="panel-body">
				    <p><?php _e( 'Create a new password for your account.', $plugin->get_plugin_slug() ); ?></p>
				    <form action="<?php echo site_url( 'wp-login.php?action=resetpass', 'login_post' ) ?>" method="post">
					<div class="form-group">
					    <label for="pass1"><?php _e( 'New Password', $plugin->get_plugin_slug() ); ?></label>
					    <input class="form-control" name="pass1" type="password">
					</div>
					<div class="form-group">
					    <label for="pass2"><?php _e( 'Confirm Password', $plugin->get_plugin_slug() ); ?></label>
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
					<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button btn btn-warning" value="<?php _e( 'Get New Password', $plugin->get_plugin_slug() ); ?>" /></p>
				    </form>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		</article>
	    </div>
