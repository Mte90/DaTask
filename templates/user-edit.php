<?php
//Based on Frontend Edit Profile Plugin
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$plugin = DaTask::get_instance();
get_header();

$current_user = wp_get_current_user();
$profileuser = get_user_to_edit( $current_user->ID );
?>
<div id="content-main" class="main" role="main">
    <p><a href="<?php echo home_url( '/member/' . $profileuser->user_login ); ?>"><?php _e( 'View Public Profile', $plugin->get_plugin_slug() ); ?></a></p>
    <form id="your-profile" method="post"<?php do_action( 'user_edit_form_tag' ); ?>>
	<?php wp_nonce_field( 'update-user_' . $current_user->ID ) ?>
	<?php
	do_action( 'profile_personal_options', $profileuser );
	do_action( 'personal_options', $profileuser );
	?>
	<table class="form-table">
	    <tr>
		<th><label for="user_login"><?php _e( 'Username' ); ?></label></th>
		<td><input type="text" name="user_login" id="user_login" value="<?php echo esc_attr( $profileuser->user_login ); ?>" disabled="disabled" class="form-control" /><em><span class="description"><?php _e( 'Usernames cannot be changed.' ); ?></span></em></td>
	    </tr>
	    <tr>
		<th><label for="first_name"><?php _e( 'First Name' ) ?></label></th>
		<td><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ) ?>" class="form-control" /></td>
	    </tr>

	    <tr>
		<th><label for="last_name"><?php _e( 'Last Name' ) ?></label></th>
		<td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ) ?>" class="form-control" /></td>
	    </tr>

	    <tr>
		<th><label for="nickname"><?php _e( 'Nickname' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
		<td><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="form-control" /></td>
	    </tr>

	    <tr>
		<th><label for="display_name"><?php _e( 'Display to Public as', $plugin->get_plugin_slug() ) ?></label></th>
		<td>
		    <select name="display_name" id="display_name" class="form-control">
			<?php
			$public_display = array();
			$public_display[ 'display_username' ] = $profileuser->user_login;
			$public_display[ 'display_nickname' ] = $profileuser->nickname;
			if ( !empty( $profileuser->first_name ) ) {
				$public_display[ 'display_firstname' ] = $profileuser->first_name;
			}
			if ( !empty( $profileuser->last_name ) ) {
				$public_display[ 'display_lastname' ] = $profileuser->last_name;
			}
			if ( !empty( $profileuser->first_name ) && !empty( $profileuser->last_name ) ) {
				$public_display[ 'display_firstlast' ] = $profileuser->first_name . ' ' . $profileuser->last_name;
				$public_display[ 'display_lastfirst' ] = $profileuser->last_name . ' ' . $profileuser->first_name;
			}
			if ( !in_array( $profileuser->display_name, $public_display ) ) { // Only add this if it isn't duplicated elsewhere
				$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;
			}
			$public_display = array_map( 'trim', $public_display );
			$public_display = array_unique( $public_display );
			foreach ( $public_display as $id => $item ) {
				?>
				<option id="<?php echo $id; ?>" value="<?php echo esc_attr( $item ); ?>"<?php selected( $profileuser->display_name, $item ); ?>><?php echo $item; ?></option>
				<?php
			}
			?>
		    </select>
		</td>
	    </tr>
	    <tr>
		<th><label for="email"><?php _e( 'E-mail' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
		<td><input type="text" name="email" id="email" value="<?php echo esc_attr( $profileuser->user_email ) ?>" class="form-control" />
		</td>
	    </tr>

	    <tr>
		<th><label for="url"><?php _e( 'Website' ) ?></label></th>
		<td><input type="text" name="url" id="url" value="<?php echo esc_attr( $profileuser->user_url ) ?>" class="form-control" /></td>
	    </tr>
	    <tr>
		<th><label for="description"><?php _e( 'Biographical Info' ); ?></label></th>
		<td><textarea name="description" id="description" rows="5" cols="30" class="form-control"><?php echo esc_html( $profileuser->description ); ?></textarea><em><span class="description"><?php _e( 'Share a little biographical information to fill out your profile. This may be shown publicly.' ); ?></span></em></td>
	    </tr>
	    <tr>
		<th><label for="pass1"><?php _e( 'New Password' ); ?></label><br /><span class="description"><small><?php _e( "If you would like to change the password type a new one. Otherwise leave this blank." ); ?></small></span></th>
		<td>
		    <input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" class="form-control" />
		    <input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" class="form-control" />
		    <em><span class="description"><?php _e( "Type your new password again." ); ?></span></em>
		    <div id="pass-strength-result"><?php _e( 'Strength indicator' ); ?></div>
		    <p class="description indicator-hint">
			<?php echo wp_get_password_hint(); ?>
		    </p>
		</td>
	    </tr>
	</table>
	<p class="submit">
	    <input type="hidden" name="action" value="update" />
	    <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>" />
	    <input type="submit" class="btn btn-primary" value="<?php _e( 'Update Profile' ); ?>" name="submit" />
	</p>
    </form>
</div>
<?php wp_print_scripts( 'user-profile' ); ?>