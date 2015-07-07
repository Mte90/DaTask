<?php

/**
 * WO_Frontend_Login
 * Frontend Login system
 *
 * @package   Wp_Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class WO_Frontend_Login {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		$plugin = Wp_Oneanddone::get_instance();
		new Fake_Page(
			array(
		    'slug' => 'login',
		    'post_title' => __( 'Login', $plugin->get_plugin_slug() ),
		    'post_content' => 'content'
			)
		);
		new Fake_Page(
			array(
		    'slug' => 'logout',
		    'post_title' => __( 'Logout', $plugin->get_plugin_slug() ),
		    'post_content' => 'content'
			)
		);
		add_action( 'login_init', array( $this, 'frontend_login' ) );
		add_action( 'template_redirect', array( $this, 'frontend_login_redirect' ) );
		add_action( 'lostpassword_post', array( $this, 'frontend_reset_password' ) );
		add_action( 'validate_password_reset', array( $this, 'frontend_validate_password_reset', 10, 2 ) );
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'prevent_access_backend' ) );
		add_filter( 'registration_errors', array( $this, 'registration_redirect' ), 10, 3 );
		add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
		add_filter( 'the_content', array( $this, 'login_page' ) );
		add_filter( 'wp_nav_menu_objects', array( $this, 'login_to_logout' ) );
	}

	/**
	 * Frontend login
	 *
	 * @since    1.0.0
	 */
	public function frontend_login() {
		$action = isset( $_REQUEST[ 'action' ] ) ? $_REQUEST[ 'action' ] : 'login';
		if ( isset( $_POST[ 'wp-submit' ] ) ) {
			$action = 'post-data';
		} else if ( isset( $_GET[ 'reauth' ] ) ) {
			$action = 'reauth';
		}
		//redirect to change password form
		if ( $action == 'rp' || $action == 'resetpass' ) {
			if ( isset( $_GET[ 'key' ] ) && isset( $_GET[ 'login' ] ) ) {
				$rp_path = wp_unslash( '/login/' );
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				$value = sprintf( '%s:%s', wp_unslash( $_GET[ 'login' ] ), wp_unslash( $_GET[ 'key' ] ) );
				setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
			}

			wp_redirect( home_url( '/login/?action=resetpass' ) );
			exit;
		}
		//redirect from wrong key when resetting password
		if ( $action == 'lostpassword' && isset( $_GET[ 'error' ] ) && ( $_GET[ 'error' ] == 'expiredkey' || $_GET[ 'error' ] == 'invalidkey' ) ) {
			wp_redirect( home_url( '/login/?action=forgot&failed=wrongkey' ) );
			exit;
		}
		if (
			$action == 'post-data' || //don't mess with POST requests
			$action == 'reauth' || //need to reauthorize
			$action == 'logout'       //user is logging out
		) {
			return;
		}
		wp_redirect( home_url( '/login/' ) );
		exit;
	}

	/**
	 * Frontend redirect when logged/not logged
	 *
	 * @since    1.0.0
	 */
	public function frontend_login_redirect() {
		if ( is_page( 'login' ) && is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			wp_redirect( home_url( '/member/' . $current_user->user_login ) );
			exit();
		} elseif ( is_page( 'logout' ) && is_user_logged_in() ) {
			wp_logout();
			wp_redirect( home_url() );
			exit();
		}
		global $wp_query;
		if ( array_key_exists( 'member', $wp_query->query_vars ) && !is_user_logged_in() ) {
			wp_redirect( home_url( '/login/' ) );
			exit();
		}
	}

	/**
	 *  Prevent access in administration for not admin user
	 *
	 * @since    1.0.0
	 */
	public function prevent_access_backend() {
		if ( current_user_can( 'subscriber' ) && !defined( 'DOING_AJAX' ) ) {
			$current_user = wp_get_current_user();
			wp_redirect( home_url( '/member/' . $current_user->user_login ) );
			exit;
		}
	}

	/**
	 *  Redirect on registration
	 *
	 * @since    1.0.0
	 */
	public function registration_redirect( $errors, $sanitized_user_login, $user_email ) {
		//don't lose your time with spammers, redirect them to a success page
		if ( !isset( $_POST[ 'confirm_email' ] ) || $_POST[ 'confirm_email' ] !== '' ) {
			wp_redirect( home_url( '/login/' ) . '?action=register&success=1' );
			exit;
		}
		if ( !empty( $errors->errors ) ) {
			if ( isset( $errors->errors[ 'username_exists' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=username_exists' );
			} else if ( isset( $errors->errors[ 'email_exists' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=email_exists' );
			} else if ( isset( $errors->errors[ 'invalid_username' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=invalid_username' );
			} else if ( isset( $errors->errors[ 'invalid_email' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=invalid_email' );
			} else if ( isset( $errors->errors[ 'empty_username' ] ) || isset( $errors->errors[ 'empty_email' ] ) ) {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=empty' );
			} else {
				wp_redirect( home_url( '/login/' ) . '?action=register&failed=generic' );
			}
			exit;
		}
		return $errors;
	}

	/**
	 * Redirect after login
	 *
	 * @since    1.0.0
	 */
	public function login_redirect( $redirect_to, $url, $user ) {
		if ( !isset( $user->errors ) ) {
			return $redirect_to;
		}
		wp_redirect( home_url( '/login/' ) . '?action=login&failed=1' );
		exit;
	}

	/**
	 * Reset password in the frontend
	 *
	 * @since    1.0.0
	 */
	public function frontend_reset_password() {
		$user_data = '';
		if ( !empty( $_POST[ 'user_login' ] ) ) {
			if ( strpos( $_POST[ 'user_login' ], '@' ) ) {
				$user_data = get_user_by( 'email', trim( $_POST[ 'user_login' ] ) );
			} else {
				$user_data = get_user_by( 'login', trim( $_POST[ 'user_login' ] ) );
			}
		}
		if ( empty( $user_data ) ) {
			wp_redirect( home_url( '/login/' ) . '?action=forgot&failed=1' );
			exit;
		}
	}

	/**
	 * Validate the password in frontend
	 *
	 * @since    1.0.0
	 */
	public function frontend_validate_password_reset( $errors, $user ) {
		//passwords don't match
		if ( $errors->get_error_code() ) {
			wp_redirect( home_url( '/login/?action=resetpass&failed=nomatch' ) );
			exit;
		}
		//wp-login already checked if the password is valid, so no further check is needed
		if ( !empty( $_POST[ 'pass1' ] ) ) {
			reset_password( $user, $_POST[ 'pass1' ] );
			wp_redirect( home_url( '/login/?action=resetpass&success=1' ) );
			exit;
		}
		//redirect to change password form
		wp_redirect( home_url( '/login/?action=resetpass' ) );
		exit;
	}

	/**
	 * Load login page
	 *
	 * @since    1.0.0
	 */
	public function login_page( $content ) {
		if ( is_page( 'login' ) ) {
			wo_get_template_part( 'log', 'in', true );
		} else {
			return $content;
		}
	}

	/**
	 * Hide the admin bar in frontend for not admin user
	 *
	 * @since    1.0.0
	 */
	public function remove_admin_bar() {
		if ( !current_user_can( 'administrator' ) && !is_admin() ) {
			show_admin_bar( false );
		}
	}

	/**
	 * Transform Login to Logout for logged users
	 *
	 * @return  $items
	 * @since    1.0.0
	 */
	function login_to_logout( $items ) {
		if ( is_user_logged_in() ) {
			foreach ( $items as $page=> $value ) {
				if ( $items[ $page ]->post_name === 'login' ) {
					$items[ $page ]->post_name = 'logout';
					$items[ $page ]->post_title = 'Logout';
					$items[ $page ]->title = 'Logout';
					$items[ $page ]->url = get_site_url() . '/logout';
				}
			}
		}
		return $items;
	}

}

new WO_Frontend_Login();
