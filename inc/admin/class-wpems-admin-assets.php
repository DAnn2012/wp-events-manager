<?php
/**
 * WP Events Manager Admin Assets class
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Class
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

class WPEMS_Admin_Assets {

	/**
	 * Register scripts
	 *
	 * @since 1.4.1.4
	 */
	public static function init() {
		add_action( 'tp_event_before_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );
	}

	/**
	 * Register scripts
	 *
	 * @param type $hook
	 */
	public static function register_scripts( $hook ) {

		WPEMS_Assets::register_script( 'wpems-admin-js', WPEMS_ASSETS_URI . '/dist/js/admin/admin-events.js' );
		WPEMS_Assets::localize_script(
			'wpems-admin-js',
			'WPEMS_ADMIN',
			array(
				'event_remove_notice_nonce' => wp_create_nonce( 'event_remove_notice' ),
			)
		);
		WPEMS_Assets::register_style( 'wpems-admin-css', WPEMS_ASSETS_URI . '/css/admin/admin.css' );
	}
}

WPEMS_Admin_Assets::init();
