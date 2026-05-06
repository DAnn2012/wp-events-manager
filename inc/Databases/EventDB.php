<?php
/**
 * Event database queries.
 *
 * @package WPEMS/Databases
 */

namespace WPEMS\Databases;

defined( 'ABSPATH' ) || exit;

class EventDB {

	/**
	 * Singleton instance.
	 *
	 * @var EventDB|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get booking posts registered to an event.
	 *
	 * @param int $event_id Event ID.
	 *
	 * @return array
	 */
	public function get_registered_bookings( int $event_id ): array {
		global $wpdb;

		$event_id = absint( $event_id );
		if ( ! $event_id ) {
			return array();
		}

		// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Table names are trusted WPDB properties; values remain prepared.
		$query = $wpdb->prepare(
			sprintf(
				'
				SELECT booked.* FROM %1$s AS booked
					LEFT JOIN %2$s AS event ON event.post_id = booked.ID
					LEFT JOIN %2$s AS book_quantity ON book_quantity.post_id = booked.ID
					LEFT JOIN %2$s AS user_booked ON user_booked.post_id = booked.ID
					LEFT JOIN %3$s AS user ON user.ID = user_booked.meta_value
				WHERE booked.post_type = %%s
					AND event.meta_key = %%s
					AND event.meta_value = %%d
					AND user_booked.meta_key = %%s
					AND book_quantity.meta_key = %%s
				',
				$wpdb->posts,
				$wpdb->postmeta,
				$wpdb->users
			),
			'event_auth_book',
			'ea_booking_event_id',
			$event_id,
			'ea_booking_user_id',
			'ea_booking_qty'
		);
		// phpcs:enable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		$results = $wpdb->get_results( $query );

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Get booked quantity for an event.
	 *
	 * @param int      $event_id Event ID.
	 * @param int|null $user_id  Optional user ID.
	 *
	 * @return int
	 */
	public function get_booked_quantity( int $event_id, ?int $user_id = null ): int {
		global $wpdb;

		$event_id = absint( $event_id );
		$user_id  = is_null( $user_id ) ? null : absint( $user_id );

		if ( ! $event_id ) {
			return 0;
		}

		$base_sql = sprintf(
			'
			SELECT SUM( pm.meta_value ) AS qty FROM %1$s AS pm
				INNER JOIN %2$s AS book ON book.ID = pm.post_id
				INNER JOIN %1$s AS pm2 ON pm2.post_id = book.ID
				INNER JOIN %1$s AS pm3 ON pm3.post_id = book.ID
				INNER JOIN %2$s AS event ON event.ID = pm3.meta_value
				INNER JOIN %3$s AS user ON user.ID = pm2.meta_value
			WHERE
				pm.meta_key = %%s
				AND book.post_type = %%s
				%4$s
				AND pm2.meta_key = %%s
				AND pm3.meta_key = %%s
				AND event.ID = %%d
				AND event.post_type = %%s
			',
			$wpdb->postmeta,
			$wpdb->posts,
			$wpdb->users,
			$user_id ? '' : 'AND book.post_status = %s'
		);

		// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- $base_sql already contains trusted WPDB table names; values remain prepared below.
		if ( $user_id ) {
			$query = $wpdb->prepare(
				$base_sql . ' AND user.ID = %d',
				'ea_booking_qty',
				'event_auth_book',
				'ea_booking_user_id',
				'ea_booking_event_id',
				$event_id,
				'tp_event',
				$user_id
			);
		} else {
			$query = $wpdb->prepare(
				$base_sql,
				'ea_booking_qty',
				'event_auth_book',
				'ea-completed',
				'ea_booking_user_id',
				'ea_booking_event_id',
				$event_id,
				'tp_event'
			);
		}
		// phpcs:enable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber

		return (int) apply_filters( 'event_auth_booked_quanity', (int) $wpdb->get_var( $query ), $event_id, $user_id );
	}
}
