<?php
/**
 * WP Events Manager Admin Metabox Booking class
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Class
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

class WPEMS_Admin_Metabox_Booking {

	public static function save( $post_id, $posted ) {
		if ( ! empty( $posted['booking-status'] ) ) {
			remove_action( 'tp_event_process_update_event_auth_book_meta', array( __CLASS__, 'save' ), 10 );
			$booking = \WPEMS\Models\BookingPostModel::find( absint( $post_id ) );
			if ( ! $booking ) {
				return;
			}

			$status           = sanitize_key( $posted['booking-status'] );
			$allowed_statuses = array_keys( wpems_get_payment_status() );
			if ( in_array( $status, $allowed_statuses, true ) ) {
				$booking->update_status( $status );
			}
			add_action( 'tp_event_process_update_event_auth_book_meta', array( __CLASS__, 'save' ), 10, 2 );
		}
		if ( ! empty( $posted['booking-notes'] ) ) {
			update_post_meta( $post_id, 'ea_booking_note', sanitize_textarea_field( $posted['booking-notes'] ) );
		}
	}

	public static function render() {
		require_once WPEMS_INC . 'admin/views/metaboxes/booking-details.php';
	}

	public static function side() {
		require_once WPEMS_INC . 'admin/views/metaboxes/booking-actions.php';
	}
}
