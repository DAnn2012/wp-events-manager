<?php
/**
 * Event shortcode wrapper.
 *
 * @package WPEMS/ShortCodes
 */

namespace WPEMS\ShortCodes;

defined( 'ABSPATH' ) || exit;

class EventShortCodes {

	/**
	 * Register shortcodes.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'tp_event_shortcode_wrapper_start', array( '\WPEMS_Shortcodes', 'shortcode_wrapper_start' ) );
		add_action( 'tp_event_shortcode_wrapper_end', array( '\WPEMS_Shortcodes', 'shortcode_wrapper_end' ) );

		$shortcodes = array(
			'list_event'      => array( __CLASS__, 'list_event' ),
			'register'        => array( __CLASS__, 'register' ),
			'login'           => array( __CLASS__, 'login' ),
			'forgot_password' => array( __CLASS__, 'forgot_password' ),
			'reset_password'  => array( __CLASS__, 'reset_password' ),
			'account'         => array( __CLASS__, 'account' ),
			'countdown'       => array( __CLASS__, 'countdown' ),
		);

		foreach ( $shortcodes as $shortcode => $callback ) {
			add_shortcode( apply_filters( "wp_event_{$shortcode}_shortcode_tag", 'wp_event_' . $shortcode ), $callback );
		}

		add_action( 'template_redirect', array( '\WPEMS_Shortcodes', 'auto_shortcode' ) );
	}

	/**
	 * Render event list shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function list_event( $atts ): string {
		return \WPEMS_Shortcodes::list_event( $atts );
	}

	/**
	 * Render register shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function register( $atts ): string {
		if ( ! wpems_get_page_id( 'register' ) ) {
			return '';
		}

		if ( ! get_option( 'users_can_register' ) ) {
			return \WPEMS_Shortcodes::render( 'user-register', 'user-cannot-register.php' );
		}

		$registered = self::request_value( 'registered', 'email' );
		if ( $registered ) {
			$user = get_user_by( 'email', $registered );
			if ( $user && $user->ID ) {
				wp_new_user_notification( $user->ID, null, 'user' );

				return \WPEMS_Shortcodes::render( 'user-register', 'register-completed.php' );
			}

			return \WPEMS_Shortcodes::render( 'user-register', 'register-error.php' );
		}

		if ( ! is_user_logged_in() ) {
			return \WPEMS_Shortcodes::render( 'user-register', 'form-register.php' );
		}

		return '';
	}

	/**
	 * Render login shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function login( $atts ): string {
		return \WPEMS_Shortcodes::login( $atts );
	}

	/**
	 * Render forgot password shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function forgot_password( $atts ): string {
		if ( ! wpems_get_page_id( 'forgot_password' ) ) {
			return '';
		}

		$checkemail = self::request_value( 'checkemail' );
		if ( 'confirm' === $checkemail ) {
			wpems_add_notice( 'success', __( 'Check your email for a link to reset your password.', 'wp-events-manager' ) );

			return '';
		}

		return \WPEMS_Shortcodes::render( 'forgot-password', 'forgot-password.php' );
	}

	/**
	 * Render reset password shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function reset_password( $atts ): string {
		if ( ! wpems_get_page_id( 'reset_password' ) ) {
			return '';
		}

		$atts = wp_parse_args(
			(array) $atts,
			array(
				'key'   => self::request_value( 'key' ),
				'login' => self::request_value( 'login' ),
			)
		);

		$atts = wp_parse_args(
			$atts,
			array(
				'user_login'  => '',
				'redirect_to' => '',
				'checkemail'  => 'confirm' === self::request_value( 'checkemail' ),
			)
		);

		if ( $atts['checkemail'] ) {
			wpems_add_notice( 'success', __( 'Check your email for a link to reset your password.', 'wp-events-manager' ) );
		}

		return \WPEMS_Shortcodes::render( 'reset-password', 'reset-password.php', array( 'atts' => $atts ) );
	}

	/**
	 * Render account shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function account( $atts ): string {
		return \WPEMS_Shortcodes::account( $atts );
	}

	/**
	 * Render countdown shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function countdown( $atts ): string {
		return \WPEMS_Shortcodes::countdown( $atts );
	}

	/**
	 * Get sanitized request value.
	 *
	 * @param string $key  Request key.
	 * @param string $type Value type.
	 *
	 * @return string
	 */
	private static function request_value( string $key, string $type = 'text' ): string {
		if ( ! isset( $_REQUEST[ $key ] ) ) {
			return '';
		}

		$value = wp_unslash( $_REQUEST[ $key ] );
		if ( 'email' === $type ) {
			return sanitize_email( $value );
		}

		return sanitize_text_field( $value );
	}
}
