<?php

/**
 * Voce Post Expiration
 *
 * @author Kevin Langley
 */

if( ! class_exists( 'Voce_Post_Expiration' ) ):

class Voce_Post_Expiration {

	const POST_TYPE_SUPPORT_NAME = 'post-expiration';

	public static function initialize() {
		add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'post_expiration_meta' ) );
		add_action( 'admin_print_scripts-post.php', array( __CLASS__, 'enqueue_admin_scripts' ) );
		add_action( 'admin_print_scripts-post-new.php', array( __CLASS__, 'enqueue_admin_scripts' ) );
		add_action( 'save_post', array( __CLASS__, 'save_expiration_date_meta' ), 10, 2 );
		add_action( 'expire_post', array( __CLASS__, 'expire_post' ) );

		if ( !wp_next_scheduled( 'expired_posts_cron' ) )
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'expired_posts_cron' );
	}

	private static function is_enabled( $post_type = null ) {
		if ( !$post_type ) {
			global $current_screen;
			if ( isset( $current_screen->post_type ) ) {
				$post_type = $current_screen->post_type;
			} else {
				return false;
			}
		}

		return post_type_supports( $post_type, self::POST_TYPE_SUPPORT_NAME );
	}

	public static function enqueue_admin_scripts() {
		if ( !self::is_enabled() ) {
			return;
		}
		// js to hide the Add New menu for this post type
		wp_enqueue_script( 'post-expiration', plugins_url('js/voce-post-expiration.js', __FILE__), array( 'jquery' ), SCRIPT_VERSION, true );
	}

	/**
	 * post_expiration_meta
	 *
	 * create post meta box fields for expiration scheduling and current expiration status
	 */
	public static function post_expiration_meta() {
		if ( !self::is_enabled() ) {
			return;
		}
		global $post;
		$datef = __( 'M j, Y @ G:i' );

		$expiration = get_post_meta( $post->ID, 'post_expiration', true );
		$exp_epoch = empty( $expiration ) ? false : strtotime( get_date_from_gmt( $expiration ) );
		$time_adj = current_time( 'timestamp', 1 );
		//date labeling
		if ( $exp_epoch && ($time_adj > $exp_epoch) ) {
			$stamp = __( 'Expired on: <b>%1$s</b>' );
			$date = date_i18n( $datef, $exp_epoch );
		} elseif ( $exp_epoch ) {
			$stamp = __( 'Expires on: <b>%1$s</b>' );
			$date = date_i18n( $datef, $exp_epoch );
		} else {
			$stamp = __( 'No Expiration Set' );
			$date = date_i18n( $datef, strtotime( current_time( 'mysql' ) ) );
		}
		?>
		<div class="misc-pub-section">
			<span id="expire-datestamp" style="background-image: url(<?php echo esc_url( plugins_url('img/expire-date-button.gif', __FILE__) ); ?>); background-repeat: no-repeat; background-position: left top; padding-left: 18px;">
				<?php printf( $stamp, $date ); ?>
			</span>
			<a href="#edit_expire-datestamp" class="edit-expire-datestamp hide-if-no-js" tabindex='4'><?php _e( 'Edit' ) ?></a>
			<div id="expire-datestamp-value" class="hide-if-js"><?php self::expiration_time(); ?></div>
		</div>
		<?php
	}

	public static function save_expiration_date_meta( $post_id, $post ) {

		if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) )
			return;

		if ( isset( $_POST['update_expire_nonce'] ) && wp_verify_nonce( $_POST['update_expire_nonce'], 'update_expire' ) ) {
			$defaults = array(
				'expire_year' => '',
				'expire_month' => '',
				'expire_day' => '',
				'expire_hour' => '',
				'expire_minute' => '',
			);
			$posted_data = wp_parse_args( $_POST, $defaults );

			// validate numeric values
			foreach ( array_keys( $defaults ) as $key ) {
				if ( 'expire_month' != $key && !is_numeric( $posted_data[$key] ) && $posted_data[$key] != '' ) {
					return;
				}
			}


			$expire_year = $posted_data['expire_year'];
			$expire_month = $posted_data['expire_month'];
			$expire_day = $posted_data['expire_day'];
			$expire_hour = $posted_data['expire_hour'];
			$expire_minute = $posted_data['expire_minute'];
			$expire_second = '00';

			if ( $expire_year !== '' && $expire_month !== '' ) {
				$expire_day = ($expire_day > 31 ) ? 31 : $expire_day;
				$expire_hour = ($expire_hour > 23 ) ? (int) $expire_hour - 24 : $expire_hour;
				$expire_minute = ($expire_minute > 59 ) ? $expire_minute - 60 : $expire_minute;
				$expire_second = ($expire_second > 59 ) ? $expire_second - 60 : $expire_second;
				$new_expiration = "$expire_year-$expire_month-$expire_day $expire_hour:$expire_minute:$expire_second";
			} else {
				$new_expiration = '';
			}

			$cur_expiration = get_post_meta( $post->ID, 'post_expiration', true );

			if( $new_expiration && strtotime( $post->post_date ) > strtotime( $new_expiration ) )
				return;

			if ( $cur_expiration != $new_expiration ) {
				wp_unschedule_event( $cur_expiration, 'expire_post', array( $post_id ) );
				if ( $new_expiration !== '' ) {
					$exp_gmt = get_gmt_from_date( $new_expiration );
					wp_schedule_single_event( strtotime( $exp_gmt ), 'expire_post', array( $post_id ) );
					update_post_meta( $post_id, 'post_expiration', $exp_gmt );
					$val1 = strtotime( $exp_gmt );
					$val2 = current_time( 'timestamp', true );
					if ( $val1 < $val2 && $post->post_status != 'draft' ){
						self::expire_post($post_id);
					} else {
						wp_schedule_single_event( strtotime( $exp_gmt ), 'expire_post', array( $post_id ) );
					}
				} else {
					delete_post_meta( $post_id, 'post_expiration' );
				}
			}
		}
	}

	public static function expire_post( $post_id ) {
		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
		update_post_meta($post_id, 'post_expired', true);
		do_action('post_expired', $post_id);
	}

	public static function expiration_time() {
		global $wp_locale, $post;

		$expire_date = get_post_meta( $post->ID, 'post_expiration', true );
		if(!empty($expire_date)){
			$gmt_time = strtotime(get_date_from_gmt($expire_date));
			$set = true;
		} else {
			$gmt_time = current_time('timestamp');
			$set = false;
		}

		$day = date('d', $gmt_time);
		$month = date('m', $gmt_time);
		$year = date('Y', $gmt_time);
		$hour = date('H', $gmt_time);
		$minute = date('i', $gmt_time);
		$second = '00';

		$month_input = '<select id="expire_month" name="expire_month" >';
		for ( $i = 1; $i < 13; $i = $i + 1 ){
			$selected = selected($i, intval($month), false);
			$month_input .= sprintf('<option value="%s" %s>%s</option>', esc_attr( zeroise( $i, 2 ) ), $selected, esc_html($wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) )) );
		}
		$month_input .= '</select>';

		$day_input = sprintf('<input type="text" id="expire_day" name="expire_day" value="%1$s" size="1" maxlength="2" autocomplete="off" data-original-value="%1$s" />', esc_attr($day) );
		$year_input = sprintf('<input type="text" id="expire_year" name="expire_year" value="%1$s" size="2" maxlength="4" autocomplete="off" data-original-value="%1$s" />', esc_attr($year));
		$hour_input = sprintf('<input type="text" id="expire_hour" name="expire_hour" value="%1$s" size="1" maxlength="2" autocomplete="off" data-original-value="%1$s" />', esc_attr($hour));
		$minute_input = sprintf('<input type="text" id="expire_minute" name="expire_minute" value="%1$s" size="1" maxlength="2" autocomplete="off" data-original-value="%1$s" />', esc_attr($minute));
		$second_input = sprintf('<input type="hidden" id="expire_second" name="expire_second" value="%1$s" data-original-value="%1$s" />', esc_attr($second));

		printf( __( '<div class="expire-datestamp-wrap" data-set="%s">%s %s, %s @ %s : %s %s</div>' ), ($set) ? 'true' : 'false', $month_input, $day_input, $year_input, $hour_input, $minute_input, $second_input );
		wp_nonce_field( 'update_expire', 'update_expire_nonce' );
		?>

		<p>
			<a href="#edit_expire-datestamp" class="save-expire-datestamp hide-if-no-js button"><?php _e( 'OK' ); ?></a>
			<a href="#edit_expire-datestamp" class="cancel-expire-datestamp hide-if-no-js"><?php _e( 'Cancel' ); ?></a>
			<a href="#remove_expire-datestamp" class="remove-expire-datestamp hide-if-no-js"><?php _e( 'Remove Expiration' ); ?></a>
		</p>
		<?php
	}

	public static function _expired_posts_cron() {
		$expired_posts = get_posts( array(
			'fields' => 'ids',
			'post_status' => array( 'inherit', 'publish' ),
			'meta_query' => array(
				array(
					'key' => 'post_expiration',
					'value' => current_time( 'mysql' ),
					'compare' => '<',
					'type' => 'DATE'
				)
			)
			) );
		foreach ( $expired_posts as $exp_post )
			self::expire_post($exp_post);
	}
}

add_action( 'init', array( 'Voce_Post_Expiration', 'initialize' ) );

endif;